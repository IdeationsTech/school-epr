<?php
namespace App\Rules;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
class PakistanPhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $phone=is_string($value) ? preg_replace('/[\s()\-]/u','',$value) : '';
        if (!preg_match('/^(?:0[0-9]{9,10}|\+?92[0-9]{9,10})$/D',$phone ?? '')) {
            $fail('Enter a valid Pakistan mobile or landline number, e.g. 03001234567 or +923001234567.');
        }
    }
}
