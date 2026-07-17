<?php
require_once __DIR__ . '/../modelo/UsuarioModelo.php';

class LoginControlador {
    private UsuarioModelo $usuarioModelo;

    public function __construct() {
        $this->usuarioModelo = new UsuarioModelo();
    }

    public function mostrarLogin(): void {
        if (isset($_SESSION['usuario'])) {
            header('Location: index.php?accion=dashboard');
            exit;
        }
        require_once __DIR__ . '/../vista/login/login.php';
    }

    public function autenticar(): void {
        $datos = $this->leerDatos();

        $correo   = trim($datos['correo'] ?? '');
        $password = $datos['password'] ?? '';

        if ($correo === '' || $password === '') {
            $this->json(['success' => false, 'error' => 'Debes ingresar correo y contraseña.'], 400);
        }

        $usuario = $this->usuarioModelo->buscarPorCorreo($correo);

        if ($usuario && password_verify($password, $usuario['password'])) {
            session_regenerate_id(true);
            $_SESSION['usuario'] = [
                'id'       => $usuario['id'],
                'nombre'   => $usuario['nombre'],
                'apellido' => $usuario['apellido'],
                'correo'   => $usuario['correo'],
                'rol'      => $usuario['rol'],
            ];
            $this->json(['success' => true, 'redirigir' => 'index.php?accion=dashboard']);
        }

        $this->json(['success' => false, 'error' => 'Correo o contraseña incorrectos.'], 401);
    }

    public function registrar(): void {
        $datos = $this->leerDatos();

        $nombre   = trim($datos['nombre'] ?? '');
        $apellido = trim($datos['apellido'] ?? '');
        $correo   = trim($datos['correo'] ?? '');
        $password = $datos['password'] ?? '';
        $rol      = trim($datos['rol'] ?? 'Estudiante');

        if ($nombre === '' || $apellido === '' || $correo === '' || $password === '') {
            $this->json(['success' => false, 'error' => 'Todos los campos son obligatorios.'], 400);
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $this->json(['success' => false, 'error' => 'El correo no tiene un formato válido.'], 400);
        }

        if (strlen($password) < 6) {
            $this->json(['success' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres.'], 400);
        }

        if ($this->usuarioModelo->correoExiste($correo)) {
            $this->json(['success' => false, 'error' => 'Ya existe una cuenta registrada con ese correo.'], 409);
        }

        $hash   = password_hash($password, PASSWORD_DEFAULT);
        $creado = $this->usuarioModelo->insertar($nombre, $apellido, $correo, $hash, $rol);

        if ($creado) {
            $this->json(['success' => true, 'message' => 'Cuenta creada correctamente. Ya puedes iniciar sesión.']);
        }

        $this->json(['success' => false, 'error' => 'No se pudo crear la cuenta. Intenta de nuevo.'], 500);
    }

    public function mostrarDashboard(): void {
        if (!isset($_SESSION['usuario'])) {
            header('Location: index.php?accion=login');
            exit;
        }
        $usuario = $_SESSION['usuario'];
        require_once __DIR__ . '/../vista/dashboard/dashboard.php';
    }

    public function cerrarSesion(): void {
        $_SESSION = [];
        session_unset();
        session_destroy();
        header('Location: index.php?accion=login');
        exit;
    }

    private function leerDatos(): array {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            return json_decode(file_get_contents('php://input'), true) ?? [];
        }
        return $_POST;
    }

    private function json(array $datos, int $codigo = 200): void {
        http_response_code($codigo);
        header('Content-Type: application/json');
        echo json_encode($datos);
        exit;
    }
}
