@extends(($login) ? 'layouts.procedure':'layouts.app')

@section('content')
    <h1 class="title">Listado de trámites disponibles</h1>
    <hr class="mb-5">
    <div class="row">
        <div class="col-sm-12">
        @if($sidebar != 'categorias')
            @include('home.tramites_home')
        @else
            @include('home.tramites_categoria')
        @endif
        </div>
    </div>
@endsection
