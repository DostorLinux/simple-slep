<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Doctrine_Query;

class AccionMailAdjuntosExist implements Rule
{
    private $process_id;

    private $mensajePersonalizado;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($process_id)
    {
        $this->process_id = $process_id;
        $this->mensajePersonalizado = 'El o las siguiente(s) variable(s): ';
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
        $isValid = true;
        // separo y limpio las variables de '@@foo' a 'foo'
        $variablesConArroba = explode(',', $value);
        // verificar si hay valores, sino continnuar...
        $variablesSinArroba = [];
        foreach ($variablesConArroba as $variable) {
            $variablesSinArroba[] = str_replace('@@', '', $variable);
        }

        $campos = Doctrine_Query::create()
            ->from('Campo c, c.Formulario f')
            ->andWhere('c.readonly = 0')
            ->andWhere('f.proceso_id = ?', $this->process_id)
            ->execute();

        $nombresExistentes = $this->getNombreVariablesDisponibles($campos);
        $variablesInexistentes = [];
        foreach ($variablesSinArroba as $variable) {
            if (!in_array($variable, $nombresExistentes)) {
                $variablesInexistentes[] = $variable;
            }
        }

        if (count($variablesInexistentes) > 0) {
            $isValid = false;
            foreach ($variablesInexistentes as $key => $variable) {
                // agregamos las @@ para el mensaje
                if ($key == count($variablesInexistentes)-1) {// si es la ultima variable
                    $this->mensajePersonalizado .= "@@{$variable} no están definidas como un tipo de campo permitido para adjuntar.";
                } else {
                    $this->mensajePersonalizado .= "@@{$variable}, ";
                }
            } 
        }

        return $isValid;
    }

    public function getNombreVariablesDisponibles($campos)
    {
        $nombresDeCampos = [];
        foreach ($campos as $campo) {
            $nombresDeCampos[] = $campo->nombre;
        }

        return $nombresDeCampos;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->mensajePersonalizado;
    }
}
