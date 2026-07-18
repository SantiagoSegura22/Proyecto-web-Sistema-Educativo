
const BASE = 'index.php?accion=';

// Página actual de la tabla
let paginaActual = 1;
const FILAS_POR_PAGINA = 10;

// Todos los registros cargados (guardamos aquí para paginar sin pedir de nuevo)
let registrosCargados = [];


// Al cargar la página hacemos todo lo inicial

document.addEventListener('DOMContentLoaded', () => {

    cargarEstadisticas();
    cargarEventosFiltro();
    cargarRegistros();

    // Los filtros se aplican automáticamente al cambiar
    document.getElementById('filtro-evento').addEventListener('change', () => {
        paginaActual = 1;
        cargarRegistros();
    });

    document.getElementById('filtro-estado').addEventListener('change', () => {
        paginaActual = 1;
        cargarRegistros();
    });

    document.getElementById('filtro-fecha').addEventListener('change', () => {
        paginaActual = 1;
        cargarRegistros();
    });

    // El buscador filtra en tiempo real sobre los datos ya cargados
    document.getElementById('buscador').addEventListener('input', () => {
        paginaActual = 1;
        renderizarTabla();
    });

    // Cerrar modales al hacer click fuera
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function (e) {
            if (e.target === this) cerrarModal(this.id);
        });
    });
});


// 1. Cargar estadísticas (números arriba)

async function cargarEstadisticas() {
    try {
        const res  = await fetch(BASE + 'asistencia_stats');
        const data = await res.json();

        if (data.ok) {
            document.getElementById('stat-total').textContent     = data.data.total     ?? 0;
            document.getElementById('stat-presentes').textContent = data.data.presentes ?? 0;
            document.getElementById('stat-ausentes').textContent  = data.data.ausentes  ?? 0;
            document.getElementById('stat-tardanzas').textContent = data.data.tardanzas ?? 0;
        }
    } catch (err) {
        console.error('Error al cargar estadísticas:', err);
    }
}


// 2. Cargar los eventos en el <select> de filtros

async function cargarEventosFiltro() {
    try {
        const res  = await fetch(BASE + 'asistencia_eventos');
        const data = await res.json();

        if (data.ok) {
            const select = document.getElementById('filtro-evento');
            data.eventos.forEach(ev => {
                const opt   = document.createElement('option');
                opt.value   = ev;
                opt.textContent = ev;
                select.appendChild(opt);
            });
        }
    } catch (err) {
        console.error('Error al cargar eventos:', err);
    }
}


// 3. Cargar registros con los filtros actuales

async function cargarRegistros() {

    const evento = document.getElementById('filtro-evento').value;
    const estado = document.getElementById('filtro-estado').value;
    const fecha  = document.getElementById('filtro-fecha').value;

    // Construimos la URL con los filtros como parámetros GET
    let url = BASE + 'asistencia_registros';
    if (evento) url += '&evento=' + encodeURIComponent(evento);
    if (estado) url += '&estado=' + encodeURIComponent(estado);
    if (fecha)  url += '&fecha='  + encodeURIComponent(fecha);

    try {
        const res  = await fetch(url);
        const data = await res.json();

        if (data.ok) {
            registrosCargados = data.registros;
            renderizarTabla();
        } else {
            mostrarError('No se pudieron cargar los registros.');
        }
    } catch (err) {
        console.error('Error al cargar registros:', err);
        mostrarError('Error de conexión con el servidor.');
    }
}


// 4. Renderizar la tabla (con paginación y buscador)

