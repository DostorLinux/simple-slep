<?php
require_once('campo.php');

use App\Helpers\Doctrine;
use Illuminate\Http\Request;

class CampoInstitucionesGob extends Campo
{
    public $requiere_datos = false;

    protected function display($modo, $dato)
    {   

        $valor_default = json_decode($this->valor_default);
        if (!$valor_default) {
            $valor_default = new stdClass();
            $valor_default->entidad = '';
            $valor_default->servicio = '';
        }

        $display = '<label class="control-label">' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        $display .= '<div class="controls">';
        $display .= '<select class="form-control" id="entidades_'.$this->id.'" data-id="' . $this->id . '" name="' . $this->nombre . '[entidad]" ' . ($modo == 'visualizacion' ? 'readonly' : '') .  ' style="width: 100%">';
        $display .= '</select>';
        $display .= '<br />';
        $display .= '<select class="form-control" id="instituciones_'.$this->id.'" data-id="' . $this->id . '" name="' . $this->nombre . '[servicio]" ' . ($modo == 'visualizacion' ? 'readonly' : '') . '>';
        $display .= '</select>';
        if ($this->ayuda)
            $display .= '<span class="help-block">' . $this->ayuda . '</span>';
        $display .= '</div>';

        $display .= '
            <script>
                $(document).ready(function(){
                    $("#entidades_'.$this->id.'").chosen({placeholder_text: "Por favor Seleccione el Ministerio u Organismo Principal"});
                    $("#instituciones_'.$this->id.'").chosen({placeholder_text: "Por favor Seleccione la Instituci\u00F3n"});

                    var justLoadedEntidad=true;
                    var justLoadedInstitucion=true;
                    var defaultEntidad="' . ($dato && $dato->valor ? $dato->valor->entidad :  $valor_default->entidad) . '";
                    var defaultInstitucion="' . ($dato && $dato->valor ? $dato->valor->servicio :  $valor_default->servicio) . '";
                    
                        
                    updateEntidades();
                    
                    function updateEntidades(){
                        if($.ajaxSettings && $.ajaxSettings.headers) {         
                        delete $.ajaxSettings.headers["X-CSRF-TOKEN"]; 
                        }   
                                $.ajax({
                                method: \'get\',
                                url: \'https://ybedaafdk8.execute-api.us-west-2.amazonaws.com/simple/entidades\',
                                success: function(data) {
                                    var entidades_obj = $("#entidades_'.$this->id.'");
                                    entidades_obj.empty();
                                    entidades_obj.append("<option value=\'\'>Por favor Seleccione el Ministerio u Organismo Principal</option>");

                                    $.each(data, function(idx, el){
                                        entidades_obj.append("<option value=\""+el.nombre+"\" data-id=\""+el.codigo+"\">"+el.nombre+"</option>");
                                    });

                                    entidades_obj.change(function(event){
                                        var selectedId=$(this).find("option:selected").data("id");
                                        updateInstituciones(selectedId);
                                   });

                                    if(justLoadedEntidad){
                                        entidades_obj.val(defaultEntidad).change();
                                        justLoadedEntidad=false;
                                    }
                                  
                                    entidades_obj.trigger("chosen:updated");
                                }
                                });
                                if($.ajaxSettings && $.ajaxSettings.headers) {         
                                    $.ajaxSettings.headers["X-CSRF-TOKEN"] = $(\'meta[name="csrf-token"]\').attr(\'content\');
                                    }        
                            }
                                                
                    function updateInstituciones(entidadId){  
                        if($.ajaxSettings && $.ajaxSettings.headers) {         
                            delete $.ajaxSettings.headers["X-CSRF-TOKEN"]; 
                            }   
                            
                                    $.ajax({
                                    method: "get",
                                    url: "https://ybedaafdk8.execute-api.us-west-2.amazonaws.com/simple/entidades/"+entidadId+"/instituciones",
                                    success: function(data) {
                                        var instituciones_obj = $("#instituciones_'.$this->id.'");
                                        instituciones_obj.empty();
    
                                        $.each(data, function(idx, el){
                                            instituciones_obj.append("<option value=\""+el.nombre+"\" data-id=\""+el.codigo+"\">"+el.nombre+"</option>");
                                        });

                                        if(justLoadedInstitucion){
                                            instituciones_obj.val(defaultEntidad).change();
                                            justLoadedInstitucion=false;
                                        }
                                        instituciones_obj.trigger("chosen:updated");
                                    }
                                    });
                                    if($.ajaxSettings && $.ajaxSettings.headers) {         
                                        $.ajaxSettings.headers["X-CSRF-TOKEN"] = $(\'meta[name="csrf-token"]\').attr(\'content\');
                                        }            
                       
                    }
                   
                });              
            </script>';

        return $display;
    }

    public function formValidate(Request $request, $etapa_id = null)
    {
        $request->validate([
            $this->nombre . '.entidad' => implode('|', $this->validacion),
            $this->nombre . '.servicio' => implode('|', $this->validacion),
          
        ], [], [
            $this->nombre . '.entidad' => "<b>Ministerio u Organismo Principal de $this->etiqueta</b>",
            $this->nombre . '.servicio' => "<b>Servicio de $this->etiqueta</b>",

        ]);
    }

}