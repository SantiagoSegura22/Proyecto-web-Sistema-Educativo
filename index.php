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
require_once __DIR__ . '/controlador/InscripcionControlador.php';

$accion = $_GET['accion'] ?? 'login';

// ── Instancias existentes (de tus compañeros) ──
$loginControlador  = new LoginControlador();
$eventoControlador = new EventoControlador();
$noticiasControlador = new NoticiasControlador();

// ── Tu instancia ──
$asistCtrl = new AsistenciaControlador();
$inscripcionCtrl = new InscripcionControlador();

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
    case 'noticias':
        $noticiasControlador->mostrarVistaNoticias();
        break;
    case 'listarNoticias':
        $noticiasControlador->listarNoticias();
        break;

    // ---- INSCRIPCIONES
    case 'inscripciones':
        $inscripcionCtrl->mostrarVista();
        break;

    case 'inscripciones_listar':
        $inscripcionCtrl->listarInscripciones();
        break;

    case 'inscripciones_stats':
        $inscripcionCtrl->obtenerEstadisticas();
        break;

    case 'inscripciones_eventos':
        $inscripcionCtrl->obtenerEventos();
        break;

    case 'inscripciones_uno':
        $inscripcionCtrl->obtenerUna();
        break;

    case 'inscripciones_crear':
        $inscripcionCtrl->crearInscripcion();
        break;

    case 'inscripciones_editar':
        $inscripcionCtrl->editarInscripcion();
        break;

    case 'inscripciones_eliminar':
        $inscripcionCtrl->eliminarInscripcion();
        break;

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
    case 'login':
    default:
        $loginControlador->mostrarLogin();
        break;
}