<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - Sistema de Eventos Universitarios</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body class="login-body">

<div class="login-main">
    <!-- Lado izquierdo: imagen institucional y eslogan -->
    <div class="login-hero">
        <div class="hero-content">
            <img src="img/logo-ug.png" alt="Universidad de Guayaquil" class="hero-logo">
            <h1>Conecta, Participa, <br><span>¡Celebra!</span></h1>
            <p>Sistema Oficial de Eventos Académicos, Culturales y Deportivos</p>
        </div>
    </div>

    <!-- Lado derecho: formulario de login -->
    <div class="login-form-container">
        <div class="form-card-panel">
            <?php $mostrarRegistro = !empty($errorRegistro) || !empty($exitoRegistro); ?>
            <div class="tabs-wrapper">
                <a href="#" id="tabLoginBtn" class="tab-link <?php echo $mostrarRegistro ? '' : 'active-tab'; ?>">Iniciar Sesión</a>
                <a href="#" id="tabRegisterBtn" class="tab-link <?php echo $mostrarRegistro ? 'active-tab' : ''; ?>">Registrarse</a>
            </div>

            <!-- PANEL LOGIN -->
            <div id="loginPanel" class="auth-panel <?php echo $mostrarRegistro ? 'hidden-panel' : ''; ?>">
                <h2>Bienvenido de nuevo</h2>
                <p class="sub-text">Ingresa tus credenciales</p>

                <?php if (!empty($error)): ?>
                    <p style="color:#c0392b; font-weight:bold; margin-bottom:15px;">
                        <?php echo htmlspecialchars($error); ?>
                    </p>
                <?php endif; ?>

                <form id="loginForm" action="index.php?accion=autenticar" method="POST" novalidate>
                    <div class="input-group">
                        <label for="correo">Correo institucional</label>
                        <div class="input-icon">
                            <i class="fa-solid fa-envelope"></i>
                            <input type="email" id="correo" name="correo" placeholder="demo@ug.edu.ec" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="password">Contraseña</label>
                        <div class="input-icon">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="VivaIsrael" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary full-width">Iniciar Sesión</button>
                </form>
                <div id="loginFeedback" aria-live="polite" style="margin-top:10px;"></div>
            </div>

            <!-- PANEL REGISTRO -->
            <div id="registerPanel" class="auth-panel <?php echo $mostrarRegistro ? '' : 'hidden-panel'; ?>">
                <h2>Crear cuenta</h2>
                <p class="sub-text">Únete al Sistema de Eventos Universitarios</p>

                <?php if (!empty($errorRegistro)): ?>
                    <p style="color:#c0392b; font-weight:bold; margin-bottom:15px;">
                        <?php echo htmlspecialchars($errorRegistro); ?>
                    </p>
                <?php endif; ?>
                <?php if (!empty($exitoRegistro)): ?>
                    <p style="color:#27ae60; font-weight:bold; margin-bottom:15px;">
                        <?php echo htmlspecialchars($exitoRegistro); ?>
                    </p>
                <?php endif; ?>

                <form id="registerForm" action="index.php?accion=registrar" method="POST" novalidate>
                    <div class="row-2cols">
                        <div class="input-group">
                            <label for="reg_nombre">Nombre</label>
                            <div class="input-icon">
                                <i class="fa-solid fa-user"></i>
                                <input type="text" id="reg_nombre" name="nombre" placeholder="Ronald" required>
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="reg_apellido">Apellido</label>
                            <div class="input-icon">
                                <i class="fa-solid fa-user-pen"></i>
                                <input type="text" id="reg_apellido" name="apellido" placeholder="Andagoya" required>
                            </div>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="reg_correo">Correo institucional</label>
                        <div class="input-icon">
                            <i class="fa-solid fa-envelope"></i>
                            <input type="email" id="reg_correo" name="correo" placeholder="tu.correo@ug.edu.ec" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="reg_password">Contraseña</label>
                        <div class="input-icon">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="reg_password" name="password" placeholder="Mínimo 6 caracteres" minlength="6" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="reg_rol">Rol</label>
                        <div class="input-icon">
                            <i class="fa-solid fa-graduation-cap"></i>
                            <select class="form-select-custom" id="reg_rol" name="rol">
                                <option>Estudiante</option>
                                <option>Docente</option>
                                <option>Administrativo</option>
                                <option>Organizador</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary full-width">Crear Cuenta</button>
                </form>
                <div id="registerFeedback" aria-live="polite" style="margin-top:10px;"></div>
            </div>

            <div class="footer-note">
                <p>Universidad de Guayaquil</p>
            </div>
        </div>
    </div>
</div>

<script>
    const loginBtn = document.getElementById('tabLoginBtn');
    const registerBtn = document.getElementById('tabRegisterBtn');
    const loginPanel = document.getElementById('loginPanel');
    const registerPanel = document.getElementById('registerPanel');

    function showLogin() {
        loginPanel.classList.remove('hidden-panel');
        registerPanel.classList.add('hidden-panel');
        loginBtn.classList.add('active-tab');
        registerBtn.classList.remove('active-tab');
    }

    function showRegister() {
        registerPanel.classList.remove('hidden-panel');
        loginPanel.classList.add('hidden-panel');
        registerBtn.classList.add('active-tab');
        loginBtn.classList.remove('active-tab');
    }

    loginBtn.addEventListener('click', (e) => { e.preventDefault(); showLogin(); });
    registerBtn.addEventListener('click', (e) => { e.preventDefault(); showRegister(); });
</script>

<script src="js/login.js"></script>
</body>
</html>
