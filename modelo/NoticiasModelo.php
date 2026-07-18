<?php
require_once __DIR__ . '/../config/Conexion.php';

class NoticiasModelo {
    private PDO $conexion;

    public function __construct() {
        $this->conexion = (new Conexion())->conectar();
    }

    public function listarEventosParaNoticias(): array {
        $sql = "SELECT id, nombre, fecha, lugar, categoria, estado, descripcion FROM eventos ORDER BY fecha DESC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
