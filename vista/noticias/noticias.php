<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Noticias - Sistema Universitario</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body class="page-bg">

<?php
    $nombreCompleto = htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']);
    $iniciales = htmlspecialchars(strtoupper(mb_substr($usuario['nombre'], 0, 1) . mb_substr($usuario['apellido'], 0, 1)));
    $rol = htmlspecialchars($usuario['rol']);
?>

    <?php $accionActiva = 'noticias'; ?>
    <?php include __DIR__ . '/../parciales/sidebar.php'; ?>

<main class="main-content">
    <header class="page-header">
        <h1 class="page-title">Noticias</h1>
        <p class="page-subtitle">Eventos registrados y anuncios universitarios</p>
    </header>

    <section class="content-area">
        <div class="section-header">
            <h2 class="section-title">Últimos eventos</h2>
            <button class="btn-primary" id="btnRecargarNoticias">
                <i class="fa-solid fa-rotate-right"></i>
                Actualizar
            </button>
        </div>

        <div id="noticiasFeedback" style="margin-bottom: 16px; font-weight: 600; display: none;"></div>

        <div class="news-grid" id="newsGrid">
            <p style="grid-column: 1 / -1; text-align: center; color: #6b7280;">Cargando eventos...</p>
        </div>
    </section>
</main>

<script src="js/noticias.js"></script>
</body>
</html>
