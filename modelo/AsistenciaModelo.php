<?php

// modelo/AsistenciaModelo.php

class AsistenciaModelo {

    private $conexion;

    public function __construct() {
        // Conexion a la base de datos
        // Ajusta host, usuario, contraseña si los cambiaste en XAMPP
        $this->conexion = new mysqli('localhost', 'root', '', 'sistema_eventos');

        if ($this->conexion->connect_error) {
            die(json_encode([
                'error' => 'Error de conexión: ' . $this->conexion->connect_error
            ]));
        }

        $this->conexion->set_charset('utf8mb4');
    }

    // Obtener todos los registros con filtros opcionales
    
    public function obtenerTodos($evento = '', $estado = '', $fecha = '') {

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

        if (!empty($params)) {
            $stmt->bind_param($tipos, ...$params);
        }

        $stmt->execute();
        $resultado = $stmt->get_result();

        $registros = [];
        while ($fila = $resultado->fetch_assoc()) {
            $registros[] = $fila;
        }

        $stmt->close();
        return $registros;
    }


    // Obtener un registro por ID
  
    public function obtenerPorId($id) {

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
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $registro  = $resultado->fetch_assoc();
        $stmt->close();

        return $registro;
    }

    
    // Actualizar estado y observaciones de un registro
   
    public function actualizar($id, $estado, $observaciones) {

        $sql = "UPDATE asistencia 
                SET estado = ?, observaciones = ?
                WHERE id = ?";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param('ssi', $estado, $observaciones, $id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    
    // Obtener estadísticas globales
    
    public function obtenerEstadisticas() {

        $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(estado = 'Presente')  AS presentes,
                    SUM(estado = 'Ausente')   AS ausentes,
                    SUM(estado = 'Tardanza')  AS tardanzas
                FROM asistencia";

        $resultado = $this->conexion->query($sql);
        return $resultado->fetch_assoc();
    }

  
    // Lista de eventos distintos (para el filtro)
  
    public function obtenerEventos() {

        $sql = "SELECT DISTINCT evento FROM asistencia ORDER BY evento ASC";
        $resultado = $this->conexion->query($sql);

        $eventos = [];
        while ($fila = $resultado->fetch_assoc()) {
            $eventos[] = $fila['evento'];
        }

        return $eventos;
    }
}