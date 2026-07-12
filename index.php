<?php
session_start();
require_once __DIR__ . '/controlador/LoginControlador.php';

$accion = $_GET['accion'] ?? 'login';
$controlador = new LoginControlador();

switch ($accion) {
    case 'autenticar':
        $controlador->autenticar();
        break;
    case 'registrar':
        $controlador->registrar();
        break;
    case 'dashboard':
        $controlador->mostrarDashboard();
        break;
    case 'logout':
        $controlador->cerrarSesion();
        break;
    case 'login':
    default:
        $controlador->mostrarLogin();
        break;
}
