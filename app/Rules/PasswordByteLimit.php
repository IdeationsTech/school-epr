<?php
namespace App\Rules;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
/** Bcrypt accepts at most 72 bytes, including multi-byte passwords. */
class PasswordByteLimit implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value) && strlen($value) > 72) { $fail('The password must not exceed 72 bytes.'); }
    }
}
