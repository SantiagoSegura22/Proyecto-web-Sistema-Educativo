<?php
session_start();
require_once __DIR__ . '/controlador/LoginControlador.php';
require_once __DIR__ . '/controlador/EventoControlador.php';
require_once __DIR__ . '/controlador/NoticiasControlador.php';

$accion = $_GET['accion'] ?? 'login';

$loginControlador = new LoginControlador();
$eventoControlador = new EventoControlador();
$noticiasControlador = new NoticiasControlador();

switch ($accion) {
    case 'autenticar':
        $loginControlador->autenticar();
        break;
    case 'registrar':
        $loginControlador->registrar();
        break;
    case 'dashboard':
        $loginControlador->mostrarDashboard();
        break;
    case 'logout':
        $loginControlador->cerrarSesion();
        break;
    case 'eventos':
        $eventoControlador->mostrarVistaEventos();
        break;
    case 'listarEventos':
        $eventoControlador->listarEventos();
        break;
    case 'crearEvento':
        $eventoControlador->crearEvento();
        break;
    case 'editarEvento':
        $eventoControlador->editarEvento();
        break;
    case 'eliminarEvento':
        $eventoControlador->eliminarEvento();
        break;
    case 'noticias':
        $loginControlador->mostrarDashboard();
        break;
    case 'listarNoticias':
        $noticiasControlador->listarNoticias();
        break;
    case 'login':
    default:
        $loginControlador->mostrarLogin();
        break;
}
