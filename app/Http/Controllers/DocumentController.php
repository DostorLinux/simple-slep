<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Doctrine_Query;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    public function get(Request $request, $inline, $etapa_id='', $filename='', $usuario_backend = null)
    {
        $id = $request->input('id');
        $token = $request->input('token');

        $usuario_front = \App\Models\Usuario::where('registrado',0)->where('id',Auth::user()->id)->first();
        if(!$usuario_front){
            $usuario_front = \App\Models\Usuario::where('registrado',1)->whereNotNull('cuenta_id')->where('id',Auth::user()->id)->first();
            if(!$usuario_front){
                $usuario_front = \App\Models\Usuario::where('registrado',1)->whereNull('cuenta_id')->where('id',Auth::user()->id)->first();
            }
        }

        //Chequeamos permisos del frontend
        $file = Doctrine_Query::create()
            ->from('File f, f.Tramite t, t.Etapas e, e.Usuario u')
            ->where('f.id = ? AND f.llave = ? AND u.id = ?', array($id, $token, $usuario_front->id))
            ->fetchOne();

        if (!$file) {

            //Chequeamos permisos en el backend
            $file = Doctrine_Query::create()
                ->from('File f, f.Tramite.Proceso.Cuenta.UsuariosBackend u')
                ->where('f.id = ? AND f.llave = ? AND u.id = ? AND (u.rol like "%super%" OR u.rol like "%operacion%" OR u.rol like "%seguimiento%")', array($id, $token, $usuario_backend))
                ->fetchOne();

            if (!$file) {
                echo 'Usuario no tiene permisos para ver este archivo.';
                exit;
            }
        }


        $path = 'uploads/documentos/' . $file->filename;

        if (preg_match('/^\.\./', $file->filename)) {
            echo 'Archivo invalido';
            exit;
        }

        if (!file_exists($path)) {
            echo 'Archivo no existe';
            exit;
        }

        if($inline == '0') {
            $friendlyName = str_replace(' ', '-', str_slug(mb_convert_case($file->Tramite->Proceso->Cuenta->nombre . ' ' . $file->Tramite->Proceso->nombre, MB_CASE_LOWER) . '-' . $file->id)) . '.' . pathinfo($path, PATHINFO_EXTENSION);
            return response()->download($path, $friendlyName);
        }else{
            header('Content-Disposition: inline; filename="'.$filename.'"');
            header("Cache-Control: no-cache, must-revalidate");
            header("Content-type:application/pdf");
            readfile($path);
        }
    }
}
