<?php
require_once __DIR__ . '/../config/Conexion.php';

class InscripcionModelo {
    private PDO $conexion;

    public function __construct() {
        $this->conexion = (new Conexion())->conectar();
        $this->asegurarColumnasInscripciones();
        $this->asegurarColumnaAsistencia();
    }

    private function asegurarColumnasInscripciones(): void {
        try {
            $this->conexion->exec("ALTER TABLE inscripciones ADD COLUMN IF NOT EXISTS hora_evento TIME DEFAULT NULL");
        } catch (PDOException $e) {
            // Ignorar si la columna ya existe o la estructura no permite el cambio.
        }
    }

    private function asegurarColumnaAsistencia(): void {
        try {
            $this->conexion->exec("ALTER TABLE asistencia ADD COLUMN IF NOT EXISTS inscripcion_id INT DEFAULT NULL");
            $this->conexion->exec("ALTER TABLE asistencia ADD COLUMN IF NOT EXISTS estado_inscripcion VARCHAR(50) DEFAULT NULL");
        } catch (PDOException $e) {
            // Ignorar si la columna ya existe o la estructura no permite el cambio.
        }
    }

    public function listarInscripciones(?int $eventoId = null, ?string $estado = null, ?string $busqueda = null): array {
        $sql = "SELECT i.id, i.evento_id, e.nombre AS evento_nombre, i.nombre, i.apellido, i.correo, i.carrera, i.fecha_inscripcion, i.hora_evento, i.estado, i.creado_en
                FROM inscripciones i
                LEFT JOIN eventos e ON e.id = i.evento_id
                WHERE 1=1";

        $params = [];

        if ($eventoId !== null) {
            $sql .= ' AND i.evento_id = :evento_id';
            $params[':evento_id'] = $eventoId;
        }

        if ($estado !== null && $estado !== '') {
            $sql .= ' AND i.estado = :estado';
            $params[':estado'] = $estado;
        }

        if ($busqueda !== null && $busqueda !== '') {
            $sql .= ' AND (
                LOWER(i.nombre) LIKE :busqueda OR
                LOWER(i.apellido) LIKE :busqueda OR
                LOWER(i.correo) LIKE :busqueda OR
                LOWER(COALESCE(i.carrera, "")) LIKE :busqueda OR
                LOWER(COALESCE(e.nombre, "")) LIKE :busqueda
            )';
            $params[':busqueda'] = '%' . mb_strtolower($busqueda) . '%';
        }

        $sql .= ' ORDER BY i.fecha_inscripcion DESC, i.id DESC';

        $stmt = $this->conexion->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
            if ($key === ':evento_id') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarEventos(): array {
        $sql = 'SELECT id, nombre FROM eventos ORDER BY nombre ASC';
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerInscripcionPorId(int $id): array|false {
        $sql = "SELECT i.id, i.evento_id, e.nombre AS evento_nombre, i.nombre, i.apellido, i.correo, i.carrera, i.fecha_inscripcion, i.hora_evento, i.estado
                FROM inscripciones i
                LEFT JOIN eventos e ON e.id = i.evento_id
                WHERE i.id = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerEstadisticas(): array {
        $sql = "SELECT
                    COUNT(*) AS total_inscripciones,
                    COUNT(DISTINCT evento_id) AS eventos_con_inscripciones,
                    SUM(CASE WHEN estado = 'Activo' THEN 1 ELSE 0 END) AS inscripciones_activas,
                    SUM(CASE WHEN estado = 'Cancelado' THEN 1 ELSE 0 END) AS inscripciones_canceladas
                FROM inscripciones";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function crearInscripcion(int $eventoId, string $nombre, string $apellido, string $correo, ?string $carrera, string $fechaInscripcion, string $horaEvento, string $estado): bool {
        $sql = 'INSERT INTO inscripciones (evento_id, nombre, apellido, correo, carrera, fecha_inscripcion, hora_evento, estado)
                VALUES (:evento_id, :nombre, :apellido, :correo, :carrera, :fecha_inscripcion, :hora_evento, :estado)';
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':evento_id', $eventoId, PDO::PARAM_INT);
        $stmt->bindValue(':nombre', $nombre);
        $stmt->bindValue(':apellido', $apellido);
        $stmt->bindValue(':correo', $correo);
        $stmt->bindValue(':carrera', $carrera);
        $stmt->bindValue(':fecha_inscripcion', $fechaInscripcion);
        $stmt->bindValue(':hora_evento', $horaEvento !== '' ? $horaEvento : null);
        $stmt->bindValue(':estado', $estado);

        if (!$stmt->execute()) {
            return false;
        }

        $inscripcionId = (int)$this->conexion->lastInsertId();
        $this->sincronizarAsistenciaDesdeInscripcion($inscripcionId, $eventoId, $nombre, $apellido, $correo, $fechaInscripcion, $horaEvento, $estado);
        return true;
    }

    public function editarInscripcion(int $id, int $eventoId, string $nombre, string $apellido, string $correo, ?string $carrera, string $fechaInscripcion, string $horaEvento, string $estado): bool {
        $sql = 'UPDATE inscripciones
                SET evento_id = :evento_id,
                    nombre = :nombre,
                    apellido = :apellido,
                    correo = :correo,
                    carrera = :carrera,
                    fecha_inscripcion = :fecha_inscripcion,
                    hora_evento = :hora_evento,
                    estado = :estado
                WHERE id = :id';
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':evento_id', $eventoId, PDO::PARAM_INT);
        $stmt->bindValue(':nombre', $nombre);
        $stmt->bindValue(':apellido', $apellido);
        $stmt->bindValue(':correo', $correo);
        $stmt->bindValue(':carrera', $carrera);
        $stmt->bindValue(':fecha_inscripcion', $fechaInscripcion);
        $stmt->bindValue(':hora_evento', $horaEvento !== '' ? $horaEvento : null);
        $stmt->bindValue(':estado', $estado);

        if (!$stmt->execute()) {
            return false;
        }

        $this->sincronizarAsistenciaDesdeInscripcion($id, $eventoId, $nombre, $apellido, $correo, $fechaInscripcion, $horaEvento, $estado);
        return true;
    }

