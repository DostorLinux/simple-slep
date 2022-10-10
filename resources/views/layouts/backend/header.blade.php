<nav class="navbar navbar-expand-lg nnavbar-dark bg-dark custom-nav simple-nav-dark">
    <div class="container-fluid container-backend">
        <div class="barra-gob">
            <div class="blue"></div>
            <div class="red"></div>
        </div>
        <a class="navbar-brand" href="{{ route('backend.home') }}">
            <img src="{{asset('/img/logo_backend_white.svg')}}" alt="Logo">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="row">
            <div class="col-12">
                <ul class="navbar-nav float-right">
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle nav-username" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Bienvenido, <strong>{{ Auth::user()->email }}</strong>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                            <a href="{{route('backend.cuentas')}}" class="dropdown-item">
                                {{__('auth.my_account')}}
                            </a>
                            <a href="{{ route('backend.logout') }}" class="dropdown-item">
                                {{__('auth.close_session')}}
                            </a>

                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid simple-bknd-menu-items">
    <ul class="menu-links text-right">
        @if (Auth::guest())
            <li class="nav-item">
                <a href="{{ route('login') }}" class="nav-link">
                    {{__('auth.login')}}
                </a>
            </li>
        @else
            <li class="nav-item {{Request::path() == 'backend' ? 'active' : ''}}">
                <a href="{{ route('backend.home') }}"
                    class="nav-link">
                    {{__('nav.home')}}
                </a>
            </li>
            @can('proceso')
                <li class="nav-item {{strstr(Request::path(), 'backend/procesos') ||
                        strstr(Request::path(), 'backend/formularios') ||
                        strstr(Request::path(), 'backend/acciones') ||
                        strstr(Request::path(), 'backend/Admseguridad') ||
                        strstr(Request::path(), 'backend/suscriptores') ||
                        strstr(Request::path(), 'backend/documentos') ?
                        'active' : ''}}">
                    <a href="{{route('backend.procesos.index')}}"
                        class="nav-link">
                        {{__('nav.bpm')}}
                    </a>
                </li>
            @endcan
            @can('seguimiento')
            <li class="nav-item {{strstr(Request::path(), 'backend/seguimiento') ? 'active' : ''}}">
                <a href="{{route('backend.tracing.index')}}"
                    class="nav-link">
                    {{__('nav.tracing')}}
                </a>
            </li>
            @endcan
            @can('gestion')
                <li class="nav-item {{strstr(Request::path(), 'backend/reportes') ? 'active' : '' }}">
                    <a href="{{route('backend.report')}}"
                        class="nav-link">
                        {{__('nav.management')}}
                    </a>
                </li>
            @endcan
            @can('auditoria')
                <li class="nav-item {{strstr(Request::path(), 'backend/auditoria') ? 'active' : ''}}">
                    <a href="{{route('backend.audit')}}"
                        class="nav-link">
                        {{__('nav.audit')}}
                    </a>
                </li>
            @endcan
            @can('api')
                <li class="nav-item {{strstr(Request::path(), 'backend/api') ? 'active' : ''}}">
                    <a href="{{route('backend.api')}}"
                        class="nav-link">
                        {{__('nav.api')}}
                    </a>
                </li>
            @endcan
              <!--https://git.gob.cl/simple/simple/issues/648-->
                          
              <li class="nav-item">
                <a href="{{ route('backend.notificaciones') }}"
                       class="nav-link {{strstr(Request::path(), 'backend/notificaciones') ? 'active' : '' }}">
                        <i class="material-icons" >notification_important </i><span tyle="color:red" class="badge">{{ $notificaciones = \DB::table('mensaje_backend')->count() }}</span> {{__('Notificaciones')}}
                 </a>
            </li>
            @can('configuracion')
            <li class="nav-item {{strstr(Request::path(), 'backend/configuracion') ? 'active' : ''}}">
                <a href="{{ route('backend.configuration.my_site') }}"
                    class="nav-link">
                    <i class="material-icons">settings</i> {{__('nav.config')}}
                </a>
            </li>
            @endcan
            <li class="nav-item">
                <a target="_blank" onclick="ga('cuenta.send', 'event', 'Mejora UX', 'Ayuda_nav_index');"
                data-toggle="modal" data-target="#ayudaModal"  href="#ayudaModal" class="nav-link">
                    <i class="material-icons">help</i> {{__('nav.help')}}
                </a>
            </li>
        @endif
    </ul>
</div>

<!-- Modal -->
<div class="modal fade" id="ayudaModal" tabindex="-1" role="dialog" aria-labelledby="ayudaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="ayudaModalLabel">Información</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul>
                    <h5> Mesa de ayuda</h5>
                    <li><b>Link de <a href="https://gd.policomp.com/WebContact/Default.aspx" target="_blank">Ingreso de solicitud de ayuda</a><br> Horario:
                    Lunes a Viernes 09:00hrs a 18:00hrs</li><br>
                    <h5>Recursos de Apoyo</h5>
                    <li>Link de <a href="https://atencioninstitucional.digital.gob.cl/#/simple" target="_blank">Preguntas Frecuentes</a></li>
                    <li>Link de <a href="https://ejemplos.simple.digital.gob.cl/" target="_blank">Biblioteca de Ejemplos</a></li></b>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="javascript:window.location.reload()">Cerrar</button>   
            </div>
        </div>
    </div>
</div>
