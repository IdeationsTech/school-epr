<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class SchoolAccess
{
    public function membership(Request $request): object
    {
        return $request->attributes->get('school_membership') ?? abort(403, 'Select an authorized school.');
    }

    public function roles(object $membership): array
    {
        return DB::table('membership_roles')->join('roles', 'roles.id', '=', 'membership_roles.role_id')
            ->where('membership_roles.school_id', $membership->school_id)
            ->where('membership_roles.membership_id', $membership->id)->pluck('roles.name')->all();
    }

    public function permissions(object $membership): array
    {
        $roleIds = DB::table('membership_roles')->where('school_id', $membership->school_id)
            ->where('membership_id', $membership->id)->pluck('role_id');
        $defaults = DB::table('role_permissions')->whereIn('role_id', $roleIds)->get();
        $overrides = DB::table('school_role_permissions')->where('school_id', $membership->school_id)
            ->whereIn('role_id', $roleIds)->get();
        $enabled = [];
        foreach ($defaults as $row) { $enabled[$row->role_id][$row->permission_id] = true; }
        foreach ($overrides as $row) { $enabled[$row->role_id][$row->permission_id] = (bool) $row->enabled; }
        $ids = [];
        foreach ($enabled as $grants) {
            foreach ($grants as $pid => $allowed) { if ($allowed) { $ids[] = $pid; } }
        }
        return DB::table('permissions')->whereIn('id', array_unique($ids))->pluck('name')->all();
    }

    public function owner(Request $request): void
    {
        abort_unless(in_array('owner', $this->roles($this->membership($request)), true), 403, 'School owner access required.');
    }

    public function can(Request $request, string $permission): bool
    {
        $m = $this->membership($request);
        return in_array('owner', $this->roles($m), true) || in_array($permission, $this->permissions($m), true);
    }

    public function campuses(object $m): array
    {
        $query = DB::table('campuses')->where('school_id', $m->school_id)->where('status', 'active');
        if (!in_array('owner', $this->roles($m), true)) {
            $query->whereIn('id', DB::table('membership_campuses')->where('school_id', $m->school_id)
                ->where('membership_id', $m->id)->select('campus_id'));
        }
        return $query->orderBy('name')->get(['id', 'name', 'code'])->all();
    }

    public function audit(Request $request, string $action, string $type, int $id, array $changes = []): void
    {
        $m = $this->membership($request);
        DB::table('audit_logs')->insert([
            'school_id' => $m->school_id, 'actor_membership_id' => $m->id,
            'action' => $action, 'target_type' => $type, 'target_id' => $id,
            'changes' => json_encode($changes), 'occurred_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