    public function eliminarInscripcion(int $id): bool {
        $sql = 'DELETE FROM inscripciones WHERE id = :id';
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $resultado = $stmt->execute();

        if ($resultado) {
            $this->eliminarAsistenciaPorInscripcion($id);
        }

        return $resultado;
    }

    private function sincronizarAsistenciaDesdeInscripcion(int $inscripcionId, int $eventoId, string $nombre, string $apellido, string $correo, string $fechaInscripcion, string $horaEvento, string $estado): void {
        $usuarioId = $this->obtenerOCrearUsuarioParticipante($nombre, $apellido, $correo);
        $eventoNombre = $this->obtenerNombreEvento($eventoId);
        $estadoAsistencia = $estado === 'Cancelado' ? 'Ausente' : 'Presente';

        $stmt = $this->conexion->prepare('SELECT id FROM asistencia WHERE inscripcion_id = :inscripcion_id LIMIT 1');
        $stmt->bindValue(':inscripcion_id', $inscripcionId, PDO::PARAM_INT);
        $stmt->execute();
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($fila) {
            $update = $this->conexion->prepare('UPDATE asistencia SET usuario_id = :usuario_id, evento = :evento, fecha = :fecha, hora = :hora, estado = :estado, observaciones = :observaciones, estado_inscripcion = :estado_inscripcion WHERE inscripcion_id = :inscripcion_id');
            $update->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $update->bindValue(':evento', $eventoNombre);
            $update->bindValue(':fecha', $fechaInscripcion);
            $update->bindValue(':hora', $horaEvento !== '' ? $horaEvento : '00:00:00');
            $update->bindValue(':estado', $estadoAsistencia);
            $update->bindValue(':observaciones', 'Registrado desde el módulo de inscripciones');
            $update->bindValue(':estado_inscripcion', $estado);
            $update->bindValue(':inscripcion_id', $inscripcionId, PDO::PARAM_INT);
            $update->execute();
            return;
        }

        $insert = $this->conexion->prepare('INSERT INTO asistencia (inscripcion_id, usuario_id, evento, fecha, hora, estado, observaciones, estado_inscripcion) VALUES (:inscripcion_id, :usuario_id, :evento, :fecha, :hora, :estado, :observaciones, :estado_inscripcion)');
        $insert->bindValue(':inscripcion_id', $inscripcionId, PDO::PARAM_INT);
        $insert->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $insert->bindValue(':evento', $eventoNombre);
        $insert->bindValue(':fecha', $fechaInscripcion);
        $insert->bindValue(':hora', $horaEvento !== '' ? $horaEvento : '00:00:00');
        $insert->bindValue(':estado', $estadoAsistencia);
        $insert->bindValue(':observaciones', 'Registrado desde el módulo de inscripciones');
        $insert->bindValue(':estado_inscripcion', $estado);
        $insert->execute();
    }

    private function obtenerOCrearUsuarioParticipante(string $nombre, string $apellido, string $correo): int {
        $stmt = $this->conexion->prepare('SELECT id FROM usuarios WHERE correo = :correo LIMIT 1');
        $stmt->bindValue(':correo', $correo);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            return (int)$usuario['id'];
        }

        $passwordHash = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
        $insert = $this->conexion->prepare('INSERT INTO usuarios (nombre, apellido, correo, password, rol) VALUES (:nombre, :apellido, :correo, :password, :rol)');
        $insert->bindValue(':nombre', $nombre);
        $insert->bindValue(':apellido', $apellido);
        $insert->bindValue(':correo', $correo);
        $insert->bindValue(':password', $passwordHash);
        $insert->bindValue(':rol', 'Estudiante');
        $insert->execute();

        return (int)$this->conexion->lastInsertId();
    }

    private function obtenerNombreEvento(int $eventoId): string {
        $stmt = $this->conexion->prepare('SELECT nombre FROM eventos WHERE id = :id LIMIT 1');
        $stmt->bindValue(':id', $eventoId, PDO::PARAM_INT);
        $stmt->execute();
        $evento = $stmt->fetch(PDO::FETCH_ASSOC);
        return $evento['nombre'] ?? 'Sin evento';
    }

    private function eliminarAsistenciaPorInscripcion(int $inscripcionId): void {
        $stmt = $this->conexion->prepare('DELETE FROM asistencia WHERE inscripcion_id = :inscripcion_id');
        $stmt->bindValue(':inscripcion_id', $inscripcionId, PDO::PARAM_INT);
        $stmt->execute();
    }
}
