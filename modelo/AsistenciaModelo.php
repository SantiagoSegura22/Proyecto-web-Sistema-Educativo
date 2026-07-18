<?php

// modelo/AsistenciaModelo.php

class AsistenciaModelo {

    private $conexion;
    private $connected = false;
    private $lastError = '';

    public function __construct() {
        // Conexion a la base de datos
        $this->conexion = new mysqli('localhost', 'root', '', 'sistema_eventos');

        if ($this->conexion->connect_error) {
            // No matar la petición completa. Guardar error y marcar desconectado.
            $this->lastError = 'Error de conexión: ' . $this->conexion->connect_error;
            $this->conexion = null;
            $this->connected = false;
            return;
        }

        $this->conexion->set_charset('utf8mb4');
        $this->connected = true;
    }

    public function isConnected() {
        return !empty($this->connected) && $this->conexion instanceof mysqli;
    }

    // Obtener todos los registros con filtros opcionales
    
    public function obtenerTodos($evento = '', $estado = '', $fecha = '') {

        if (!$this->isConnected()) {
            return false; // Indica error de conexión
        }

        $sql = "SELECT 
                    a.id,
                    a.evento,
                    a.fecha,
                    a.hora,
                    a.estado,
                    a.observaciones,
                    u.nombre,
                    u.apellido,
                    u.correo
                FROM asistencia a
                INNER JOIN usuarios u ON a.usuario_id = u.id
                WHERE 1=1";

        $params = [];
        $tipos  = '';

        if (!empty($evento)) {
            $sql     .= " AND a.evento = ?";
            $params[] = $evento;
            $tipos   .= 's';
        }

        if (!empty($estado)) {
            $sql     .= " AND a.estado = ?";
            $params[] = $estado;
            $tipos   .= 's';
        }

        if (!empty($fecha)) {
            $sql     .= " AND a.fecha = ?";
            $params[] = $fecha;
            $tipos   .= 's';
        }

        $sql .= " ORDER BY a.fecha DESC, a.hora DESC";

        $stmt = $this->conexion->prepare($sql);
        if ($stmt === false) {
            $this->lastError = $this->conexion->error ?? 'Error al preparar consulta';
            return false;
        }

        if (!empty($params)) {
            $stmt->bind_param($tipos, ...$params);
        }

        if (!$stmt->execute()) {
            $this->lastError = $stmt->error;
            $stmt->close();
            return false;
        }

        $registros = [];

        // Compatibilidad: usar get_result() si está disponible, sino bind_result()
        if (method_exists($stmt, 'get_result')) {
            $resultado = $stmt->get_result();
            if ($resultado === false) {
                $this->lastError = $stmt->error;
                $stmt->close();
                return false;
            }
            while ($fila = $resultado->fetch_assoc()) {
                $registros[] = $fila;
            }
        } else {
            // Fallback sin get_result
            $stmt->store_result();
            $stmt->bind_result($id, $eventoCol, $fechaCol, $hora, $estadoCol, $observaciones, $nombre, $apellido, $correo);
            while ($stmt->fetch()) {
                $registros[] = [
                    'id' => $id,
                    'evento' => $eventoCol,
                    'fecha' => $fechaCol,
                    'hora' => $hora,
                    'estado' => $estadoCol,
                    'observaciones' => $observaciones,
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'correo' => $correo
                ];
            }
        }

        $stmt->close();
        return $registros;
    }


    // Obtener un registro por ID
  
    public function obtenerPorId($id) {

        if (!$this->isConnected()) {
            return null;
        }

        $sql = "SELECT 
                    a.id,
                    a.evento,
                    a.fecha,
                    a.hora,
                    a.estado,
                    a.observaciones,
                    u.nombre,
                    u.apellido,
                    u.correo
                FROM asistencia a
                INNER JOIN usuarios u ON a.usuario_id = u.id
                WHERE a.id = ?";

        $stmt = $this->conexion->prepare($sql);
        if ($stmt === false) {
            $this->lastError = $this->conexion->error ?? 'Error al preparar consulta';
            return null;
        }

        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) {
            $this->lastError = $stmt->error;
            $stmt->close();
            return null;
        }

        if (!$stmt->execute()) {
            $this->lastError = $stmt->error;
            $stmt->close();
            return null;
        }

        // Compatibilidad con get_result()
        if (method_exists($stmt, 'get_result')) {
            $resultado = $stmt->get_result();
            $registro  = $resultado ? $resultado->fetch_assoc() : null;
        } else {
            $stmt->bind_result($id, $eventoCol, $fechaCol, $hora, $estadoCol, $observaciones, $nombre, $apellido, $correo);
            if ($stmt->fetch()) {
                $registro = [
                    'id' => $id,
                    'evento' => $eventoCol,
                    'fecha' => $fechaCol,
                    'hora' => $hora,
                    'estado' => $estadoCol,
                    'observaciones' => $observaciones,
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'correo' => $correo
                ];
            } else {
                $registro = null;
            }
        }
        $stmt->close();

        return $registro;
    }

    
    // Actualizar estado y observaciones de un registro
   
    public function actualizar($id, $estado, $observaciones) {

        if (!$this->isConnected()) {
            return false;
        }

        $sql = "UPDATE asistencia 
                SET estado = ?, observaciones = ?
                WHERE id = ?";

        $stmt = $this->conexion->prepare($sql);
        if ($stmt === false) {
            $this->lastError = $this->conexion->error ?? 'Error al preparar consulta';
            return false;
        }
        $stmt->bind_param('ssi', $estado, $observaciones, $id);
        $ok = $stmt->execute();
        if (!$ok) {
            $this->lastError = $stmt->error;
        }
        $stmt->close();

        return $ok;
    }

    
    // Obtener estadísticas globales
    
    public function obtenerEstadisticas() {

        if (!$this->isConnected()) {
            return false;
        }

        $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(estado = 'Presente')  AS presentes,
                    SUM(estado = 'Ausente')   AS ausentes,
                    SUM(estado = 'Tardanza')  AS tardanzas
                FROM asistencia";

        $resultado = $this->conexion->query($sql);
        if ($resultado === false) {
            $this->lastError = $this->conexion->error ?? 'Error en consulta';
            return false;
        }
        return $resultado->fetch_assoc();
    }

  
    // Lista de eventos distintos (para el filtro)
  
    public function obtenerEventos() {

        $sql = "SELECT DISTINCT evento FROM asistencia ORDER BY evento ASC";
        $resultado = $this->conexion->query($sql);
        if ($resultado === false) {
            $this->lastError = $this->conexion->error ?? 'Error en consulta';
            return false;
        }

        $eventos = [];
        while ($fila = $resultado->fetch_assoc()) {
            $eventos[] = $fila['evento'];
        }

        return $eventos;
    }
}