<?php

namespace App\Rules;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Validation\ValidationRule;

class EmailExistsInUsersOrAdminsRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $existsInUsers = DB::table('users')->where('email', $value)->exists();
        $existsInAdmins = DB::table('admins')->where('email', $value)->exists();


        if (!$existsInUsers && !$existsInAdmins) {
            $fail('The :attribute does not exist in users or admins.');
    }
}
    }

