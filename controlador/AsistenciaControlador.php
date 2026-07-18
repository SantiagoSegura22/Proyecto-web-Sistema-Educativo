<?php
// =============================================
// controlador/AsistenciaControlador.php
// =============================================

require_once __DIR__ . '/../modelo/AsistenciaModelo.php';

class AsistenciaControlador {

    private $modelo;

    public function __construct() {
        $this->modelo = new AsistenciaModelo();
    }

   
    // Muestra la vista principal de asistencia
    
   public function mostrarVista() {
    // Tomamos el usuario de la sesión
    $usuario        = $_SESSION['usuario'] ?? [];
    $nombreCompleto = htmlspecialchars(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? ''));
    $iniciales      = htmlspecialchars(strtoupper(
                        mb_substr($usuario['nombre']   ?? 'U', 0, 1) .
                        mb_substr($usuario['apellido'] ?? '', 0, 1)
                      ));
    $rol            = htmlspecialchars($usuario['rol'] ?? 'Estudiante');

    require_once __DIR__ . '/../vista/Asistencia/Asistencia.php';
}


    // AJAX: registros con filtros opcionales

    public function obtenerRegistros() {
        header('Content-Type: application/json');

        if (!$this->modelo->isConnected()) {
            echo json_encode(['ok' => false, 'mensaje' => 'Error de conexión a la base de datos']);
            return;
        }

        $evento = $_GET['evento'] ?? '';
        $estado = $_GET['estado'] ?? '';
        $fecha  = $_GET['fecha']  ?? '';

        $registros = $this->modelo->obtenerTodos($evento, $estado, $fecha);

        if ($registros === false) {
            echo json_encode(['ok' => false, 'mensaje' => 'Error al obtener registros']);
            return;
        }

        echo json_encode([
            'ok'        => true,
            'registros' => $registros
        ]);
    }

   
    // AJAX: estadísticas globales
   
    public function obtenerEstadisticas() {
        header('Content-Type: application/json');

        if (!$this->modelo->isConnected()) {
            echo json_encode(['ok' => false, 'mensaje' => 'Error de conexión a la base de datos']);
            return;
        }

        $stats = $this->modelo->obtenerEstadisticas();

        if ($stats === false) {
            echo json_encode(['ok' => false, 'mensaje' => 'Error al obtener estadísticas']);
            return;
        }

        echo json_encode([
            'ok'   => true,
            'data' => $stats
        ]);
    }

   
    // AJAX: un registro por ID (para Ver / Editar)
   
    public function obtenerUno() {
        header('Content-Type: application/json');

        if (!$this->modelo->isConnected()) {
            echo json_encode(['ok' => false, 'mensaje' => 'Error de conexión a la base de datos']);
            return;
        }

        $id = intval($_GET['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['ok' => false, 'mensaje' => 'ID inválido']);
            return;
        }

        $registro = $this->modelo->obtenerPorId($id);

        if (!$registro) {
            echo json_encode(['ok' => false, 'mensaje' => 'Registro no encontrado']);
            return;
        }
        echo json_encode(['ok' => true, 'data' => $registro]);
    }

    // AJAX: actualizar estado y observaciones

    public function actualizar() {
        header('Content-Type: application/json');

        if (!$this->modelo->isConnected()) {
            echo json_encode(['ok' => false, 'mensaje' => 'Error de conexión a la base de datos']);
            return;
        }

        $body          = json_decode(file_get_contents('php://input'), true);
        $id            = intval($body['id']            ?? 0);
        $estado        = $body['estado']               ?? '';
        $observaciones = $body['observaciones']        ?? '';

        if ($id <= 0 || empty($estado)) {
            echo json_encode(['ok' => false, 'mensaje' => 'Datos incompletos']);
            return;
        }

        $ok = $this->modelo->actualizar($id, $estado, $observaciones);

        echo json_encode([
            'ok'      => $ok,
            'mensaje' => $ok ? 'Registro actualizado' : 'Error al actualizar'
        ]);
    }


    // AJAX: lista de eventos para el filtro
    
    public function obtenerEventos() {
        header('Content-Type: application/json');

        if (!$this->modelo->isConnected()) {
            echo json_encode(['ok' => false, 'mensaje' => 'Error de conexión a la base de datos']);
            return;
        }

        $eventos = $this->modelo->obtenerEventos();

        if ($eventos === false) {
            echo json_encode(['ok' => false, 'mensaje' => 'Error al obtener eventos']);
            return;
        }

        echo json_encode(['ok' => true, 'eventos' => $eventos]);
    }
}