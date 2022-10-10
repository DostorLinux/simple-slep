<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Trámite no activo - {{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <meta name="google" content="notranslate"/>

    <!-- fav and touch icons -->
    <link rel="shortcut icon" href="{{asset('/img/favicon.png')}}">

</head>
<body class="page-error">
<div class="container">
    <div class="row justify-content-center">
        <div class="col" >
            <h2 class="status-error" style="position:absolute;left:5em;"><span style="font-size: 1.8em;" class="material-icons"> priority_high </span></h2>
            <img class="error-icon" src="{{ asset('/img/error_icon.svg') }}" alt="Error 404 icon">
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-7">
            <h1 style=" font-size: 1.875em;">No es posible acceder al trámite</h1>
            <p>
                Lo sentimos, el trámite al cual deseas acceder no se encuentra disponible. <br>
                Esto se debe a que el trámite fue desactivado por la institución o 
                ya fue completado por el usuario
            </p>
           
            <a href="{{ route('home') }}" class="btn btn-simple btn-error-500 btn-primary">
                <i class="material-icons">arrow_left</i>
                Volver al Home
            </a>
        </div>
    </div>
</div>
</body>
</html>