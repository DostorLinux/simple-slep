<?php
require_once('campo.php');
class CampoPaises extends Campo{
    
    public $requiere_datos=false;
    
    protected function display($modo, $dato) {
        $display = '<label class="control-label">' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        $display.='<div class="controls">';
        $display.='<select class="select-semi-large paises form-control" id="paises_'.$this->id.'" data-id="'.$this->id.'" name="' . $this->nombre . '" ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' style="width:100%">';
        $display.='<option value="">Seleccione Pa&iacute;s</option>';
        $display.='</select>';
        if($this->ayuda)
            $display.='<span class="help-block">'.$this->ayuda.'</span>';
        $display.='</div>';

        $display.='
            <script>
                $(document).ready(function(){

                    var justLoadedPais=true;
                    var defaultPais="'.($dato && $dato->valor?$dato->valor:'').'";
                    $("#paises_'.$this->id.'").chosen({placeholder_text: "Seleccione Pa\u00cds"});

                    function updatePaises(location) {';
        
            if(!App\Models\UsuarioBackend::find(Auth::user()->id))
                $display .= 'delete $.ajaxSettings.headers["X-CSRF-TOKEN"];';
            
                $display .= '$.ajax({
                                method: \'get\',
                                url: \'https://i3x0p9jbi3.execute-api.us-west-2.amazonaws.com/prd/paises/get\',
                                success: function(data) {
                                var paises_obj = $("#paises_'.$this->id.'");
                                $.each(data, function(idx, el){
                                    paises_obj.append("<option data-id=\""+el.codigo+"\" value=\""+el.nombre+"\">"+el.nombre+"</option>");
                                });
                                
                                if(justLoadedPais){
                                    paises_obj.val(defaultPais).change();
                                    justLoadedPais=false;
                                }
                                paises_obj.trigger("chosen:updated");
                                }
                            });';

            if(!App\Models\UsuarioBackend::find(Auth::user()->id))
                $display .= '$.ajaxSettings.headers["X-CSRF-TOKEN"] = $(\'meta[name="csrf-token"]\').attr(\'content\');';

                $display .= '
                        }
                        updatePaises();

                    });
                    

                    
                </script>';
                    
        return $display;

    }
    
    
}