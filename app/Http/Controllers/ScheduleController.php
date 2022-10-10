<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Cuenta;
use Illuminate\Support\Facades\Mail;
use App\Helpers\Doctrine;
use Doctrine_Query;
use Doctrine_Core;

class ScheduleController extends Controller
{
    public function vencidas_obtener()
    {
        $fecha_actual = \Carbon\Carbon::now('America/Santiago')->format('Y-m-d');
        try{
        $etapas_vencidas = \App\Models\Etapa::where('vencimiento_at','<=',$fecha_actual)
                           ->select('id')
                           ->where('pendiente',1)
                           ->take(5)
                           ->orderBy('id','desc')
                           ->get();
        }catch(\Exception $e){
            $excepcion = 'Se produjo una excepción al obtener etapas vencidas: '.$e;
            return response()->json([
                'mensaje' => $excepcion
            ],200);
        }
        return response()->json($etapas_vencidas,200);
    }

    public function vencidas_avanzar($etapa_id)
    {
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
        if($etapa->vencida())
        {
            try{
                $etapa->avanzar();
            }catch(\Exception $e){
                $excepcion = 'Se produjo una excepción al avanzar etapa vencida: '.$e;
                return response()->json([
                    'mensaje' => $excepcion
                ],200);
            }
            return response()->json(['vencida' => true],200);
        }else{
            return response()->json(['vencida' => false],200);
        }
    }
}
