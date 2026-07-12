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

        // Leer entrada cruda (soporta JSON enviado por fetch)
        $rawInput = file_get_contents('php://input');
        $data = [];
        if (!empty($rawInput)) {
            $decoded = json_decode($rawInput, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $data = $decoded;
            }
        }

        // Priorizar datos JSON, luego _POST (compatibilidad con envío clásico)
        $correo = trim($data['correo'] ?? $_POST['correo'] ?? '');
        $password = $data['password'] ?? $_POST['password'] ?? '';

        // Detectar si el cliente espera/ha enviado JSON
        $isJsonRequest = (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false)
                         || (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);

        if ($correo === '' || $password === '') {
            if ($isJsonRequest) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Debes ingresar correo y contraseña.']);
                exit;
            }

            $_SESSION['login_error'] = 'Debes ingresar correo y contraseña.';
            header('Location: index.php?accion=login');
            exit;
        }

        try {
            $usuario = $this->usuarioModelo->buscarPorCorreo($correo);
        } catch (Exception $e) {
            if ($isJsonRequest) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Error interno del servidor.']);
                exit;
            }
            $_SESSION['login_error'] = 'Error interno del servidor.';
            header('Location: index.php?accion=login');
            exit;
        }

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

            if ($isJsonRequest) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => true, 'message' => 'Autenticado correctamente.']);
                exit;
            }

            header('Location: index.php?accion=dashboard');
            exit;
        }

        if ($isJsonRequest) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Correo o contraseña incorrectos.']);
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

        // Soporta JSON en el body (fetch) y también formulario clásico
        $rawInput = file_get_contents('php://input');
        $data = [];
        if (!empty($rawInput)) {
            $decoded = json_decode($rawInput, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $data = $decoded;
            }
        }

        $nombre = trim($data['nombre'] ?? $_POST['nombre'] ?? '');
        $apellido = trim($data['apellido'] ?? $_POST['apellido'] ?? '');
        $correo = trim($data['correo'] ?? $_POST['correo'] ?? '');
        $password = $data['password'] ?? $_POST['password'] ?? '';
        $rol = trim($data['rol'] ?? $_POST['rol'] ?? 'Estudiante');

        $isJsonRequest = (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false)
                         || (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);

        if ($nombre === '' || $apellido === '' || $correo === '' || $password === '') {
            if ($isJsonRequest) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Todos los campos son obligatorios.']);
                exit;
            }
            $_SESSION['registro_error'] = 'Todos los campos son obligatorios.';
            header('Location: index.php?accion=login&tab=registro');
            exit;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            if ($isJsonRequest) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'El correo no tiene un formato válido.']);
                exit;
            }
            $_SESSION['registro_error'] = 'El correo no tiene un formato válido.';
            header('Location: index.php?accion=login&tab=registro');
            exit;
        }

        if (strlen($password) < 6) {
            if ($isJsonRequest) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres.']);
                exit;
            }
            $_SESSION['registro_error'] = 'La contraseña debe tener al menos 6 caracteres.';
            header('Location: index.php?accion=login&tab=registro');
            exit;
        }

        if ($this->usuarioModelo->correoExiste($correo)) {
            if ($isJsonRequest) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(409);
                echo json_encode(['success' => false, 'error' => 'Ya existe una cuenta registrada con ese correo.']);
                exit;
            }
            $_SESSION['registro_error'] = 'Ya existe una cuenta registrada con ese correo.';
            header('Location: index.php?accion=login&tab=registro');
            exit;
        }

        try {
            $creado = $this->usuarioModelo->insertar($nombre, $apellido, $correo, $password, $rol);
        } catch (Exception $e) {
            if ($isJsonRequest) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Error interno al crear la cuenta.']);
                exit;
            }
            $_SESSION['registro_error'] = 'No se pudo crear la cuenta. Intenta de nuevo.';
            header('Location: index.php?accion=login');
            exit;
        }

        if ($creado) {
            if ($isJsonRequest) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(201);
                echo json_encode(['success' => true, 'message' => 'Cuenta creada correctamente. Ya puedes iniciar sesión.']);
                exit;
            }
            $_SESSION['registro_exito'] = 'Cuenta creada correctamente. Ya puedes iniciar sesión.';
        } else {
            if ($isJsonRequest) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'No se pudo crear la cuenta. Intenta de nuevo.']);
                exit;
            }
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