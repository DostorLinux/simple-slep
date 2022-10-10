<?php
require_once('accion.php');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Rules\AccionMailAdjuntosExist;
use App\Rules\AccionMailAdjuntosSize;
use App\Exceptions\EnviarCorreoException;

class AccionEnviarCorreo extends Accion
{

    public function displayForm()
    {
        $display = '<label>Para</label>';
        $display .= '<input type="text" class="form-control col-2" name="extra[para]" value="' . (isset($this->extra->para) ? $this->extra->para : '') . '" />';
        $display .= '<label>CC</label>';
        $display .= '<input type="text" class="form-control col-2" name="extra[cc]" value="' . (isset($this->extra->cc) ? $this->extra->cc : '') . '" />';
        $display .= '<label>CCO</label>';
        $display .= '<input type="text" class="form-control col-2" name="extra[cco]" value="' . (isset($this->extra->cco) ? $this->extra->cco : '') . '" />';
        $display .= '<label>Tema</label>';
        $display .= '<input type="text" class="form-control col-2" name="extra[tema]" value="' . (isset($this->extra->tema) ? $this->extra->tema : '') . '" />';
        $display .= '<label>Contenido</label>';
        $display .= '<textarea class="form-control col-2" name="extra[contenido]">' . (isset($this->extra->contenido) ? $this->extra->contenido : '') . '</textarea>';
        $display .= '<label>Adjunto (para más de un archivo separar por comas) </label>';
        $display .= '<textarea class="form-control col-2" name="extra[adjunto]">' . (isset($this->extra->adjunto) ? $this->extra->adjunto : '') . '</textarea>';
        $display .= '<p>El tamaño total de todos los documentos adjuntos no debe exceder los 5 mb.</br>En el caso de que se exceda este límite, la acción de envío de correo no se ejecutará.</p>';
        // cambiar de 5 a 10

        return $display;
    }

    public function validateForm(Request $request)
    {
        $request->validate([
            'extra.para' => 'required',
            'extra.tema' => 'required',
            'extra.contenido' => 'required'
            // 'extra.adjunto' => [
            //     new AccionMailAdjuntosExist($this->proceso_id),
            //     new AccionMailAdjuntosSize($this->proceso_id),
            // ]
        ]);
    }

