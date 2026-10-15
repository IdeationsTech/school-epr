<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
class SchoolAvailability
{
    public static function allowed(int $schoolId): bool
    {
        $q=DB::table('subscriptions')->where('school_id',$schoolId);
        // Existing installations without a subscription remain usable until onboarded.
        if(!(clone $q)->exists())return true;
        $today=now('Asia/Karachi')->toDateString();
        return $q->where('status','active')->whereDate('starts_on','<=',$today)->whereDate('ends_on','>=',$today)->exists();
    }
    public static function campusLimit(int $schoolId): ?int
    {
        $today=now('Asia/Karachi')->toDateString();
        $value=DB::table('subscriptions')->join('plans','plans.id','=','subscriptions.plan_id')->where('subscriptions.school_id',$schoolId)
            ->where('subscriptions.status','active')->whereDate('starts_on','<=',$today)->whereDate('ends_on','>=',$today)
            ->orderByDesc('subscriptions.ends_on')->value('plans.campus_limit');
        return $value===null?null:(int)$value;
    }
}
