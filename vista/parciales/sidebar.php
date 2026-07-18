<?php
// =============================================
// vista/parciales/sidebar.php
// =============================================
?>
<div class="sidebar">

    <div class="sidebar-logo">
        <img src="img/logo-ug.png" alt="Universidad de Guayaquil">
    </div>

    <div class="sidebar-user">
        <div class="user-avatar"><?php echo $iniciales ?? 'RA'; ?></div>
        <div class="user-info">
            <p class="user-name"><?php echo $nombreCompleto ?? 'Usuario'; ?></p>
            <p class="user-role"><?php echo $rol ?? 'Estudiante'; ?></p>
        </div>
    </div>

    <nav class="sidebar-nav">

        <a href="index.php?accion=dashboard"
            class="nav-link <?php echo ($accionActiva ?? '') === 'dashboard' ? 'active' : ''; ?>">
            <i class="fa-solid fa-house"></i>
            <span>Inicio</span>
        </a>

        <a href="index.php?accion=eventos"
            class="nav-link <?php echo ($accionActiva ?? '') === 'eventos' ? 'active' : ''; ?>">
            <i class="fa-solid fa-calendar"></i>
            <span>Eventos</span>
        </a>

        <a href="index.php?accion=inscripciones"
            class="nav-link <?php echo ($accionActiva ?? '') === 'inscripciones' ? 'active' : ''; ?>">
            <i class="fa-solid fa-ticket"></i>
            <span>Inscripciones</span>
        </a>

        <a href="index.php?accion=asistencia"
            class="nav-link <?php echo ($accionActiva ?? '') === 'asistencia' ? 'active' : ''; ?>">
            <i class="fa-solid fa-chart-bar"></i>
            <span>Asistencia</span>
        </a>


        <div class="sidebar-divider"></div>

        <a href="index.php?accion=logout" class="nav-link">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            <span>Salir</span>
        </a>

    </nav>

</div>