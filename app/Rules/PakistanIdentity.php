<?php
namespace App\Rules;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
class PakistanIdentity implements ValidationRule
{
 public static function normalize(?string $v):?string{return $v===null||trim($v)===''?null:str_replace(['-',' '],'',trim($v));}
 public function validate(string $attribute,mixed $value,Closure $fail):void {
  if(!is_string($value)||!preg_match('/^[1-7][0-9]{12}$/D',self::normalize($value)??''))$fail('Enter a 13-digit CNIC/B-Form number, e.g. 35202-1234567-1.');
 }
}
