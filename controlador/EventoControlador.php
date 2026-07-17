<?php
require_once __DIR__ . '/../modelo/EventoModelo.php';

class EventoControlador {
    private EventoModelo $eventoModelo;

    public function __construct() {
        $this->eventoModelo = new EventoModelo();
    }

    public function mostrarVistaEventos(): void {
        // Verificar si el usuario está autenticado
        if (!isset($_SESSION['usuario'])) {
            header('Location: index.php?accion=login');
            exit;
        }

        $usuario = $_SESSION['usuario'];
        require_once __DIR__ . '/../vista/eventos/eventos.php';
    }

    public function listarEventos(): void {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $eventos = $this->eventoModelo->listarEventos();
            echo json_encode(['success' => true, 'data' => $eventos]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al obtener eventos: ' . $e->getMessage()]);
        }
        exit;
    }

    public function crearEvento(): void {
        $this->procesarFormularioEvento('crear');
    }

    public function editarEvento(): void {
        $this->procesarFormularioEvento('editar');
    }

    public function eliminarEvento(): void {
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Método no permitido.']);
            exit;
        }

        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true) ?? [];
        $id = $data['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID de evento requerido.']);
            exit;
        }

        try {
            $resultado = $this->eventoModelo->eliminarEvento($id);
            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Evento eliminado correctamente.']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'No se pudo eliminar el evento.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al eliminar el evento: ' . $e->getMessage()]);
        }
        exit;
    }

    private function procesarFormularioEvento(string $accion): void {
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Método no permitido.']);
            exit;
        }

        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true) ?? [];

        $id = $data['id'] ?? null;
        $nombre = trim($data['nombre'] ?? '');
        $fecha = trim($data['fecha'] ?? '');
        $lugar = trim($data['lugar'] ?? '');
        $categoria = trim($data['categoria'] ?? '');
        $estado = trim($data['estado'] ?? '');
        $descripcion = trim($data['descripcion'] ?? '');

        if ($nombre === '' || $fecha === '' || $lugar === '' || $categoria === '' || $estado === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Todos los campos obligatorios deben ser completados.']);
            exit;
        }

        try {
            if ($accion === 'crear') {
                $resultado = $this->eventoModelo->crearEvento($nombre, $fecha, $lugar, $categoria, $estado, $descripcion);
                $mensaje = 'Evento creado correctamente.';
            } else if ($accion === 'editar') {
                if (!$id) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'ID de evento requerido para editar.']);
                    exit;
                }
                $resultado = $this->eventoModelo->editarEvento($id, $nombre, $fecha, $lugar, $categoria, $estado, $descripcion);
                $mensaje = 'Evento actualizado correctamente.';
            }

            if (isset($resultado) && $resultado) {
                echo json_encode(['success' => true, 'message' => $mensaje]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'No se pudo guardar el evento.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
        exit;
    }
}
