<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page"><?=$title?></li>
    </ol>
</nav>

<p><a class="btn btn-primary" href="<?=url('manager/message_backend/editar')?>">Crear Mensaje</a></p>

<table class="table">
    <thead>
    <tr>
        <th width="70%">Texto</th>
        <th width="30%">Acciones</th>
    </tr>
    </thead>
    <tbody>
     @foreach($message_backend as $msje)
    <tr>
        <td width="70%"><a href="<?=url('manager/message_backend/editar/'.$msje->id)?>">{{ $msje->titulo }}</a></td>
      
        <td width="30%">  

             <a class="btn btn-danger" href="<?=url('manager/message_backend/eliminar/' . $msje->id)?>"
                onclick="return confirm('¿Está seguro que desea eliminar este mensaje?')">
                <i class="material-icons">delete</i> Eliminar
             </a>
        </td>
        

    </tr>
     @endforeach
    </tbody>
</table>