<?php
session_start();
require_once __DIR__ . '/controlador/LoginControlador.php';
require_once __DIR__ . '/controlador/EventoControlador.php';

$accion = $_GET['accion'] ?? 'login';
$loginControlador = new LoginControlador();
$eventoControlador = new EventoControlador();

switch ($accion) {
    // ---- RUTAS DE AUTENTICACIÓN ----
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

    // ---- RUTAS DE EVENTOS ----
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

    // ---- RUTAS POR DEFECTO ----
    case 'login':
    default:
        $loginControlador->mostrarLogin();
        break;
}
