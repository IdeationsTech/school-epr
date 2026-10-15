<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class RequirePlatformAdmin
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->user(),401);
        abort_unless(DB::table('platform_admins')->where('user_id',$request->user()->id)->where('active',true)->exists(),403,'Platform administrator access required.');
        return $next($request);
    }
}
