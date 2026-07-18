<?php
require_once __DIR__ . '/../modelo/InscripcionModelo.php';

class InscripcionControlador {
    private InscripcionModelo $modelo;

    public function __construct() {
        $this->modelo = new InscripcionModelo();
    }

    public function mostrarVista(): void {
        if (!isset($_SESSION['usuario'])) {
            header('Location: index.php?accion=login');
            exit;
        }

        $usuario = $_SESSION['usuario'];
        require_once __DIR__ . '/../vista/inscripciones/inscripciones.php';
    }

    public function listarInscripciones(): void {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $eventoId = isset($_GET['evento_id']) && $_GET['evento_id'] !== '' ? (int)$_GET['evento_id'] : null;
            $estado = trim($_GET['estado'] ?? '');
            $busqueda = trim($_GET['busqueda'] ?? '');

            $inscripciones = $this->modelo->listarInscripciones(
                $eventoId ?: null,
                $estado !== '' ? $estado : null,
                $busqueda !== '' ? $busqueda : null
            );

            echo json_encode(['success' => true, 'data' => $inscripciones]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al obtener inscripciones: ' . $e->getMessage()]);
        }

        exit;
    }

    public function obtenerEstadisticas(): void {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $stats = $this->modelo->obtenerEstadisticas();
            echo json_encode(['success' => true, 'data' => $stats]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al obtener estadísticas: ' . $e->getMessage()]);
        }

        exit;
    }

    public function obtenerEventos(): void {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $eventos = $this->modelo->listarEventos();
            echo json_encode(['success' => true, 'data' => $eventos]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al obtener eventos: ' . $e->getMessage()]);
        }

        exit;
    }

    public function obtenerUna(): void {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'ID de inscripción inválido.']);
                exit;
            }

            $inscripcion = $this->modelo->obtenerInscripcionPorId($id);
            if ($inscripcion) {
                echo json_encode(['success' => true, 'data' => $inscripcion]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Inscripción no encontrada.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al obtener la inscripción: ' . $e->getMessage()]);
        }

        exit;
    }

    public function crearInscripcion(): void {
        $this->procesarFormulario('crear');
    }

    public function editarInscripcion(): void {
        $this->procesarFormulario('editar');
    }

    public function eliminarInscripcion(): void {
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
            echo json_encode(['success' => false, 'error' => 'ID de inscripción requerido.']);
            exit;
        }

        try {
            $resultado = $this->modelo->eliminarInscripcion((int)$id);
            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Inscripción eliminada correctamente.']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'No se pudo eliminar la inscripción.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al eliminar la inscripción: ' . $e->getMessage()]);
        }

        exit;
    }

    private function procesarFormulario(string $accion): void {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Método no permitido.']);
            exit;
        }

        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true) ?? [];

        $id = $data['id'] ?? null;
        $eventoId = isset($data['evento_id']) ? (int)$data['evento_id'] : 0;
        $nombre = trim($data['nombre'] ?? '');
        $apellido = trim($data['apellido'] ?? '');
        $correo = trim($data['correo'] ?? '');
        $carrera = trim($data['carrera'] ?? '');
        $fechaInscripcion = trim($data['fecha_inscripcion'] ?? '');
        $horaEvento = trim($data['hora_evento'] ?? '');
        $estado = trim($data['estado'] ?? 'Activo');

        if ($eventoId <= 0 || $nombre === '' || $apellido === '' || $correo === '' || $fechaInscripcion === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Completa los campos obligatorios.']);
            exit;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'El correo ingresado no es válido.']);
            exit;
        }

        try {
            if ($accion === 'crear') {
                $resultado = $this->modelo->crearInscripcion($eventoId, $nombre, $apellido, $correo, $carrera !== '' ? $carrera : null, $fechaInscripcion, $horaEvento, $estado);
                $mensaje = 'Inscripción registrada correctamente.';
            } else {
                if (!$id) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'ID de inscripción requerido para editar.']);
                    exit;
                }

                $resultado = $this->modelo->editarInscripcion((int)$id, $eventoId, $nombre, $apellido, $correo, $carrera !== '' ? $carrera : null, $fechaInscripcion, $horaEvento, $estado);
                $mensaje = 'Inscripción actualizada correctamente.';
            }

            if ($resultado) {
                echo json_encode(['success' => true, 'message' => $mensaje]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'No se pudo guardar la inscripción.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }

        exit;
    }
}
