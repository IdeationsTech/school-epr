<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SchoolAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SchoolController extends Controller
{
    public function __construct(private SchoolAccess $access) {}

    public function bootstrap(Request $request)
    {
        $m = $this->access->membership($request);
        $school = DB::table('schools')->where('id', $m->school_id)->first(['id','name','code','timezone','currency','status']);
        $owner = in_array('owner', $this->access->roles($m), true);
        $campuses = $this->access->campuses($m);
        $counts = ['campuses' => count($campuses)];
        if ($owner) {
            $counts['users'] = DB::table('school_memberships')->where('school_id', $m->school_id)->where('status','active')->count();
        }
        $contact = DB::table('school_settings')->where('school_id', $m->school_id)->where('key','contact')->value('value');
        return response()->json(['school'=>$school, 'contact'=>$contact ? json_decode($contact,true) : ['phone'=>'','email'=>'','address'=>''],
            'roles'=>$this->access->roles($m), 'permissions'=>$this->access->permissions($m), 'is_owner'=>$owner,
            'campuses'=>$campuses, 'counts'=>$counts, 'csrf_token'=>csrf_token()]);
    }

    public function updateSchool(Request $request)
    {
        abort_unless($this->access->can($request,'school.manage'),403);
        $m = $this->access->membership($request);
        $d = $request->validate(['name'=>'required|string|min:2|max:160', 'timezone'=>['required',Rule::in(['Asia/Karachi'])],
            'currency'=>['required',Rule::in(['PKR'])], 'phone'=>['required',new \App\Rules\PakistanPhone()], 'address'=>'required|string|min:8|max:2000', 'email'=>'nullable|email|max:160']);
        $d['phone']=\App\Rules\PakistanPhone::canonical($d['phone']);
        DB::transaction(function () use ($m,$d,$request): void {
            DB::table('schools')->where('id',$m->school_id)->update(['name'=>$d['name'],'timezone'=>$d['timezone'],'currency'=>$d['currency'],'updated_at'=>now()]);
            DB::table('school_settings')->updateOrInsert(['school_id'=>$m->school_id,'key'=>'contact'],[
                'value'=>json_encode(['phone'=>$d['phone']??'','email'=>$d['email']??'','address'=>$d['address']]), 'updated_at'=>now()]);
            $this->access->audit($request,'school.updated','school',(int)$m->school_id,['fields'=>array_keys($d)]);
        });
        return $this->bootstrap($request);
    }

    public function campuses(Request $request)
    {
        abort_unless($this->access->can($request,'campuses.manage'),403);
        $m = $this->access->membership($request);
        return response()->json(DB::table('campuses')->where('school_id',$m->school_id)->orderBy('name')->get(['id','name','code','address','phone','status']));
    }

    public function saveCampus(Request $request, ?int $campus = null)
    {
        abort_unless($this->access->can($request,'campuses.manage'),403);
        $m = $this->access->membership($request);
        if ($campus) { abort_unless(DB::table('campuses')->where('school_id',$m->school_id)->where('id',$campus)->exists(),404); }
        $d = $request->validate(['name'=>'required|string|min:2|max:160','code'=>['required','alpha_dash','max:30',
            Rule::unique('campuses','code')->where('school_id',$m->school_id)->ignore($campus)],
            'address'=>'required|string|min:8|max:2000','phone'=>['required',new \App\Rules\PakistanPhone()],'status'=>['required',Rule::in(['active','inactive'])]]);
        $d['phone']=\App\Rules\PakistanPhone::canonical($d['phone']);
        DB::transaction(function () use ($m,$d,$campus,$request): void {
            DB::table('schools')->where('id',$m->school_id)->lockForUpdate()->first();
            if($d['status']==='active') {
                $limit=\App\Services\SchoolAvailability::campusLimit((int)$m->school_id);
                $count=DB::table('campuses')->where('school_id',$m->school_id)->where('status','active')->when($campus,fn($q)=>$q->where('id','<>',$campus))->count();
                if($limit!==null && $count >= $limit)throw ValidationException::withMessages(['status'=>'Your package campus limit has been reached. Contact the provider.']);
            }
            if ($campus && $d['status']==='inactive') {
                $others = DB::table('campuses')->where('school_id',$m->school_id)->where('status','active')->where('id','<>',$campus)->count();
                if (!$others) { throw ValidationException::withMessages(['status'=>'At least one active campus is required.']); }
            }
            $values = array_merge($d,['updated_at'=>now()]);
            if ($campus) { DB::table('campuses')->where('school_id',$m->school_id)->where('id',$campus)->update($values); $id=$campus; }
            else { $id=DB::table('campuses')->insertGetId(array_merge($values,['school_id'=>$m->school_id,'created_at'=>now()])); }
            $this->access->audit($request,$campus?'campus.updated':'campus.created','campus',$id,['fields'=>array_keys($d)]);
        });
        return response()->json(['message'=>'Campus saved.']);
    }

    public function deleteCampus(Request $request, int $campus)
    {
        abort_unless($this->access->can($request,'campuses.manage'),403);
        $sid=$this->access->membership($request)->school_id;
        try {
            DB::transaction(function () use ($request,$sid,$campus): void {
                DB::table('schools')->where('id',$sid)->lockForUpdate()->first();
                $target=DB::table('campuses')->where('school_id',$sid)->where('id',$campus)->lockForUpdate()->first();
                abort_unless($target,404);
                if ($target->status==='active' && !DB::table('campuses')->where('school_id',$sid)->where('status','active')->where('id','<>',$campus)->exists()) {
                    throw ValidationException::withMessages(['campus'=>'The last active campus cannot be deleted.']);
                }
                // RESTRICT foreign keys protect staff access, students and all other linked records.
                DB::table('campuses')->where('school_id',$sid)->where('id',$campus)->delete();
                $this->access->audit($request,'campus.deleted','campus',$campus,['name'=>$target->name,'code'=>$target->code]);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            if ((int)($e->errorInfo[1] ?? 0) !== 1451) { throw $e; }
            throw ValidationException::withMessages(['campus'=>'This campus has linked records or assigned users. Keep its history and set it inactive instead.']);
        }
        return response()->json(['message'=>'Campus deleted.']);
    }

    public function members(Request $request)
    {
        $this->access->owner($request);
        $sid=$this->access->membership($request)->school_id;
        $members=DB::table('school_memberships')->join('users','users.id','=','school_memberships.user_id')
            ->where('school_memberships.school_id',$sid)->orderBy('users.name')->get(['school_memberships.id','school_memberships.user_id','school_memberships.status','users.name','users.email']);
        foreach($members as $m) {
            $m->role_ids=DB::table('membership_roles')->where('school_id',$sid)->where('membership_id',$m->id)->pluck('role_id');
            $m->campus_ids=DB::table('membership_campuses')->where('school_id',$sid)->where('membership_id',$m->id)->pluck('campus_id');
        }
        return response()->json(['members'=>$members,'roles'=>DB::table('roles')->where('scope','school')->get(['id','name']),
            'campuses'=>DB::table('campuses')->where('school_id',$sid)->where('status','active')->get(['id','name'])]);
    }

    public function saveMember(Request $request, ?int $member = null)
    {
        $this->access->owner($request);
        $actor=$this->access->membership($request); $sid=$actor->school_id;
        $target=$member ? DB::table('school_memberships')->where('school_id',$sid)->where('id',$member)->first() : null;
        if ($member) { abort_unless($target,404); }
        if (!$member && $request->has('email')) { $request->merge(['email'=>strtolower(trim((string)$request->input('email')))]); }
        $rules=['status'=>['required',Rule::in(['active','inactive'])], 'role_ids'=>'required|array|min:1',
            'role_ids.*'=>['required','integer',Rule::exists('roles','id')->where('scope','school')],
            'campus_ids'=>'required|array|min:1','campus_ids.*'=>['required','integer',Rule::exists('campuses','id')->where('school_id',$sid)->where('status','active')]];
        if (!$member) { $rules += ['name'=>'required|string|min:2|max:160','email'=>'required|email|max:160|unique:users,email','password'=>['required','string','min:12','confirmed',new \App\Rules\PasswordByteLimit()]]; }
        $d=$request->validate($rules);
        DB::transaction(function () use ($request,$d,$target,$member,$actor,$sid): void {
            DB::table('schools')->where('id',$sid)->lockForUpdate()->first();
            $ownerId=DB::table('roles')->where('name','owner')->value('id');
            $roles=array_values(array_unique(array_map('intval',$d['role_ids'])));
            if ($target) {
                if ((int)$target->id===(int)$actor->id && $d['status']!=='active') {
                    throw ValidationException::withMessages(['status'=>'You cannot deactivate your own membership.']);
                }
                $wasOwner=DB::table('membership_roles')->where('school_id',$sid)->where('membership_id',$member)->where('role_id',$ownerId)->exists();
                if ($wasOwner && ($d['status']!=='active' || !in_array((int)$ownerId,$roles,true))) {
                    $other=DB::table('school_memberships')->join('membership_roles','membership_roles.membership_id','=','school_memberships.id')
                        ->where('school_memberships.school_id',$sid)->where('membership_roles.school_id',$sid)
                        ->where('school_memberships.status','active')->where('membership_roles.role_id',$ownerId)->where('school_memberships.id','<>',$member)->exists();
                    if (!$other) { throw ValidationException::withMessages(['role_ids'=>'The last active owner cannot be removed.']); }
                }
                DB::table('school_memberships')->where('school_id',$sid)->where('id',$member)->update(['status'=>$d['status'],'updated_at'=>now()]);
                $id=$member;
            } else {
                $uid=DB::table('users')->insertGetId(['name'=>$d['name'],'email'=>$d['email'],'password'=>Hash::make($d['password']),'created_at'=>now(),'updated_at'=>now()]);
                $id=DB::table('school_memberships')->insertGetId(['school_id'=>$sid,'user_id'=>$uid,'status'=>$d['status'],'created_at'=>now(),'updated_at'=>now()]);
            }
            DB::table('membership_roles')->where('school_id',$sid)->where('membership_id',$id)->delete();
            foreach ($roles as $rid) { DB::table('membership_roles')->insert(['school_id'=>$sid,'membership_id'=>$id,'role_id'=>$rid,'created_at'=>now(),'updated_at'=>now()]); }
            DB::table('membership_campuses')->where('school_id',$sid)->where('membership_id',$id)->delete();
            foreach (array_unique($d['campus_ids']) as $cid) { DB::table('membership_campuses')->insert(['school_id'=>$sid,'membership_id'=>$id,'campus_id'=>$cid,'created_at'=>now(),'updated_at'=>now()]); }
            $this->access->audit($request,$member?'member.updated':'member.created','membership',$id,['roles'=>$roles,'status'=>$d['status']]);
        });
        return response()->json(['message'=>'User access saved.']);
    }

    public function roleMatrix(Request $request)
    {
        $this->access->owner($request); $sid=$this->access->membership($request)->school_id;
        return response()->json(['roles'=>DB::table('roles')->where('scope','school')->get(['id','name']),
            'permissions'=>DB::table('permissions')->orderBy('name')->get(['id','name','description']),
            'defaults'=>DB::table('role_permissions')->get(['role_id','permission_id']),
            'overrides'=>DB::table('school_role_permissions')->where('school_id',$sid)->get(['role_id','permission_id','enabled'])]);
    }

    public function saveRole(Request $request, int $role)
    {
        $this->access->owner($request); $m=$this->access->membership($request);
        $target=DB::table('roles')->where('id',$role)->where('scope','school')->first(); abort_unless($target,404);
        abort_if($target->name==='owner',403,'Owner permissions cannot be changed.');
        $d=$request->validate(['permission_ids'=>'present|array','permission_ids.*'=>['integer',Rule::exists('permissions','id')]]);
        $protected=['school.manage','campuses.manage','members.manage','roles.manage'];
        $forbidden=DB::table('permissions')->whereIn('name',$protected)->whereIn('id',$d['permission_ids'])->exists();
        if($forbidden) { throw ValidationException::withMessages(['permission_ids'=>'School and access administration are reserved for owners.']); }
        DB::transaction(function () use($request,$m,$role,$d): void {
            DB::table('schools')->where('id',$m->school_id)->lockForUpdate()->first();
            DB::table('school_role_permissions')->where('school_id',$m->school_id)->where('role_id',$role)->delete();
            foreach(DB::table('permissions')->pluck('id') as $pid) {
                DB::table('school_role_permissions')->insert(['school_id'=>$m->school_id,'role_id'=>$role,'permission_id'=>$pid,
                    'enabled'=>in_array((int)$pid,array_map('intval',$d['permission_ids']),true),'created_at'=>now(),'updated_at'=>now()]);
            }
            $this->access->audit($request,'role.permissions_updated','role',$role,['permission_ids'=>$d['permission_ids']]);
        });
        return response()->json(['message'=>'Permissions saved for this school only.']);
    }
}
