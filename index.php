<?php
// =============================================
// index.php  — punto de entrada de toda la app
// =============================================
session_start();

// ── Controladores existentes (de tus compañeros) ──
require_once __DIR__ . '/controlador/LoginControlador.php';
require_once __DIR__ . '/controlador/EventoControlador.php';
require_once __DIR__ . '/controlador/NoticiasControlador.php';

// ── Tu controlador ──
require_once __DIR__ . '/controlador/AsistenciaControlador.php';

$accion = $_GET['accion'] ?? 'login';

// ── Instancias existentes (de tus compañeros) ──
$loginControlador  = new LoginControlador();
$eventoControlador = new EventoControlador();
$noticiasControlador = new NoticiasControlador();

// ── Tu instancia ──
$asistCtrl = new AsistenciaControlador();

switch ($accion) {

    // ---- LOGIN 
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

    // ---- EVENTOS 
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
<<<<<<< HEAD
    case 'noticias':
        $loginControlador->mostrarDashboard();
        break;
    case 'listarNoticias':
        $noticiasControlador->listarNoticias();
        break;
=======

    // ---- ASISTENCIA:
    case 'asistencia':
        $asistCtrl->mostrarVista();
        break;

    // ---- ASISTENCIA: endpoints AJAX 
    case 'asistencia_registros':
        $asistCtrl->obtenerRegistros();
        break;

    case 'asistencia_stats':
        $asistCtrl->obtenerEstadisticas();
        break;

    case 'asistencia_uno':
        $asistCtrl->obtenerUno();
        break;

    case 'asistencia_actualizar':
        $asistCtrl->actualizar();
        break;

    case 'asistencia_eventos':
        $asistCtrl->obtenerEventos();
        break;

    // ---- LOGIN por defecto ----
>>>>>>> 44350ea (The attendance module was changed to PHP.)
    case 'login':
    default:
        $loginControlador->mostrarLogin();
        break;
}