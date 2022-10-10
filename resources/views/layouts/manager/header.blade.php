<nav class="navbar navbar-expand-lg navbar-light bg-dark custom-nav simple-nav-dark">
    <div class="container-fluid container-manager">
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
                    <a href="#" class="nav-link dropdown-toggle nav-username" " id="navbarDropdownMenuLink"data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="color: white;">
                            Bienvenido, <b>{{ Auth::user()->usuario }}</b>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                        <a href="{{ route('manager.logout') }}" class="dropdown-item">
                            {{__('auth.close_session')}}
                        </a>
                    </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
