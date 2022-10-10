<?php

namespace App\Http\Controllers\Manager;

use App\Helpers\Doctrine;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\MessageBackend;
use Doctrine_Manager;
use Illuminate\Support\Facades\Log;

class MessageBackendController extends Controller
{
    public function index(){
        $data['message_backend'] = MessageBackend::get();

        $data['title'] = 'Listado de mensajes para el usuario Backend';
        $data['content'] = view('manager.message_backend.index', $data);

        return view('layouts.manager.app', $data);
    }

    public function edit($message_id = null){

        if($message_id){
            $message_backend = MessageBackend::find($message_id);
            $data['message_backend'] = $message_backend;
        }else{
            $message_backend = new MessageBackend();
        }
        $data['message_backend'] = $message_backend;
        $data['title'] = $message_backend->id ? 'Editar' : 'Crear';
        $data['content'] = view('manager.message_backend.edit', $data);

        return view('layouts.manager.app', $data);
    }

    public function edit_form(Request $request, $message_id = null){
        Doctrine_Manager::connection()->beginTransaction();

        try {
            if ($message_id)
                $message_backend = MessageBackend::find($message_id);
            else
                $message_backend = new MessageBackend();

            $validations = [
                'titulo' => 'required',
                'texto' => 'required',
             ];

            $messages = [
                'titulo.required' => 'El campo Titulo es obligatorio',
                'texto.required' => 'El campo Texto del mensaje es obligatorio',
            ];
            $request->validate($validations, $messages);

            $respuesta = new \stdClass();
            $message_backend->titulo = $request->input('titulo');
            $message_backend->texto =$request->input('texto');
            $message_backend->save();

            Doctrine_Manager::connection()->commit();

            $request->session()->flash('success', 'Mensaje para perfil Backend guardado con éxito.');
            $respuesta->validacion = true;
            $respuesta->redirect = url('manager/message_backend');

        } catch (Exception $ex) {
            $respuesta->validacion = false;
            $respuesta->errores = '<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>' . $ex->getMessage() . '</div>';
            Doctrine_Manager::connection()->rollback();
        }

        return response()->json($respuesta);
    }

    public function delete(Request $request, $message_id){
        $message_backend = MessageBackend::find($message_id);
        $message_backend->delete();

        $request->session()->flash('success', 'message_backend eliminado con éxito.');
        return redirect('manager/message_backend');
    }

    public function cambiar_estado(Request $request, $message_id, $activacion = null){
        //Desactivando el que está activo
        if(!is_null($activacion))
        MessageBackend::where('activo',1)->update(['activo' => 0]);
        
        $message_backend = MessageBackend::find($message_id);
        $message_backend->activo = $activacion ? 1 : 0;
        //$message_backend->inactivo = MessageBackend::find('activo=0');
        Log::info("El message_backend: " . $message_backend->activo);
        $message_backend->save();
        $mensaje_estado = $activacion ? 'Mensaje Backend activado con éxito.' : 'message_backend desactivado con éxito.';
        $request->session()->flash('success', $mensaje_estado);
        return redirect('manager/message_backend');
    }
}