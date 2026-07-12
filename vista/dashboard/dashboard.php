<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Sistema de Eventos Universitarios</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

<?php
    $nombreCompleto = htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']);
    $iniciales = htmlspecialchars(strtoupper(mb_substr($usuario['nombre'], 0, 1) . mb_substr($usuario['apellido'], 0, 1)));
    $rol = htmlspecialchars($usuario['rol']);
?>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-logo">
        <img src="img/logo-ug.png" alt="Universidad de Guayaquil">
    </div>

    <div class="sidebar-user">
        <div class="user-avatar"><?php echo $iniciales; ?></div>
        <div class="user-info">
            <p class="user-name"><?php echo $nombreCompleto; ?></p>
            <p class="user-role"><?php echo $rol; ?></p>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="index.php?accion=dashboard" class="nav-link active">
            <i class="fa-solid fa-house"></i>
            <span>Inicio</span>
        </a>
        <a href="eventos.html" class="nav-link">
            <i class="fa-solid fa-calendar"></i>
            <span>Eventos</span>
        </a>
        <a href="inscripciones.html" class="nav-link">
            <i class="fa-solid fa-ticket"></i>
            <span>Mis inscripciones</span>
        </a>
        <a href="asistencia.html" class="nav-link">
            <i class="fa-solid fa-chart-bar"></i>
            <span>Asistencia</span>
        </a>
        <a href="noticias.html" class="nav-link">
            <i class="fa-solid fa-newspaper"></i>
            <span>Noticias</span>
        </a>
        <div class="sidebar-divider"></div>
        <a href="index.php?accion=logout" class="nav-link">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            <span>Salir</span>
        </a>
    </nav>
</div>

<!-- CONTENIDO PRINCIPAL -->
<div class="main-content">
    <header class="main-header">
        <div class="search-bar">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" placeholder="Buscar eventos...">
        </div>
        <div class="user-menu">
            <button class="notif-btn">
                <i class="fa-solid fa-bell"></i>
                <span class="notif-badge">3</span>
            </button>
            <div class="user-info">
                <div class="user-details">
                    <p class="user-name"><?php echo $nombreCompleto; ?></p>
                    <p class="user-role"><?php echo $rol; ?></p>
                </div>
                <div class="user-avatar"><?php echo $iniciales; ?></div>
            </div>
        </div>
    </header>

    <div class="dashboard-container">
        <section class="welcome-card">
            <div class="welcome-text">
                <p class="welcome-badge">Panel principal</p>
                <h1>¡Bienvenido, <?php echo htmlspecialchars($usuario['nombre']); ?>!</h1>
                <p>Revisa tus eventos activos, controla tus inscripciones y mantente al día con las actividades universitarias.</p>
            </div>
            <div class="welcome-actions">
                <a href="eventos.html" class="btn-primary">
                    <i class="fa-solid fa-calendar-plus"></i> Ver eventos
                </a>
                <a href="inscripciones.html" class="btn-outline">
                    <i class="fa-solid fa-ticket"></i> Mis inscripciones
                </a>
            </div>
        </section>
    </div>
</div>

</body>
</html>
