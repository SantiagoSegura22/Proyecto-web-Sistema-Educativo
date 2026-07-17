<?php
require_once __DIR__ . '/../config/Conexion.php';

class EventoModelo {
    private PDO $conexion;

    public function __construct() {
        $this->conexion = (new Conexion())->conectar();
    }

    public function listarEventos(): array {
        $sql = "SELECT * FROM eventos ORDER BY fecha DESC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerEventoPorId(int $id): array|false {
        $sql = "SELECT * FROM eventos WHERE id = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crearEvento(string $nombre, string $fecha, string $lugar, string $categoria, string $estado, string $descripcion): bool {
        $sql = "INSERT INTO eventos (nombre, fecha, lugar, categoria, estado, descripcion) VALUES (:nombre, :fecha, :lugar, :categoria, :estado, :descripcion)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':lugar', $lugar);
        $stmt->bindParam(':categoria', $categoria);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':descripcion', $descripcion);
        return $stmt->execute();
    }

    public function editarEvento(int $id, string $nombre, string $fecha, string $lugar, string $categoria, string $estado, string $descripcion): bool {
        $sql = "UPDATE eventos SET nombre = :nombre, fecha = :fecha, lugar = :lugar, categoria = :categoria, estado = :estado, descripcion = :descripcion WHERE id = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':lugar', $lugar);
        $stmt->bindParam(':categoria', $categoria);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':descripcion', $descripcion);
        return $stmt->execute();
    }

    public function eliminarEvento(int $id): bool {
        $sql = "DELETE FROM eventos WHERE id = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
