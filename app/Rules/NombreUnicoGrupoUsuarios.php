<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\GrupoUsuarios;

class NombreUnicoGrupoUsuarios implements Rule
{
    private $grupoUsuario;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($grupoUsuario)
    {
        $this->grupoUsuario = $grupoUsuario;
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
        $esValido = true;
        $grupo = GrupoUsuarios::where('nombre', $value)
            ->orderBy('id', 'desc')
            ->first();
        if($grupo) {
            // solo fallará si al editar el nombre existe en un registro diferente
            if ($this->grupoUsuario->id != $grupo->id) {
                $esValido = false;
            }
        }
        

        return $esValido;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'El valor de :attribute ya está en uso.';
    }
}
