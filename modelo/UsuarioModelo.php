<?php
require_once __DIR__ . '/../config/Conexion.php';

class UsuarioModelo {
    private PDO $conexion;

    public function __construct() {
        $this->conexion = (new Conexion())->conectar();
    }

    public function buscarPorCorreo(string $correo): array|false {
        $sql = "SELECT id, nombre, apellido, correo, password, rol FROM usuarios WHERE correo = :correo LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function correoExiste(string $correo): bool {
        $sql = "SELECT id FROM usuarios WHERE correo = :correo LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function insertar(string $nombre, string $apellido, string $correo, string $passwordHash, string $rol): bool {
        $sql = "INSERT INTO usuarios (nombre, apellido, correo, password, rol) VALUES (:nombre, :apellido, :correo, :password, :rol)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido', $apellido);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':password', $passwordHash);
        $stmt->bindParam(':rol', $rol);
        return $stmt->execute();
    }
}
