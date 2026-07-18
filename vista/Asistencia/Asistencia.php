
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asistencia - Sistema de Eventos Universitarios</title>

 
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">


    <link rel="stylesheet" href="/Proyecto-web-Sistema-Educativo/css/estilos.css">
</head>

<body>
    <?php $accionActiva = 'asistencia'; ?>
    <?php include __DIR__ . '/../parciales/sidebar.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="main-content">

        <header class="main-header">

            <div class="search-bar">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="buscador" placeholder="Buscar asistencia...">
            </div>

            <div class="user-menu">
                <button class="notif-btn">
                    <i class="fa-solid fa-bell"></i>
                    <span class="notif-badge">3</span>
                </button>
                <div class="user-info">
                    <div class="user-details">
                        <p class="user-name">Ronald Andagoya</p>
                        <p class="user-role">Estudiante</p>
                    </div>
                    <div class="user-avatar">RA</div>
                </div>
            </div>

        </header>

        <div class="dashboard-container">

            <!-- TITULO -->
            <div class="section-header">
                <div>
                    <h2>Asistencia</h2>
                    <p>Gestión de registros de asistencia a eventos universitarios</p>
                </div>
            </div>

            <!-- ESTADISTICAS -->
            <section class="stats-asistencia">

                <div class="stat-asistencia-card presente">
                    <p class="stat-asistencia-number" id="stat-total">--</p>
                    <p class="stat-asistencia-label">Total registros</p>
                </div>

                <div class="stat-asistencia-card presente">
                    <p class="stat-asistencia-number" id="stat-presentes">--</p>
                    <p class="stat-asistencia-label">Presentes</p>
                </div>

                <div class="stat-asistencia-card ausente">
                    <p class="stat-asistencia-number" id="stat-ausentes">--</p>
                    <p class="stat-asistencia-label">Ausentes</p>
                </div>

                <div class="stat-asistencia-card tardanza">
                    <p class="stat-asistencia-number" id="stat-tardanzas">--</p>
                    <p class="stat-asistencia-label">Tardanzas</p>
                </div>

            </section>

            <!-- FILTROS (se aplican solos al cambiar) -->
            <div class="form-card">

                <h3>Filtros</h3>

                <div class="form-row">

                    <div class="form-group">
                        <label>Evento</label>
                        <select class="form-select" id="filtro-evento">
                            <option value="">Todos los eventos</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Estado</label>
                        <select class="form-select" id="filtro-estado">
                            <option value="">Todos</option>
                            <option value="Presente">Presente</option>
                            <option value="Ausente">Ausente</option>
                            <option value="Tardanza">Tardanza</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Fecha</label>
                        <input type="date" class="form-input" id="filtro-fecha">
                    </div>

                </div>

            </div>

            <!-- TABLA -->
            <div class="asistencia-table-container">

                <table class="asistencia-table">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Estudiante</th>
                            <th>Evento</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Estado</th>
                            <th>Observaciones</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody id="tabla-body">
                        <tr>
                            <td colspan="8" style="text-align:center; padding:2rem;">
                                Cargando registros...
                            </td>
                        </tr>
                    </tbody>

                </table>

            </div>

            <!-- PAGINACION -->
            <div class="pagination" id="paginacion"></div>

        </div>

    </div>

    <!-- MODAL VER -->
    <div class="modal-overlay" id="modal-ver">
        <div class="modal-container">

            <div class="modal-header">
                <h3>Detalle de asistencia</h3>
                <button class="modal-close" onclick="cerrarModal('modal-ver')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-body">
                <div class="asistencia-form-grid">

                    <div class="asistencia-form-group">
                        <label>Estudiante</label>
                        <p id="ver-nombre" class="modal-valor">--</p>
                    </div>

                    <div class="asistencia-form-group">
                        <label>Correo</label>
                        <p id="ver-correo" class="modal-valor">--</p>
                    </div>

                    <div class="asistencia-form-group">
                        <label>Evento</label>
                        <p id="ver-evento" class="modal-valor">--</p>
                    </div>

                    <div class="asistencia-form-group">
                        <label>Fecha</label>
                        <p id="ver-fecha" class="modal-valor">--</p>
                    </div>

                    <div class="asistencia-form-group">
                        <label>Hora</label>
                        <p id="ver-hora" class="modal-valor">--</p>
                    </div>

                    <div class="asistencia-form-group">
                        <label>Estado</label>
                        <p id="ver-estado" class="modal-valor">--</p>
                    </div>

                    <div class="asistencia-form-group" style="grid-column: 1 / -1;">
                        <label>Observaciones</label>
                        <p id="ver-observaciones" class="modal-valor">--</p>
                    </div>

                </div>
            </div>

            <div class="modal-actions">
                <button class="btn-secondary-modal" onclick="cerrarModal('modal-ver')">
                    Cerrar
                </button>
            </div>

        </div>
    </div>

    <!-- MODAL EDITAR -->
    <div class="modal-overlay" id="modal-editar">
        <div class="modal-container">

            <div class="modal-header">
                <h3>Editar asistencia</h3>
                <button class="modal-close" onclick="cerrarModal('modal-editar')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-body">

                <input type="hidden" id="editar-id">

                <div class="asistencia-form-grid">

                    <div class="asistencia-form-group">
                        <label>Estudiante</label>
                        <input type="text" id="editar-nombre" class="form-input" disabled>
                    </div>

                    <div class="asistencia-form-group">
                        <label>Correo</label>
                        <input type="email" id="editar-correo" class="form-input" disabled>
                    </div>

                    <div class="asistencia-form-group">
                        <label>Evento</label>
                        <input type="text" id="editar-evento" class="form-input" disabled>
                    </div>

                    <div class="asistencia-form-group">
                        <label>Estado</label>
                        <select id="editar-estado" class="form-select">
                            <option value="Presente">Presente</option>
                            <option value="Ausente">Ausente</option>
                            <option value="Tardanza">Tardanza</option>
                        </select>
                    </div>

                    <div class="asistencia-form-group" style="grid-column: 1 / -1;">
                        <label>Observaciones</label>
                        <textarea id="editar-observaciones" class="form-input"
                            rows="3" placeholder="Observaciones..."></textarea>
                    </div>

                </div>
            </div>

            <div class="modal-actions">
                <button class="btn-secondary-modal" onclick="cerrarModal('modal-editar')">
                    Cancelar
                </button>
                <button class="btn-primary" onclick="guardarEdicion()">
                    Guardar cambios
                </button>
            </div>

        </div>
    </div>


  <script src="/Proyecto-web-Sistema-Educativo/js/asistencia.js"></script>

</body>
</html>