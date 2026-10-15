<?php
namespace App\Rules;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
class PakistanPhone implements ValidationRule
{
    public static function canonical(string $value): string
    {
        $phone=preg_replace('/[\s()\-]/u','',$value) ?? '';
        if (str_starts_with($phone,'0')) { return '+92'.substr($phone,1); }
        return str_starts_with($phone,'92') ? '+'.$phone : $phone;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $phone=is_string($value) ? self::canonical($value) : '';
        if (!preg_match('/^\+92(?:3[0-9]{9}|[124-9][0-9]{8,9})$/D',$phone)) {
            $fail('Enter a valid Pakistan mobile or landline number, e.g. +923001234567.');
        }
    }
}
