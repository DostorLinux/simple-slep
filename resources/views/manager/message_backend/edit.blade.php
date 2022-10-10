<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?=url('manager/message_backend')?>">Mensaje para perfil Backend</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?=$title?></li>
    </ol>
</nav>

<form class="ajaxForm" method="post" action="<?= url('manager/message_backend/editar_form/' . $message_backend->id) ?>">
    {{csrf_field()}}
    <fieldset>
        <legend><?= $title ?></legend>
        <hr>
        <div class="validacion"></div>
        <label>Titulo</label>
        <input class="form-control col-6" type="text" name="titulo" value="<?= $message_backend->titulo ?>"/><br>
        <div class="form-group">
            <label>Texto del mensaje (Puede contener HTML)</label>
            <textarea class="form-control col-6" id="message_backendTexto" 
            type="text" rows="4" name="texto" value="<?= $message_backend->texto ?>"><?= $message_backend->texto ?></textarea>
          </div>
    </fieldset>
    <br>
    <div class="form-actions">
        <button class="btn btn-primary" type="submit">Guardar</button>
        <a class="btn btn-light" href="<?= url('manager/message_backend') ?>">Cancelar</a>
    </div>
</form>
