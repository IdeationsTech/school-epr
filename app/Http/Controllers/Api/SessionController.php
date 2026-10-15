<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SessionController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $schools = $user ? DB::table('school_memberships')->join('schools', 'schools.id', '=', 'school_memberships.school_id')
            ->where('school_memberships.user_id', $user->id)->where('school_memberships.status', 'active')
            ->where('schools.status', 'active')->orderBy('schools.name')->get(['schools.id', 'schools.name', 'schools.code']) : [];
        return response()->json(['user' => $user ? ['id' => $user->id, 'name' => $user->name, 'email' => $user->email] : null,
            'is_platform_admin' => $user ? DB::table('platform_admins')->where('user_id',$user->id)->where('active',true)->exists() : false,
            'schools' => $schools, 'school_id' => $request->session()->get('school_id'), 'csrf_token' => csrf_token()]);
    }

    public function login(Request $request)
    {
        $data = $request->validate(['email' => 'required|string|email|max:255', 'password' => 'required|string|max:1024', 'remember' => 'sometimes|boolean']);
        $email = Str::lower(trim($data['email']));
        $key = 'login:'.hash('sha256', $email.'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['message' => 'Too many attempts. Try again in a minute.'], 429);
        }
        if (!Auth::attempt(['email' => $email, 'password' => $data['password']], $data['remember'] ?? false)) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['email' => 'Email or password is incorrect.']);
        }
        $candidateIds=DB::table('school_memberships')->join('schools','schools.id','=','school_memberships.school_id')
            ->where('school_memberships.user_id',Auth::id())->where('school_memberships.status','active')
            ->where('schools.status','active')->orderBy('schools.id')->pluck('schools.id');
        $sid=$candidateIds->first(fn($id)=>\App\Services\SchoolAvailability::allowed((int)$id));
        $admin=DB::table('platform_admins')->where('user_id',Auth::id())->where('active',true)->exists();
        if (!$sid && !$admin) {
            Auth::logout();
            $request->session()->invalidate(); $request->session()->regenerateToken();
            RateLimiter::hit($key, 60);
            return response()->json(['message' => 'School access is inactive or the subscription has expired. Contact the provider.', 'csrf_token' => csrf_token()], 403);
        }
        RateLimiter::clear($key);
        $request->session()->regenerate();
        $request->session()->put('school_id', (int) $sid);
        return $this->show($request);
    }

    public function logout(Request $request)
    {
        Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken();
        return response()->json(['message' => 'Signed out.', 'csrf_token' => csrf_token()]);
    }

    public function switchSchool(Request $request)
    {
        abort_unless($request->user(), 401);
        $id = $request->validate(['school_id' => 'required|integer'])['school_id'];
        $allowed = DB::table('school_memberships')->join('schools', 'schools.id', '=', 'school_memberships.school_id')
            ->where('school_memberships.user_id', $request->user()->id)->where('school_memberships.school_id', $id)
            ->where('school_memberships.status', 'active')->where('schools.status', 'active')->exists();
        abort_unless(\App\Services\SchoolAvailability::allowed((int)$id),403,'School subscription has expired.');
        abort_unless($allowed, 403, 'School access is not authorized.');
        $request->session()->put('school_id', (int) $id);
        $request->session()->forget('campus_id');
        return $this->show($request);
    }
}
