@extends('layouts.backend')

@section('title', 'Notificaciones')

@section('content')
<div class="container-fluid">
    <div class="row mt-3">
        <div class="col-md-12">

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page">Notificaciones enviadas para usuarios Backend</li>
                </ol>
            </nav>

    <table class="table">
        <thead>
        <th width="50%">Historial de Notificaciones</th>
        <th width="20%" style="text-align:left" >Fecha de creación</th>
        <th width="10%">Mensajes</th>
        </thead>
        <tbody>
            @foreach($notificaciones_backend as $notificacion)
            <tr>
                <td>{{ $notificacion->titulo }}</td>
                <td>{{ $notificacion->created_at }}</td>
                <td>
                    <a href="#" data-toggle="modal" data-target="#notificacion"  data-whatever="{{ $notificacion->titulo }}" data-body="{{ $notificacion->texto }}" class="btn btn-primary">
                        <i class="material-icons">remove_red_eye</i> Ver Mensaje
                    </a>
                </td>
            </tr>

                              <!-- Modal -->
                 <div class="modal fade" id="notificacion" tabindex="-1" role="dialog" aria-hidden="true">
                     <div class="modal-dialog" role="document">
                        <div class="modal-content">
                             <div class="modal-header">
                               
                                 <h4 class="modal-title"></h4>
                                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                     <span aria-hidden="true">&times;</span>
                                  </button>
                             </div><p>
                                 <h5 style="text-align:center">Mensaje</h5>
                                <div class="modal-body">
                                </div>
                            
                            
                            <div class="modal-footer">
                               <button type="button" class="btn btn-primary" data-dismiss="modal" 
                                onclick="javascript:window.location.reload()">Cerrar</button>   
                            </div>
                         </div>
                     </div>
                 </div>

                 
                
              @endforeach
        </table>



<script>
  $(document).ready(function(){
    $('#notificacion').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget) // Botón que activó el modal
        var recipient = button.data('whatever') //  Extraer información de datos- * atributos
        var body_notificacion = button.data('body')

        var modal = $(this)
        modal.find('.modal-title').text(recipient)
        modal.find('.modal-body').html(body_notificacion)
    });
});
</script>
@endsection