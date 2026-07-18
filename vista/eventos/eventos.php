<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventos - Sistema Universitario</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>

<body>

<?php
    $nombreCompleto = htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']);
    $iniciales = htmlspecialchars(strtoupper(mb_substr($usuario['nombre'], 0, 1) . mb_substr($usuario['apellido'], 0, 1)));
    $rol = htmlspecialchars($usuario['rol']);
?>

    <?php $accionActiva = 'eventos'; ?>
    <?php include __DIR__ . '/../parciales/sidebar.php'; ?>

    <!-- MAIN -->
    <main class="main-content">

        <!-- HEADER -->
        <header class="main-header">

            <div class="search-bar">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="inputBuscarEvento" placeholder="Buscar eventos...">
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
                    <div class="user-avatar">
                        <?php echo $iniciales; ?>
                    </div>
                </div>
            </div>

        </header>

        <!-- CONTENT -->
        <section class="dashboard-container">

            <!-- TITULO -->
            <div class="section-header">
                <div>
                    <h2>Eventos</h2>
                    <p class="stat-label">Gestión de eventos universitarios</p>
                </div>
                
                <button class="btn-primary" id="btnNuevoEvento">
                    <i class="fa-solid fa-plus"></i> Registrar Evento
                </button>
            </div>

            <!-- FILTROS -->
            <div class="filter-tabs">
                <a href="#" class="filter-tab active" data-filter="Todos">Todos</a>
                <a href="#" class="filter-tab" data-filter="Academico">Académicos</a>
                <a href="#" class="filter-tab" data-filter="Tecnologia">Tecnología</a>
                <a href="#" class="filter-tab" data-filter="Deportes">Deportivos</a>
                <a href="#" class="filter-tab" data-filter="Cultural">Culturales</a>
            </div>

            <!-- MENSAJES FEEDBACK -->
            <div id="eventoFeedback" style="margin-bottom: 15px; font-weight: bold; display: none;"></div>

            <!-- TABLA -->
            <div class="table-container">
                <table>
                    <thead class="table-header">
                        <tr>
                            <th>Evento</th>
                            <th>Fecha</th>
                            <th>Lugar</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="table-body" id="tablaEventosBody">
                        <!-- Llenado dinámicamente con AJAX -->
                        <tr>
                            <td colspan="6" style="text-align:center;">Cargando eventos...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- FORMULARIO -->
            <div class="form-card" id="formularioContenedor" style="display: none; margin-top: 20px;">
                <h3 id="formTitulo">Registrar Nuevo Evento</h3>
                <form id="eventoForm">
                    <input type="hidden" id="eventoId" name="id">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nombre del Evento</label>
                            <input type="text" id="eventoNombre" name="nombre" class="form-input" placeholder="Nombre del evento" required>
                        </div>
                        <div class="form-group">
                            <label>Fecha</label>
                            <input type="date" id="eventoFecha" name="fecha" class="form-input" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Lugar</label>
                            <input type="text" id="eventoLugar" name="lugar" class="form-input" placeholder="Lugar del evento" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Categoría</label>
                            <select class="form-select" id="eventoCategoria" name="categoria" required>
                                <option value="">Seleccione...</option>
                                <option value="Academico">Académico</option>
                                <option value="Tecnologia">Tecnología</option>
                                <option value="Deportes">Deportes</option>
                                <option value="Cultural">Cultural</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                         <div class="form-group">
                            <label>Estado</label>
                            <select class="form-select" id="eventoEstado" name="estado" required>
                                <option value="Activo">Activo</option>
                                <option value="Finalizado">Finalizado</option>
                                <option value="Cancelado">Cancelado</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea id="eventoDescripcion" name="descripcion" class="form-textarea" placeholder="Descripción del evento"></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> Guardar Evento
                        </button>
                        <button type="button" class="btn-secondary" id="btnCancelarForm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>

        </section>

    </main>
    <script src="js/eventos.js"></script>
</body>
</html>
