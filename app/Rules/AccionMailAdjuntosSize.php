<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Doctrine_Query;

class AccionMailAdjuntosSize implements Rule
{
    private $process_id;

    private $mensajePersonalizado;
    private $maximoTotalPermitido;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($process_id)
    {
        $this->process_id = $process_id;
        $this->maximoTotalPermitido = 5;
        $this->mensajePersonalizado = "El o los archivo(s) adjunto(s) que se indican en el campo Adjunto, supera(n) la capacidad permitida de tamaño en las acciones de envío de correos electrónicos {$this->maximoTotalPermitido}MB. Debe revisar la configuración de tamaño máximo permitido de el o los siguiente(s) campo(s) de tipo FILE: ";
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

        $totalAcumulado = 0;
        $variablesAndSize = [];
        foreach($campos as $campo) {
            if (in_array($campo->nombre, $variablesSinArroba)) {
                if (isset($campo->extra->maxfilesize)) {
                    $totalAcumulado += (int)$campo->extra->maxfilesize;
                }
                $variablesAndSize[] = [
                    'nombre' => $campo->nombre,
                ];
            }
        }

        if ($totalAcumulado > $this->maximoTotalPermitido) {
            foreach($variablesAndSize as $key => $variable) {
                if ($key == count($variablesAndSize)-1) {
                    $this->mensajePersonalizado .= "@@{$variable['nombre']}. Dicho(s) adjunto(s) no debe(n) superar en su totalidad los {$this->maximoTotalPermitido} MB.";
                } else {
                    $this->mensajePersonalizado .= "@@{$variable['nombre']}, ";
                }
                
            }
            $isValid = false;
        }

        return $isValid;
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
