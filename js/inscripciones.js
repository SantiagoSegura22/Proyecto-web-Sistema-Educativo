const BASE = 'index.php?accion=';
let paginaActual = 1;
const FILAS_POR_PAGINA = 10;
let inscripcionesCargadas = [];

function inicializar() {
    cargarEstadisticas();
    cargarEventosFiltro();
    cargarInscripciones();

    document.getElementById('filtro-evento').addEventListener('change', () => {
        paginaActual = 1;
        cargarInscripciones();
    });

    document.getElementById('filtro-estado').addEventListener('change', () => {
        paginaActual = 1;
        cargarInscripciones();
    });

    document.getElementById('buscador').addEventListener('input', () => {
        paginaActual = 1;
        cargarInscripciones();
    });

    document.getElementById('btn-nueva-inscripcion').addEventListener('click', abrirModalCrear);
    document.getElementById('btn-cerrar-modal').addEventListener('click', cerrarModal);
    document.getElementById('btn-cancelar-modal').addEventListener('click', cerrarModal);
    document.getElementById('btn-guardar-inscripcion').addEventListener('click', guardarInscripcion);

    document.getElementById('modal-inscripcion').addEventListener('click', function (event) {
        if (event.target === this) {
            cerrarModal();
        }
    });
}

document.addEventListener('DOMContentLoaded', inicializar);

async function cargarEstadisticas() {
    try {
        const respuesta = await fetch(BASE + 'inscripciones_stats');
        const data = await respuesta.json();

        if (data.success) {
            const stats = data.data || {};
            document.getElementById('stat-total').textContent = stats.total_inscripciones ?? 0;
            document.getElementById('stat-eventos').textContent = stats.eventos_con_inscripciones ?? 0;
            document.getElementById('stat-activas').textContent = stats.inscripciones_activas ?? 0;
            document.getElementById('stat-canceladas').textContent = stats.inscripciones_canceladas ?? 0;
        }
    } catch (error) {
        console.error('Error al cargar estadísticas:', error);
    }
}

