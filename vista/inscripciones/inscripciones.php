<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscripciones - Sistema de Eventos Universitarios</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

    <?php
    $nombreCompleto = htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']);
    $iniciales = htmlspecialchars(strtoupper(mb_substr($usuario['nombre'], 0, 1) . mb_substr($usuario['apellido'], 0, 1)));
    $rol = htmlspecialchars($usuario['rol']);
    ?>

    <?php $accionActiva = 'inscripciones'; ?>
    <?php include __DIR__ . '/../parciales/sidebar.php'; ?>

    <div class="main-content">
        <header class="main-header">
            <div class="search-bar">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="buscador" placeholder="Buscar participantes...">
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
            <div class="section-header">
                <div>
                    <h2>Administrador de inscripciones</h2>
                    <p>Registra y administra los participantes inscritos en cada evento.</p>
                </div>
                <button class="btn-primary" id="btn-nueva-inscripcion">
                    <i class="fa-solid fa-plus"></i>
                    Nueva inscripción
                </button>
            </div>

            <section class="stats-asistencia">
                <div class="stat-asistencia-card presente">
                    <p class="stat-asistencia-number" id="stat-total">--</p>
                    <p class="stat-asistencia-label">Total inscripciones</p>
                </div>

                <div class="stat-asistencia-card presente">
                    <p class="stat-asistencia-number" id="stat-eventos">--</p>
                    <p class="stat-asistencia-label">Eventos con inscripciones</p>
                </div>

                <div class="stat-asistencia-card presente">
                    <p class="stat-asistencia-number" id="stat-activas">--</p>
                    <p class="stat-asistencia-label">Inscripciones activas</p>
                </div>

                <div class="stat-asistencia-card ausente">
                    <p class="stat-asistencia-number" id="stat-canceladas">--</p>
                    <p class="stat-asistencia-label">Inscripciones canceladas</p>
                </div>
            </section>

            <div class="form-card">
                <h3>Filtros</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="filtro-evento">Evento</label>
                        <select class="form-select" id="filtro-evento">
                            <option value="">Todos los eventos</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="filtro-estado">Estado</label>
                        <select class="form-select" id="filtro-estado">
                            <option value="">Todos</option>
                            <option value="Activo">Activo</option>
                            <option value="Cancelado">Cancelado</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="asistencia-table-container">
                <table class="asistencia-table">
                    <thead>
                        <tr>
                            <th>Evento</th>
                            <th>Participante</th>
                            <th>Correo</th>
                            <th>Carrera</th>
                            <th>Fecha de inscripción</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-body">
                        <tr>
                            <td colspan="7" class="table-empty-state">Cargando inscripciones...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="pagination" id="paginacion"></div>
        </div>
    </div>

    <div class="modal-overlay" id="modal-inscripcion">
        <div class="modal-container">
            <div class="modal-header">
                <h3 id="modal-title">Nueva inscripción</h3>
                <button class="modal-close" id="btn-cerrar-modal" type="button">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="inscripcion-id">

                <div class="asistencia-form-grid">
                    <div class="asistencia-form-group">
                        <label for="evento_id">Evento</label>
                        <select id="evento_id" class="form-select" required></select>
                    </div>

                    <div class="asistencia-form-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" class="form-input" required>
                    </div>

                    <div class="asistencia-form-group">
                        <label for="apellido">Apellido</label>
                        <input type="text" id="apellido" class="form-input" required>
                    </div>

                    <div class="asistencia-form-group">
                        <label for="correo">Correo</label>
                        <input type="email" id="correo" class="form-input" required>
                    </div>

                    <div class="asistencia-form-group">
                        <label for="carrera">Carrera</label>
                        <input type="text" id="carrera" class="form-input">
                    </div>

                    <div class="asistencia-form-group">
                        <label for="fecha_inscripcion">Fecha de inscripción</label>
                        <input type="date" id="fecha_inscripcion" class="form-input" required>
                    </div>

                    <div class="asistencia-form-group">
                        <label for="hora_evento">Hora de inicio del evento</label>
                        <input type="time" id="hora_evento" class="form-input">
                    </div>

                    <div class="asistencia-form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" class="form-select">
                            <option value="Activo">Activo</option>
                            <option value="Cancelado">Cancelado</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-actions">
                <button class="btn-secondary-modal" id="btn-cancelar-modal" type="button">Cancelar</button>
                <button class="btn-primary" id="btn-guardar-inscripcion" type="button">Guardar</button>
            </div>
        </div>
    </div>

    <script src="js/inscripciones.js"></script>
</body>
</html>
