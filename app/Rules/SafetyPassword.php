<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class SafetyPassword implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $regex = '/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?(\*|!|\.|\,|\-|\_|\+|\(|\)|\{|\}|\[|\]|\&|\%|\$|\#|\@|\<|\>|\=)).{8,}$/';
        return preg_match($regex, $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'La contraseña es muy débil y no cumple el mínimo solicitado. Por favor ingrese una contraseña de al menos 8 caracteres que contenga mayúsculas, minúsculas, números y símbolos, como por ejemplo: M1Pa$$w0rD';
    }
}
