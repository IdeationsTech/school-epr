<?php

namespace App\Http\Middleware;

use App\Support\SchoolContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SetSchoolContext
{
    public function handle(Request $request, Closure $next)
    {
        $context = app(SchoolContext::class);
        $context->clear();
        abort_unless($request->user(), 401, 'Please sign in.');
        $schoolId = $request->session()->get('school_id');
        $m = DB::table('school_memberships')->join('schools', 'schools.id', '=', 'school_memberships.school_id')
            ->where('school_memberships.user_id', $request->user()->id)
            ->where('school_memberships.school_id', $schoolId)->where('school_memberships.status', 'active')
            ->where('schools.status', 'active')->select('school_memberships.*')->first();
        abort_unless($m, 403, 'School access is inactive or not authorized.');
        $request->attributes->set('school_membership', $m);
        $context->set((int) $m->school_id);
        try { return $next($request); }
        finally { $context->clear(); }
    }
}