function renderizarTabla() {

    const busqueda = document.getElementById('buscador').value.toLowerCase();

    // Filtrar en cliente por lo que escribe el usuario
    const filtrados = registrosCargados.filter(r => {
        const texto = (r.nombre + ' ' + r.apellido + ' ' + r.correo + ' ' + r.evento).toLowerCase();
        return texto.includes(busqueda);
    });

    // Calcular páginas
    const totalPaginas = Math.ceil(filtrados.length / FILAS_POR_PAGINA) || 1;
    if (paginaActual > totalPaginas) paginaActual = totalPaginas;

    const inicio = (paginaActual - 1) * FILAS_POR_PAGINA;
    const fin    = inicio + FILAS_POR_PAGINA;
    const pagina = filtrados.slice(inicio, fin);

    const tbody = document.getElementById('tabla-body');

    if (pagina.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" style="text-align:center; padding:2rem; color:#888;">
                    No hay registros que coincidan con los filtros.
                </td>
            </tr>`;
        document.getElementById('paginacion').innerHTML = '';
        return;
    }

    // Construir filas
    tbody.innerHTML = pagina.map((r, idx) => {

        const num    = String(inicio + idx + 1).padStart(3, '0');
        const nombre = escHtml(r.nombre + ' ' + r.apellido);
        const correo = escHtml(r.correo);
        const evento = escHtml(r.evento);
        const fecha  = formatearFecha(r.fecha);
        const hora   = r.hora.substring(0, 5); // HH:MM
        const obs    = escHtml(r.observaciones || 'Sin observaciones');

        // Badge de estado
        const claseBadge = {
            'Presente': 'badge-presente',
            'Ausente':  'badge-ausente',
            'Tardanza': 'badge-tardanza'
        }[r.estado] || '';

        // Select de estado inline (editable directo en la tabla)
        const selectEstado = `
            <select class="estado-inline ${claseBadge}"
                    data-id="${r.id}"
                    onchange="cambiarEstadoInline(this)">
                <option value="Presente" ${r.estado === 'Presente' ? 'selected' : ''}>Presente</option>
                <option value="Ausente"  ${r.estado === 'Ausente'  ? 'selected' : ''}>Ausente</option>
                <option value="Tardanza" ${r.estado === 'Tardanza' ? 'selected' : ''}>Tardanza</option>
            </select>`;

        return `
            <tr>
                <td>${num}</td>
                <td>
                    <strong>${nombre}</strong><br>
                    <small>${correo}</small>
                </td>
                <td>${evento}</td>
                <td>${fecha}</td>
                <td>${hora}</td>
                <td>${selectEstado}</td>
                <td>${obs}</td>
                <td>
                    <button class="action-icon view" title="Ver detalle"
                        onclick="abrirVer(${r.id})">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                    <button class="action-icon" title="Editar"
                        onclick="abrirEditar(${r.id})">
                        <i class="fa-solid fa-pen"></i>
                    </button>
                </td>
            </tr>`;
    }).join('');

    // Paginación
    renderizarPaginacion(totalPaginas);
}


// 5. Renderizar botones de paginación

function renderizarPaginacion(totalPaginas) {

    const div = document.getElementById('paginacion');

    let html = `<button class="page-btn" onclick="cambiarPagina(${paginaActual - 1})"
                    ${paginaActual === 1 ? 'disabled' : ''}>
                    <i class="fa-solid fa-chevron-left"></i>
                </button>`;

    for (let i = 1; i <= totalPaginas; i++) {
        html += `<button class="page-btn ${i === paginaActual ? 'active' : ''}"
                    onclick="cambiarPagina(${i})">${i}</button>`;
    }

    html += `<button class="page-btn" onclick="cambiarPagina(${paginaActual + 1})"
                ${paginaActual === totalPaginas ? 'disabled' : ''}>
                <i class="fa-solid fa-chevron-right"></i>
            </button>`;

    div.innerHTML = html;
}

function cambiarPagina(n) {
    const totalPaginas = Math.ceil(registrosCargados.length / FILAS_POR_PAGINA) || 1;
    if (n < 1 || n > totalPaginas) return;
    paginaActual = n;
    renderizarTabla();
}


// 6. Cambiar estado inline (select en la tabla)

async function cambiarEstadoInline(selectEl) {

    const id     = selectEl.dataset.id;
    const estado = selectEl.value;

    // Buscamos las observaciones actuales en el array
    const reg = registrosCargados.find(r => String(r.id) === String(id));
    const observaciones = reg ? (reg.observaciones || '') : '';

    try {
        const res  = await fetch(BASE + 'asistencia_actualizar', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ id, estado, observaciones })
        });

        const data = await res.json();

        if (data.ok) {
            // Actualizar el array local para no recargar todo
            if (reg) reg.estado = estado;

            // Actualizar la clase del badge del select
            selectEl.className = 'estado-inline ' + ({
                'Presente': 'badge-presente',
                'Ausente':  'badge-ausente',
                'Tardanza': 'badge-tardanza'
            }[estado] || '');

            // Refrescar estadísticas
            cargarEstadisticas();

            mostrarToast('Estado actualizado correctamente', 'exito');
        } else {
            mostrarToast('Error al actualizar: ' + data.mensaje, 'error');
        }
    } catch (err) {
        console.error('Error al cambiar estado:', err);
        mostrarToast('Error de conexión', 'error');
    }
}


// 7. Abrir modal VER

async function abrirVer(id) {

    try {
        const res  = await fetch(BASE + 'asistencia_uno&id=' + id);
        const data = await res.json();

        if (data.ok) {
            const r = data.data;
            document.getElementById('ver-nombre').textContent       = r.nombre + ' ' + r.apellido;
            document.getElementById('ver-correo').textContent       = r.correo;
            document.getElementById('ver-evento').textContent       = r.evento;
            document.getElementById('ver-fecha').textContent        = formatearFecha(r.fecha);
            document.getElementById('ver-hora').textContent         = r.hora.substring(0, 5);
            document.getElementById('ver-estado').textContent       = r.estado;
            document.getElementById('ver-observaciones').textContent = r.observaciones || 'Sin observaciones';

            abrirModal('modal-ver');
        } else {
            mostrarToast('No se pudo cargar el registro', 'error');
        }
    } catch (err) {
        console.error('Error al abrir ver:', err);
    }
}


// 8. Abrir modal EDITAR

async function abrirEditar(id) {

    try {
        const res  = await fetch(BASE + 'asistencia_uno&id=' + id);
        const data = await res.json();

        if (data.ok) {
            const r = data.data;
            document.getElementById('editar-id').value            = r.id;
            document.getElementById('editar-nombre').value        = r.nombre + ' ' + r.apellido;
            document.getElementById('editar-correo').value        = r.correo;
            document.getElementById('editar-evento').value        = r.evento;
            document.getElementById('editar-estado').value        = r.estado;
            document.getElementById('editar-observaciones').value = r.observaciones || '';

            abrirModal('modal-editar');
        } else {
            mostrarToast('No se pudo cargar el registro', 'error');
        }
    } catch (err) {
        console.error('Error al abrir editar:', err);
    }
}


// 9. Guardar cambios del modal EDITAR

async function guardarEdicion() {

    const id            = document.getElementById('editar-id').value;
    const estado        = document.getElementById('editar-estado').value;
    const observaciones = document.getElementById('editar-observaciones').value;

    try {
        const res  = await fetch(BASE + 'asistencia_actualizar', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ id, estado, observaciones })
        });

        const data = await res.json();

        if (data.ok) {
            cerrarModal('modal-editar');
            mostrarToast('Registro actualizado correctamente', 'exito');
            // Recargar para reflejar los cambios
            cargarRegistros();
            cargarEstadisticas();
        } else {
            mostrarToast('Error: ' + data.mensaje, 'error');
        }
    } catch (err) {
        console.error('Error al guardar edición:', err);
        mostrarToast('Error de conexión', 'error');
    }
}


// Helpers de modales

function abrirModal(id) {
    document.getElementById(id).classList.add('open');
}

function cerrarModal(id) {
    document.getElementById(id).classList.remove('open');
}

// Helper: formatear fecha de YYYY-MM-DD a DD/MM/YYYY

function formatearFecha(fecha) {
    if (!fecha) return '--';
    const [y, m, d] = fecha.split('-');
    return `${d}/${m}/${y}`;
}


// Helper: escapar HTML para evitar XSS

function escHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}


// Helper: mostrar mensaje de error en la tabla

function mostrarError(msg) {
    document.getElementById('tabla-body').innerHTML = `
        <tr>
            <td colspan="8" style="text-align:center; padding:2rem; color:#e53e3e;">
                <i class="fa-solid fa-triangle-exclamation"></i> ${msg}
            </td>
        </tr>`;
}


// Helper: toast de notificación temporal

function mostrarToast(mensaje, tipo = 'exito') {

    // Crear elemento
    const toast = document.createElement('div');
    toast.className = 'toast toast-' + tipo;
    toast.textContent = mensaje;

    // Estilos inline mínimos (puedes moverlos a estilos.css)
    Object.assign(toast.style, {
        position:     'fixed',
        bottom:       '24px',
        right:        '24px',
        background:   tipo === 'exito' ? '#38a169' : '#e53e3e',
        color:        '#fff',
        padding:      '12px 20px',
        borderRadius: '8px',
        fontSize:     '14px',
        zIndex:       '9999',
        boxShadow:    '0 4px 12px rgba(0,0,0,0.15)',
        transition:   'opacity 0.3s'
    });

    document.body.appendChild(toast);

    // Desaparecer tras 3 segundos
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}