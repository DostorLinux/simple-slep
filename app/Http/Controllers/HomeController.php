<?php

namespace App\Http\Controllers;

use App\Helpers\Doctrine;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Cuenta;
use Illuminate\Support\Facades\Log;
use App\Models\Etapa;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct()
    {
        #if user not logged, create new user and auto login this new user.
        //$this->middleware('auth_user');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $procesosDestacados = [];
        $listadoCategorias = [];
        $listadoIdsCategorias = [];
        $countCategorias = [];
        $otrosProcesos = [];

        $totalDestacados = 0;
        $totalOtrosProcesos = 0;

        if(session()->has('redirect_url')){
            return redirect()->away(session()->get('redirect_url'));
        }

        $user_id = 1;

        if (Auth::check()) {
            $user_id = Auth::user()->id;
        }

        $procesos = Doctrine::getTable('Proceso')
            ->findProcesosDisponiblesParaIniciar(
                $user_id,
                \Cuenta::cuentaSegunDominio(),
                'nombre',
                'asc'
            );

        $categorias = Doctrine::getTable('Categoria')->findAll();
        // se agrupan los procesos por Destacados y otros, identificando los de una categoria, pero ignorandolos
        // las categorias de los procesos se agrupan a su vez en $listadoIdsCategorias para identificarlas luego
        foreach($procesos as $proceso) {
            if ($proceso->destacado) {
                $procesosDestacados[] = $proceso;
            } else {
                if (null != $proceso->categoria_id && 0 != $proceso->categoria_id) {
                    if (!in_array($proceso->categoria_id, $listadoIdsCategorias)) {
                        $listadoIdsCategorias[] = $proceso->categoria_id;
                    }
                } else {
                    $otrosProcesos[] = $proceso;
                }
            }

            // Cuenta los procesos por categoria
            if (null != $proceso->categoria_id && 0 != $proceso->categoria_id && !$proceso->ocultar_front) {
                $countCategorias[$proceso->categoria_id] = isset($countCategorias[$proceso->categoria_id]) ? $countCategorias[$proceso->categoria_id]+1:1;
            }
        }

        foreach($categorias as $categoria) {
            if (in_array($categoria->id, $listadoIdsCategorias)) {
                $listadoCategorias[] = $categoria;
            }
        }

        $totalDestacados = count($procesosDestacados);
        $totalOtrosProcesos = count($otrosProcesos);

        $data = \Cuenta::configSegunDominio();

        $data['title'] = 'Home';
        $data['num_destacados'] = $totalDestacados;
        $data['num_otros'] = $totalOtrosProcesos;
        $data['sidebar'] = 'disponibles';
        $data['procesosDestacados'] = $procesosDestacados;
        $data['listadoCategorias'] = $listadoCategorias;
        $data['otrosProcesos'] = $otrosProcesos;
        $data['countCategorias'] = $countCategorias;
        $data['page'] = 'home';
        $data['login'] = false;

        if (Auth::check() && Auth::user()->registrado) {
            $data['login'] = true;
        } else {
        }

        return view('home', $data);
    }

    public function procesosCategoria($categoria_id)
    {
        $procesos = Doctrine::getTable('Proceso')->findProcesosDisponiblesParaIniciarByCategoria(Auth::user()->id, $categoria_id, Cuenta::cuentaSegunDominio(), 'nombre', 'asc');
        $categoria = Doctrine::getTable('Categoria')->find($categoria_id);

        $data = \Cuenta::configSegunDominio();

        $data['listadoProcesos'] = $procesos;
        $data['categoria'] = $categoria;
        $data['login'] = false;
        $data['sidebar'] = 'categorias';

        if (Auth::check() && Auth::user()->registrado) {
            $data['login'] = true;
        }

        return view('home', $data);
    }

    public function terminosFront(Request $reuest)
    {
        return view('terminos.front_view');
    }

}
