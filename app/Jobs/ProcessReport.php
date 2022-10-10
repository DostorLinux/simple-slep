<?php

namespace App\Jobs;

use App\Models\DatoSeguimiento;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Job;
use App\Models\Tramite;
use DB;
use Carbon\Carbon;
use PHPExcel_Style_Fill;

class ProcessReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id;
    protected $user_type;
    protected $proceso_id;
    protected $reporte_id;
    protected $params;
    protected $max_running_jobs = 1;
    protected $tries = 1;
    protected $job_info;
    protected $reporte_tabla;
    protected $header_variables;
    protected $link_host;
    protected $email_to;
    protected $email_subject;
    protected $email_message;
    protected $email_name;
    protected $_base_dir;
    protected $nombre_reporte;
    protected $desde;
    protected $hasta;
    protected $pendiente;
    protected $cuenta;
    protected $reportname;
    protected $nombre_cuenta;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id,$user_type,$proceso_id,$reporte_id,$params,$reporte_tabla,$header_variables,$host, $email_to, $email_name, $email_subject, $desde, $hasta, $pendiente, $cuenta, $reportname, $nombre_cuenta){
        $this->user_id = $user_id;
        $this->user_type = $user_type;
        $this->proceso_id = $proceso_id;
        $this->reporte_id = $reporte_id;
        $this->params = $params;
        $this->reporte_tabla = $reporte_tabla;
        $this->header_variables = $header_variables;
        $this->link_host = $host;
        $this->email_to = $email_to;
        $this->email_name = $email_name;
        $this->email_subject = $email_subject;
        $this->_base_dir = public_path('uploads/tmp');
        if(!file_exists($this->_base_dir) ) {
            mkdir($this->_base_dir, 0777, true);
        }
        $this->desde = $desde;
        $this->hasta = $hasta;
        $this->pendiente = $pendiente;
        $this->cuenta = $cuenta;
        $this->reportname = $reportname;
        $this->nombre_cuenta = $nombre_cuenta;
  
        $this->job_info = new Job();
        $this->arguments = serialize([$user_id, $user_type, $proceso_id, $reporte_id]);
        $this->job_info->user_id = $this->user_id;
        $this->job_info->user_type = $this->user_type;
        $this->job_info->arguments = $this->arguments;
        $this->job_info->extra = $this->nombre_cuenta; //se guarda el nombre del reporte en el campo extra 
        $this->job_info->status = Job::$created;
        $this->job_info->save();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {   
        $this->job_info->status = Job::$running;
        $this->job_info->save();

        $this->generar_consulta();

        $this->job_info->filename = $this->nombre_reporte.'.xls';
        $this->job_info->filepath = $this->_base_dir;
        
        try{
            $this->send_notification();
            $this->job_info->status = Job::$finished;
        }catch(\Exception $e){
            Log::error("ProcessReport::handle() Error al enviar notificacion: " . $e->getMessage());
            $this->job_info->status = Job::$error;
        }
        $this->job_info->save();
    }

    /**
     * @internal Genera unn reporte en excel con los datos buscados
     *
     * @return excel 
     */
    private function generar_consulta()
    {
        $data = []; // Array donde se almacenaran los datos del excel
        $excel_row = $this->reporte_tabla;
        $header_variables = $this->header_variables;
        Log::info("###VALOR Campos reporte Backend solicitadas por el usuario: " .  implode(",", $header_variables));
        /** Query para obtener los tramites de acuerdo al proceso seleccionado */
        $tramites = Tramite::whereHas('proceso', function($q){
            $q->where('id', $this->proceso_id);
            $q->whereNull('deleted_at');
        })
        ->whereHas('etapas', function($q){
            $q->whereHas('datoSeguimientos');
        });
        if(!is_null($this->desde)) // Si viene filtro por fecha desde  agrego la condición a la query
        {
            $this->desde = $this->desde.' 00:00:00';
            $this->desde = date('Y-m-d H:i:s', strtotime($this->desde));
            $tramites = $tramites->where('tramite.created_at','>=',Carbon::createFromFormat('Y-m-d H:i:s',$this->desde));
        }
        if(!is_null($this->hasta)) // Si viene filtro por fecha hasta agrego la condición a la query
        {
            $this->hasta = $this->hasta.' 23:59:59';
            $this->hasta = date('Y-m-d H:i:s', strtotime($this->hasta));
            $tramites = $tramites->where('tramite.created_at','<=',Carbon::createFromFormat('Y-m-d H:i:s',$this->hasta));
        }
        if($this->pendiente != -1) 
        {
            $tramites = $tramites->where('pendiente', $this->pendiente);
        }
        $tramites = $tramites->groupBy('id')->get();
        // Comienzo a recorrer los tramites
        foreach ($tramites as $t) 
        {
            unset($data);
            // Obtengo las etapas actuales del tramite
            $etapas_actuales = $t->etapas()->where('pendiente',1)->join('tarea', 'tarea.id', '=','etapa.tarea_id')
            ->pluck('tarea.nombre')->toArray();

             //issue https://git.gob.cl/simple/simple/issues/637
            $usrs_etapas = $t->etapas('usuario_id')->get();
            
            foreach($usrs_etapas as $usr_etapa){
                $id_etapa_actual = $usr_etapa->id;
                $tte_id = $usr_etapa->tramite_id;
                $pendiente_etapa_actual = $usr_etapa->pendiente;
                $acceso_modo = $usr_etapa->Tarea->acceso_modo;
                $usuario_asignado = $usr_etapa->usuario_id; 
                $nombre_etapa = $usr_etapa->Tarea->nombre;
            }
             
            $proceso_etps =  DB::table('proceso')->
            join('tramite',  'tramite.proceso_id'  ,'=','proceso.id')->
            where('tramite.id', '=', $tte_id)->get();
 
            foreach ($proceso_etps as $proc_e){
                $proceso_id = $proc_e->proceso_id;
                $data['proceso_id'] = $proceso_id;

            }

            $grupo_usrs_tareas = DB::table('tramite')->
            join('etapa',  'etapa.tramite_id'  ,'=','tramite.id')->
            join('tarea',  'tarea.id'  ,'=','etapa.tarea_id')->
            join('grupo_usuarios', 'grupo_usuarios.id', '=','tarea.grupos_usuarios')->
            where('etapa.id', $id_etapa_actual)->get();
            
            $usuarios_etapa = $t->etapas()->whereIn('pendiente',[0,1])->join('usuario', 'usuario.id', '=','etapa.usuario_id')->get();
            
            if($t->pendiente){
                if($acceso_modo=='grupos_usuarios'){
                    if(!is_null($usuario_asignado)){
                        foreach($usuarios_etapa as $usr_e){
                            $data['usuario_id'] = $usr_e->email;
                        }
                    }

                    if($pendiente_etapa_actual == 1){
                        foreach($grupo_usrs_tareas as $info_grupo){
                            $id_grupo = $info_grupo->grupos_usuarios;
                            $proceso_id = $info_grupo->proceso_id;
                            $grupo_usr = DB::table('grupo_usuarios')->find($id_grupo);
                            $data['nombre_grupo'] = $grupo_usr->nombre;
                            $data['id_grupo_usuario'] = str_replace(',', '',$id_grupo);
                        }
                    }
                }
            }
    
            /** seteo las variables para el excel */
            $data['id'] = $t->id;
            //issue https://git.gob.cl/simple/simple/issues/637
            
            $data['etapa_actual'] = $nombre_etapa;

        
            $data['pendiente'] = $t->pendiente ? 'En curso' : 'Completado';
            $data['created_at'] = isset($t->created_at) ? \Carbon\Carbon::parse($t->created_at)->format('d-m-Y H:i:s') : '';
            $data['updated_at'] = isset($t->updated_at) ? \Carbon\Carbon::parse($t->updated_at)->format('d-m-Y H:i:s') : '';
            $data['ended_at'] = isset($t->ended_at) ? \Carbon\Carbon::parse($t->ended_at)->format('d-m-Y H:i:s') : '';
            $row = array();
            // Busco los datos seguimientos actuales del tramite
            $datos_actuales = DatoSeguimiento::join('etapa','etapa.id', 'dato_seguimiento.etapa_id') 
            ->where('tramite_id',$t->id)
            ->groupBy('nombre')
            ->selectRaw('max(dato_seguimiento.id) as max_id')
            ->get()->toArray();
            // Obtengo los registros de la tabla dato seguimiento de acuerdo a los max(id) 
            $datos = DatoSeguimiento::whereIn('id',$datos_actuales)
            ->groupBy('nombre')
            ->get();
            // Recorro los datos obtenidos
            foreach ($datos as $d) {
                $val = $d->valor;
                if (!is_string($val)) { // Si el valor no es string, codifico el valor 
                    $val = json_encode($val, JSON_UNESCAPED_UNICODE);
                }
                $data[$d->nombre] = strip_tags($val== 'null' ? '' : json_decode('"'.$this->cleanValue($val).'"'));// Agrego el valor al array para mostrar en el excel
            }
            // Recorro las variables de la cabecera del excel 
            foreach ($header_variables as $h) 
            {
                $var_find = explode("->", $h);
                if (count($var_find) > 1) 
                {
                    $row[] = isSet($data[$var_find[0]]) ? json_decode($data[$var_find[0]])->$var_find[1] : '';
                } 
                else 
                {
                    $row[] = isSet($data[$h]) ? $data[$h] : '';
                }
            }
            $excel_row[] = $row;
        }
        $this->nombre_reporte = 'reporte-'.$this->reporte_id.'-'.Carbon::now('America/Santiago')->format('dmYHis');
        Excel::create($this->nombre_reporte, function ($excel) use ($excel_row) {
            $excel->sheet('reporte', function ($sheet) use ($excel_row) {
                $sheet->fromArray($excel_row, null, 'A1', false, false);
                // TODO: agregar estilos al reporte
            });
        })->store('xls', $this->_base_dir);
    }

    private function send_notification_old(){
        $reportname = $this->reportname;
        $nombre_cuenta = $this->nombre_cuenta;
        $link = "{$this->link_host}/backend/reportes/descargar_archivo/{$this->user_id}/{$this->job_info->id}/{$this->job_info->filename}";
        $data = ['link' => $link, 'reportname' => $reportname, 'nombre_cuenta' => $nombre_cuenta];
        $email_to = $this->email_to;
        $email_subject = $this->email_subject;
        $cuenta = $this->cuenta;
        $html = view('emails.download_link', ['link' => $link, "reportname" => $reportname]);
        $body = $html->render();
        \Log::info('inicio reporte con servicio de correo');
        try{
            $curl = curl_init();
            $data_body['from'] = env('SERVICIO_CORREO_FROM');
            $data_body['to'] = [$email_to];
            $data_body['subject'] = base64_encode($email_subject);
            $data_body['body'] = base64_encode($body);
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
            curl_close($curl);   
        }catch(Exception $e) {
            Log::info('Error en la ejecución de servicio de correo \n\n', [
                'error' => $e
            ]);
            return false;
        }
        \Log::info('fin reporte con servicio de correo');
    }

    private function send_notification() {
        $reportname = $this->reportname;
        $nombre_cuenta = $this->nombre_cuenta;
        $link = "{$this->link_host}/backend/reportes/descargar_archivo/{$this->user_id}/{$this->job_info->id}/{$this->job_info->filename}";
        $data = ['link' => $link, 'reportname' => $reportname, 'nombre_cuenta' => $nombre_cuenta];
        $email_to = $this->email_to;
        $email_subject = $this->email_subject;
        $cuenta = $this->cuenta;
        try{
            Mail::send('emails.download_link', $data, function($message) use ($cuenta, $link, $email_to, $email_subject){
                \Log::debug('Iniciando Envio correo Api Ses v1');
                $message->subject($email_subject);
                $mail_from = env('SERVICIO_CORREO_FROM');
                if(empty($mail_from))
                    $message->from($cuenta->nombre . '@' . env('APP_MAIN_DOMAIN', 'localhost'), $cuenta->nombre_largo);
                else
                    $message->from($mail_from);
    
                $message->to($email_to);
                \Log::debug('Enviando correo a: ' . $email_to);
            });
        }
        catch(Exception $e) {
            Log::info('Error en la ejecución de servicio de correo API Ses V1 \n\n', [
                'error' => $e
            ]);
            return false;
        }
        \Log::info('fin reporte con servicio de correo API Ses V1');
       

    }

    private function cleanValue($valor)
    {
        return str_replace('[','',str_replace(']','',str_replace('{','',str_replace('}','',str_replace('"','',$valor)))));
    }
}
