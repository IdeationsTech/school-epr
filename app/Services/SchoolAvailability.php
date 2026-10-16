<?php
namespace App\Services;
class SchoolAvailability
{
 public static function allowed(int $schoolId):bool{return LicenseService::allowed($schoolId);}
 public static function campusLimit(int $schoolId):?int {
  $s=LicenseService::period($schoolId);return $s?($s->campus_limit===null?null:(int)$s->campus_limit):null;
 }
}
