<div class="row">
    @if($num_destacados > 0)
    <div class="col-md-12">
        <h2>Trámites destacados</h2>
        <hr class="mb-3">
        <div class="row">
        @foreach($procesosDestacados as $proceso)
            @if(is_null($proceso->ocultar_front) || !$proceso->ocultar_front)
                <div class="{{$login ? 'col-md-6' : 'col-md-4' }} item">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="media">
                                @if($proceso->icon_ref)
                                    <img src="<?= asset('img/icon/' . $proceso->icon_ref) ?>" class="img-service">
                                @else
                                    <i class="icon-archivo"></i>
                                @endif
                                <div class="media-body">
                                    <p class="card-text">
                                    @if($proceso->nombre_frontend)
                                        {{$proceso->nombre_frontend}}
                                    @else
                                        {{$proceso->nombre}}
                                    @endif
                                    </p>
                                    <p>{{$proceso->descripcion}}</p>
                                    @if(!is_null($proceso->url_informativa))
                                        <p><a href="{{$proceso->url_informativa}}" target="_blank" style="text-decoration: underline;font-size: 14px;">Mas información</a></p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($proceso->ficha_informativa)
                            <a href="{{ route('tramites.pre_inicio', [$proceso->id]) }}"></a>
                        @else
                            <a href="{{
                                    $proceso->canUsuarioIniciarlo(Auth::user()->id) ? route('tramites.iniciar',  [$proceso->id]) :
                                    (
                                        $proceso->getTareaInicial()->acceso_modo == 'claveunica' ? route('login.claveunica').'?redirect='.route('tramites.iniciar', [$proceso->id]) :
                                        route('login').'?redirect='.route('tramites.iniciar', $proceso->id)
                                    )
                                    }}"
                            class="card-footer {{$proceso->getTareaInicial()->acceso_modo == 'claveunica'? 'claveunica' : ''}}">
                                @if ($proceso->canUsuarioIniciarlo(Auth::user()->id))
                                    Iniciar
                                @else
                                    @if ($proceso->getTareaInicial()->acceso_modo == 'claveunica')
                                        <i class="icon-claveunica"></i> Iniciar con Clave Única
                                    @else
                                        Autenticarse
                                    @endif
                                @endif
                                <span>&#8594;</span>
                            </a>
                        @endif

                    </div>
                </div>
            @endif
        @endforeach
        </div>
    </div>
    @endif

    @if(count($listadoCategorias) > 0)
        <div class="col-md-12">
            <h2 class="mt-3">Categorías</h2>
            <hr class="mb-3">
            <div class="row">
                @foreach($listadoCategorias as $categoria)
                    <div class="{{$login ? 'col-lg-6 col-md-6' : 'col-lg-4 col-md-4' }} item">
                        <!-- <a href="<?=url('home/procesos/' . $categoria->id)?>" class="card-link-home"> -->
                        <!-- <a href="<?=url('categoria/' . $categoria->id . '/procesos')?>" class="card-link-home"> -->
                        <a href="{{ route('home.procesos', $categoria->id) }}" class="card-link-home">
                            <div class="card text-center card-categoria">
                                <div class="card-body">
                                    <div class="media">
                                        @if($categoria->icon_ref)
                                            <img src="{{ asset('uploads/logos/' . $categoria->icon_ref) }}" class="img-service">
                                        @else
                                            <i class="icon-archivo"></i>
                                        @endif
                                        <div class="media-body">
                                            <p class="card-text">
                                                ({{ $countCategorias[$categoria->id] }}) {{$categoria->nombre}}
                                            </p>
                                            <p class="card-text">
                                                <font size="3">
                                                    {{$categoria->descripcion}}
                                                </font>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if(count($otrosProcesos) > 0)
        <div class="col-md-12">
            <h2 class="mt-3">Otros trámites</h2>
            <hr class="mb-3">
            <div class="row">
                @foreach($otrosProcesos as $proceso)
                    @if(is_null($proceso->ocultar_front) || !$proceso->ocultar_front)
                        <div class="{{$login ? 'col-md-6' : 'col-md-4' }} item">
                            <div class="card text-center">
                                <div class="card-body">
                                    <div class="media">
                                        @if($proceso->icon_ref)
                                            <img src="<?= asset('img/icon/' . $proceso->icon_ref) ?>"
                                                    class="img-service">
                                        @else
                                            <i class="icon-archivo"></i>
                                        @endif
                                        <div class="media-body">
                                            <p class="card-text">
                                            @if($proceso->nombre_frontend)
                                                {{$proceso->nombre_frontend}}
                                            @else
                                                {{$proceso->nombre}}
                                            @endif
                                            </p>
                                            <p>{{$proceso->descripcion}}</p>
                                            @if(!is_null($proceso->url_informativa))
                                                <p><a href="{{$proceso->url_informativa}}" target="_blank" style="text-decoration: underline;font-size: 14px;">Mas información</a></p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if($proceso->ficha_informativa)
                                    <a class="card-footer" href="{{ route('tramites.pre_inicio', [$proceso->id]) }}">
                                        Iniciar trámite
                                    </a>
                                @else
                                    <a href="{{
                                                $proceso->canUsuarioIniciarlo(Auth::user()->id) ? route('tramites.iniciar',  [$proceso->id]) :
                                            (
                                                $proceso->getTareaInicial()->acceso_modo == 'claveunica' ? route('login.claveunica').'?redirect='.route('tramites.iniciar', [$proceso->id]) :
                                                route('login').'?redirect='.route('tramites.iniciar', $proceso->id)
                                            )
                                            }}"
                                        class="card-footer {{$proceso->getTareaInicial()->acceso_modo == 'claveunica'? 'claveunica' : ''}}">
                                        @if ($proceso->canUsuarioIniciarlo(Auth::user()->id))
                                            Iniciar trámite
                                        @else
                                            @if ($proceso->getTareaInicial()->acceso_modo == 'claveunica')
                                                <i class="icon-claveunica"></i> Iniciar con Clave Única
                                            @else
                                                <i class="material-icons">person</i> Autenticarse
                                            @endif
                                        @endif
                                        <span class="float-right">&#8594;</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
</div>