    //public function ejecutar(Etapa $etapa)
    public function ejecutar_old($tramite_id)
    {
        $etapa = $tramite_id;

        $regla = new Regla($this->extra->para);

        $to = $regla->getExpresionParaOutput($etapa->id);

        if (empty($to)) {
            \Log::info('Acción de tipo correo sin destinatario');
        }

        $cc = null;
        $bcc = null;

        if (isset($this->extra->cc)) {
            $regla = new Regla($this->extra->cc);
            $cc = $regla->getExpresionParaOutput($etapa->id);
        }
        if (isset($this->extra->cco)) {
            $regla = new Regla($this->extra->cco);
            $bcc = $regla->getExpresionParaOutput($etapa->id);
        }
        $regla = new Regla($this->extra->tema);
        $subject = $regla->getExpresionParaOutput($etapa->id);
        $regla = new Regla($this->extra->contenido);
        $message = $regla->getExpresionParaOutput($etapa->id);

        $cuenta = $etapa->Tramite->Proceso->Cuenta;
        if(!empty($to)){
            \Log::info('inicio accion envio correo con servicio de correo');
            $curl = curl_init();
            $data_body['from'] = env('SERVICIO_CORREO_FROM');
            $data_body['to'] = [$to];

            // Si lleva copia
            if (!is_null($cc))
            {
                $data_body['cc'] = explode(",",$cc);
            }

            // Si lleva copia oculta
            if (!is_null($bcc))
            {
                $data_body['bcc'] = explode(",",$bcc);
            }

            // Si lleva adjuntos
            if (isset($this->extra->adjunto))
            {
                $attachments = explode(",", trim($this->extra->adjunto));
                $files = array();
                foreach ($attachments as $a) {
                    $regla = new Regla($a);
                    $filename = $regla->getExpresionParaOutput($etapa->id);
                    $file = Doctrine_Query::create()
                        ->from('File f, f.Tramite t')
                        ->where('f.filename = ? AND t.id = ?', array($filename, $etapa->Tramite->id))
                        ->fetchOne();
                    if ($file) {
                        $folder = $file->tipo == 'dato' ? 'datos' : 'documentos';
                        $ruta = 'uploads/' . $folder . '/' . $filename;
                        if (file_exists($ruta)) {
                            $data_file['content'] = base64_encode(file_get_contents($ruta));
                            $data_file['checksum'] = hash_file("sha256", $ruta);
                            $data_file['file_name'] = $filename;

                            // Log::info('FILES======', [
                            //     'FILE_A' => $data_file
                            // ]);
                            array_push($files,$data_file);
                        }
                    }
                }
                $data_body['files'] = $files;
            }
            // Petición de envio
            try{
                $data_body['subject'] = base64_encode($subject);
                $data_body['body'] = base64_encode($message);
                $data_body['category'] = env('APP_MAIN_DOMAIN', 'localhost');
                $data_body = json_encode($data_body);

                curl_setopt_array($curl, array(
                    CURLOPT_URL => env('SERVICIO_CORREO_ENDPOINT'),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => $data_body,
                    CURLOPT_HTTPHEADER => array(
                        "Content-Type: application/json",
                        "x-api-key: ".env('SERVICIO_CORREO_API_KEY'),
                        "User-Agent: Mozilla/5.0"
                    ),
                ));
            }catch(Exception $e) {
                Log::info('Error en la petición de envío \n\n', [
                    'error' => $e
                ]);
                return false;
            }

            // Ejecución de envio de correo
            try{
                $response = curl_exec($curl);
                $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);   
                if ((int)$http_status != 200) {
                    Log::info('========> MENSAJE =======:', [
                        'http_status' => $http_status,
                        'message' => $response
                    ]);
                    throw new EnviarCorreoException();
                }
            }catch(Exception $e) {
                Log::info('Error en la ejecución de servicio de correo \n\n', [
                    'error' => $e
                ]);
                if ((int)$http_status != 200) {
                    throw new EnviarCorreoException();
                }
                return false;
            }
            \Log::info('fin accion envio correo con servicio de correo');
        }
    }

    public function ejecutar($tramite_id)
    {
        $etapa = $tramite_id;

        $regla = new Regla($this->extra->para);

        $to = $regla->getExpresionParaOutput($etapa->id);

        if (empty($to)) {
            \Log::info('Acción de tipo correo sin destinatario');
        }

        $cc = null;
        $bcc = null;

        if (isset($this->extra->cc)) {
            $regla = new Regla($this->extra->cc);
            $cc = $regla->getExpresionParaOutput($etapa->id);
        }
        if (isset($this->extra->cco)) {
            $regla = new Regla($this->extra->cco);
            $bcc = $regla->getExpresionParaOutput($etapa->id);
        }
        $regla = new Regla($this->extra->tema);
        $subject = $regla->getExpresionParaOutput($etapa->id);
        $regla = new Regla($this->extra->contenido);
        $message = $regla->getExpresionParaOutput($etapa->id);

        $cuenta = $etapa->Tramite->Proceso->Cuenta;
        if(!empty($to)){
            Mail::send('emails.send', ['content' => $message], function ($message) use ($etapa, $subject, $cuenta, $to, $cc, $bcc) {

                $message->subject($subject);
                $mail_from = env('MAIL_FROM_ADDRESS');
                if(empty($mail_from)) {
                    $message->from($cuenta->nombre . '@' . env('APP_MAIN_DOMAIN', 'localhost'), $cuenta->nombre_largo);
                } else {
                    $message->from($mail_from);
                }

                if (!is_null($cc)) {
                    foreach (explode(',', $cc) as $cc) {
                        if (!empty($cc)) {
                            $message->cc(trim($cc));
                        }
                    }
                }

                if (!is_null($bcc)) {
                    foreach (explode(',', $bcc) as $bcc) {
                        if (!empty($bcc)) {
                            $message->bcc(trim($bcc));
                        }
                    }
                }

                if(empty(env('EMAIL_TEST')))
                    $message->to($to);
                else{
                    $destinatarios_test = explode(",",env('EMAIL_TEST'));
                    $message->to($destinatarios_test);
                }

                if (isset($this->extra->adjunto)) {
                    $attachments = explode(",", trim($this->extra->adjunto));
                    foreach ($attachments as $a) {
                        $regla = new Regla($a);
                        $filename = $regla->getExpresionParaOutput($etapa->id);
                        $file = Doctrine_Query::create()
                            ->from('File f, f.Tramite t')
                            ->where('f.filename = ? AND t.id = ?', array($filename, $etapa->Tramite->id))
                            ->fetchOne();
                        if ($file) {
                            $folder = $file->tipo == 'dato' ? 'datos' : 'documentos';
                            if (file_exists('uploads/' . $folder . '/' . $filename)) {
                                $message->attach('uploads/' . $folder . '/' . $filename);
                            }
                        }
                    }
                }

            });
        }
    }
}