async function cargarEventosFiltro() {
    try {
        const respuesta = await fetch(BASE + 'inscripciones_eventos');
        const data = await respuesta.json();

        if (data.success) {
            const select = document.getElementById('filtro-evento');
            const formSelect = document.getElementById('evento_id');
            const opciones = data.data || [];

            select.innerHTML = '<option value="">Todos los eventos</option>';
            formSelect.innerHTML = '';

            opciones.forEach((evento) => {
                const option = document.createElement('option');
                option.value = evento.id;
                option.textContent = evento.nombre;
                select.appendChild(option.cloneNode(true));
                formSelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error al cargar eventos:', error);
    }
}

async function cargarInscripciones() {
    const eventoId = document.getElementById('filtro-evento').value;
    const estado = document.getElementById('filtro-estado').value;
    const busqueda = document.getElementById('buscador').value.trim();

    let url = BASE + 'inscripciones_listar';
    const params = [];

    if (eventoId) params.push('evento_id=' + encodeURIComponent(eventoId));
    if (estado) params.push('estado=' + encodeURIComponent(estado));
    if (busqueda) params.push('busqueda=' + encodeURIComponent(busqueda));

    if (params.length > 0) {
        url += '&' + params.join('&');
    }

    try {
        const respuesta = await fetch(url);
        const data = await respuesta.json();

        if (data.success) {
            inscripcionesCargadas = Array.isArray(data.data) ? data.data : [];
            renderizarTabla();
        } else {
            mostrarMensajeTabla(data.error || 'No se pudieron cargar las inscripciones.');
        }
    } catch (error) {
        console.error('Error al cargar inscripciones:', error);
        mostrarMensajeTabla('No se pudo conectar con el servidor.');
    }
}

function renderizarTabla() {
    const busqueda = document.getElementById('buscador').value.toLowerCase();
    const filtradas = inscripcionesCargadas.filter((item) => {
        const texto = [item.evento_nombre, item.nombre, item.apellido, item.correo, item.carrera].join(' ').toLowerCase();
        return texto.includes(busqueda);
    });

    const totalPaginas = Math.max(1, Math.ceil(filtradas.length / FILAS_POR_PAGINA));
    if (paginaActual > totalPaginas) {
        paginaActual = totalPaginas;
    }

    const inicio = (paginaActual - 1) * FILAS_POR_PAGINA;
    const fin = inicio + FILAS_POR_PAGINA;
    const pagina = filtradas.slice(inicio, fin);

    const tbody = document.getElementById('tabla-body');
    if (pagina.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="table-empty-state">No hay inscripciones que coincidan con los filtros.</td></tr>';
        document.getElementById('paginacion').innerHTML = '';
        return;
    }

    tbody.innerHTML = pagina.map((item) => {
        const nombreCompleto = `${item.nombre} ${item.apellido}`;
        const fecha = formatearFecha(item.fecha_inscripcion);
        const badgeClass = item.estado === 'Cancelado' ? 'badge-ausente' : 'badge-presente';

        return `
            <tr>
                <td>${escHtml(item.evento_nombre || 'Sin evento')}</td>
                <td>
                    <strong>${escHtml(nombreCompleto)}</strong>
                </td>
                <td>${escHtml(item.correo || '-')}</td>
                <td>${escHtml(item.carrera || '-')}</td>
                <td>${escHtml(fecha)}</td>
                <td><span class="asistencia-badge ${badgeClass}">${escHtml(item.estado || 'Activo')}</span></td>
                <td>
                    <div class="table-actions">
                        <button class="action-icon view" title="Editar" data-id="${item.id}" onclick="abrirModalEditar(${item.id})">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button class="action-icon delete" title="Eliminar" data-id="${item.id}" onclick="eliminarInscripcion(${item.id})">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
    }).join('');

    renderizarPaginacion(totalPaginas);
}

function renderizarPaginacion(totalPaginas) {
    const contenedor = document.getElementById('paginacion');
    let html = `<button class="page-btn" onclick="cambiarPagina(${paginaActual - 1})" ${paginaActual === 1 ? 'disabled' : ''}>
                    <i class="fa-solid fa-chevron-left"></i>
                </button>`;

    for (let i = 1; i <= totalPaginas; i++) {
        html += `<button class="page-btn ${i === paginaActual ? 'active' : ''}" onclick="cambiarPagina(${i})">${i}</button>`;
    }

    html += `<button class="page-btn" onclick="cambiarPagina(${paginaActual + 1})" ${paginaActual === totalPaginas ? 'disabled' : ''}>
                <i class="fa-solid fa-chevron-right"></i>
            </button>`;

    contenedor.innerHTML = html;
}

function cambiarPagina(numero) {
    const totalPaginas = Math.max(1, Math.ceil(inscripcionesCargadas.length / FILAS_POR_PAGINA));
    if (numero < 1 || numero > totalPaginas) return;
    paginaActual = numero;
    renderizarTabla();
}

function abrirModalCrear() {
    document.getElementById('modal-title').textContent = 'Nueva inscripción';
    document.getElementById('inscripcion-id').value = '';
    document.getElementById('evento_id').value = '';
    document.getElementById('nombre').value = '';
    document.getElementById('apellido').value = '';
    document.getElementById('correo').value = '';
    document.getElementById('carrera').value = '';
    document.getElementById('fecha_inscripcion').value = '';
    document.getElementById('hora_evento').value = '';
    document.getElementById('estado').value = 'Activo';
    abrirModal();
}

async function abrirModalEditar(id) {
    try {
        const respuesta = await fetch(BASE + 'inscripciones_uno&id=' + id);
        const data = await respuesta.json();

        if (data.success) {
            const item = data.data;
            document.getElementById('modal-title').textContent = 'Editar inscripción';
            document.getElementById('inscripcion-id').value = item.id;
            document.getElementById('evento_id').value = item.evento_id || '';
            document.getElementById('nombre').value = item.nombre || '';
            document.getElementById('apellido').value = item.apellido || '';
            document.getElementById('correo').value = item.correo || '';
            document.getElementById('carrera').value = item.carrera || '';
            document.getElementById('fecha_inscripcion').value = item.fecha_inscripcion || '';
            document.getElementById('hora_evento').value = item.hora_evento || '';
            document.getElementById('estado').value = item.estado || 'Activo';
            abrirModal();
        } else {
            mostrarToast(data.error || 'No se pudo cargar la inscripción.', 'error');
        }
    } catch (error) {
        console.error('Error al cargar inscripción:', error);
        mostrarToast('No se pudo cargar la inscripción.', 'error');
    }
}

async function guardarInscripcion() {
    const id = document.getElementById('inscripcion-id').value;
    const payload = {
        id: id || null,
        evento_id: document.getElementById('evento_id').value,
        nombre: document.getElementById('nombre').value.trim(),
        apellido: document.getElementById('apellido').value.trim(),
        correo: document.getElementById('correo').value.trim(),
        carrera: document.getElementById('carrera').value.trim(),
        fecha_inscripcion: document.getElementById('fecha_inscripcion').value,
        hora_evento: document.getElementById('hora_evento').value,
        estado: document.getElementById('estado').value
    };

    const accion = id ? 'inscripciones_editar' : 'inscripciones_crear';

    try {
        const respuesta = await fetch(BASE + accion, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await respuesta.json();
        if (data.success) {
            cerrarModal();
            mostrarToast(data.message || 'Operación realizada correctamente.', 'exito');
            cargarEstadisticas();
            cargarInscripciones();
        } else {
            mostrarToast(data.error || 'No se pudo guardar la inscripción.', 'error');
        }
    } catch (error) {
        console.error('Error al guardar inscripción:', error);
        mostrarToast('No se pudo conectar con el servidor.', 'error');
    }
}

async function eliminarInscripcion(id) {
    if (!window.confirm('¿Deseas eliminar esta inscripción?')) {
        return;
    }

    try {
        const respuesta = await fetch(BASE + 'inscripciones_eliminar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });

        const data = await respuesta.json();
        if (data.success) {
            mostrarToast(data.message || 'Inscripción eliminada.', 'exito');
            cargarEstadisticas();
            cargarInscripciones();
        } else {
            mostrarToast(data.error || 'No se pudo eliminar la inscripción.', 'error');
        }
    } catch (error) {
        console.error('Error al eliminar inscripción:', error);
        mostrarToast('No se pudo conectar con el servidor.', 'error');
    }
}

function abrirModal() {
    document.getElementById('modal-inscripcion').classList.add('open');
}

function cerrarModal() {
    document.getElementById('modal-inscripcion').classList.remove('open');
}

function formatearFecha(fecha) {
    if (!fecha) return '--';
    const [anio, mes, dia] = fecha.split('-');
    return `${dia}/${mes}/${anio}`;
}

function escHtml(valor) {
    if (!valor) return '';
    return String(valor)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function mostrarMensajeTabla(mensaje) {
    document.getElementById('tabla-body').innerHTML = `<tr><td colspan="7" class="table-empty-state">${escHtml(mensaje)}</td></tr>`;
}

function mostrarToast(mensaje, tipo = 'exito') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${tipo}`;
    toast.textContent = mensaje;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('visible');
    }, 10);

    setTimeout(() => {
        toast.classList.remove('visible');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
