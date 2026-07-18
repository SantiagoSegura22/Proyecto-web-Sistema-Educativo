<?php
require_once __DIR__ . '/../modelo/NoticiasModelo.php';

class NoticiasControlador {
    private NoticiasModelo $noticiasModelo;

    public function __construct() {
        $this->noticiasModelo = new NoticiasModelo();
    }

    public function mostrarVistaNoticias(): void {
        if (!isset($_SESSION['usuario'])) {
            header('Location: index.php?accion=login');
            exit;
        }

        $usuario = $_SESSION['usuario'];
        require_once __DIR__ . '/../vista/noticias/noticias.php';
    }

    public function listarNoticias(): void {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $eventos = $this->noticiasModelo->listarEventosParaNoticias();
            echo json_encode(['success' => true, 'data' => $eventos]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al obtener noticias: ' . $e->getMessage()]);
        }
        exit;
    }
}
