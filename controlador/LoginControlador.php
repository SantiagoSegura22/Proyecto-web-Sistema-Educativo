<?php
require_once __DIR__ . '/../modelo/UsuarioModelo.php';

class LoginControlador {
    private UsuarioModelo $usuarioModelo;

    public function __construct() {
        $this->usuarioModelo = new UsuarioModelo();
    }

    public function mostrarLogin(): void {
        // Si ya inició sesión, no tiene sentido ver el login de nuevo
        if (isset($_SESSION['usuario'])) {
            header('Location: index.php?accion=dashboard');
            exit;
        }

        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);

        $errorRegistro = $_SESSION['registro_error'] ?? null;
        unset($_SESSION['registro_error']);

        $exitoRegistro = $_SESSION['registro_exito'] ?? null;
        unset($_SESSION['registro_exito']);

        require_once __DIR__ . '/../vista/login/login.php';
    }

    public function autenticar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?accion=login');
            exit;
        }

        $correo = trim($_POST['correo'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($correo === '' || $password === '') {
            $_SESSION['login_error'] = 'Debes ingresar correo y contraseña.';
            header('Location: index.php?accion=login');
            exit;
        }

        $usuario = $this->usuarioModelo->buscarPorCorreo($correo);

        if ($usuario && $password === $usuario['password']) {
            // Regenerar el ID de sesión al autenticar (evita fijación de sesión)
            session_regenerate_id(true);

            $_SESSION['usuario'] = [
                'id'       => $usuario['id'],
                'nombre'   => $usuario['nombre'],
                'apellido' => $usuario['apellido'],
                'correo'   => $usuario['correo'],
                'rol'      => $usuario['rol'],
            ];
            header('Location: index.php?accion=dashboard');
            exit;
        }

        $_SESSION['login_error'] = 'Correo o contraseña incorrectos.';
        header('Location: index.php?accion=login');
        exit;
    }

    public function mostrarDashboard(): void {
        // Este es el candado real: sin sesión, no hay dashboard, sin importar la URL que se escriba
        if (!isset($_SESSION['usuario'])) {
            header('Location: index.php?accion=login');
            exit;
        }

        $usuario = $_SESSION['usuario'];
        require_once __DIR__ . '/../vista/dashboard/dashboard.php';
    }

    public function registrar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?accion=login');
            exit;
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $password = $_POST['password'] ?? '';
        $rol = trim($_POST['rol'] ?? 'Estudiante');

        if ($nombre === '' || $apellido === '' || $correo === '' || $password === '') {
            $_SESSION['registro_error'] = 'Todos los campos son obligatorios.';
            header('Location: index.php?accion=login&tab=registro');
            exit;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['registro_error'] = 'El correo no tiene un formato válido.';
            header('Location: index.php?accion=login&tab=registro');
            exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['registro_error'] = 'La contraseña debe tener al menos 6 caracteres.';
            header('Location: index.php?accion=login&tab=registro');
            exit;
        }

        if ($this->usuarioModelo->correoExiste($correo)) {
            $_SESSION['registro_error'] = 'Ya existe una cuenta registrada con ese correo.';
            header('Location: index.php?accion=login&tab=registro');
            exit;
        }

        $creado = $this->usuarioModelo->insertar($nombre, $apellido, $correo, $password, $rol);

        if ($creado) {
            $_SESSION['registro_exito'] = 'Cuenta creada correctamente. Ya puedes iniciar sesión.';
        } else {
            $_SESSION['registro_error'] = 'No se pudo crear la cuenta. Intenta de nuevo.';
        }

        header('Location: index.php?accion=login');
        exit;
    }

    public function cerrarSesion(): void {
        $_SESSION = [];
        session_unset();
        session_destroy();
        header('Location: index.php?accion=login');
        exit;
    }
}