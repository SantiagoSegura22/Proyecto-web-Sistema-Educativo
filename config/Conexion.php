<?php
class Conexion {
    private string $host = "localhost";
    private string $database = "sistema_eventos"; 
    private string $port = "3306";
    private string $user = "root";
    private string $password = "";

    public function conectar(): PDO {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->database};port={$this->port}";
            $conexion = new PDO($dsn, $this->user, $this->password);
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conexion;
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
}
