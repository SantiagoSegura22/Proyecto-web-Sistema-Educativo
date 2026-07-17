document.addEventListener('DOMContentLoaded', () => {
    const tablaEventosBody = document.getElementById('tablaEventosBody');
    const formContenedor = document.getElementById('formularioContenedor');
    const eventoForm = document.getElementById('eventoForm');
    const btnNuevoEvento = document.getElementById('btnNuevoEvento');
    const btnCancelarForm = document.getElementById('btnCancelarForm');
    const formTitulo = document.getElementById('formTitulo');
    const eventoFeedback = document.getElementById('eventoFeedback');
    const filterTabs = document.querySelectorAll('.filter-tab');
    
    let todosLosEventos = [];

    // --- CARGAR EVENTOS ---
    const cargarEventos = async () => {
        try {
            const resp = await fetch('index.php?accion=listarEventos');
            const data = await resp.json();
            
            if (data.success) {
                todosLosEventos = data.data;
                renderizarEventos(todosLosEventos);
            } else {
                mostrarFeedback(data.error, 'error');
            }
        } catch (error) {
            console.error('Error al cargar eventos:', error);
            mostrarFeedback('Error de red al cargar los eventos.', 'error');
        }
    };

    //RENDERIZAR TABLA
    const renderizarEventos = (eventos) => {
        tablaEventosBody.innerHTML = '';
        
        if (eventos.length === 0) {
            tablaEventosBody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No hay eventos registrados.</td></tr>';
            return;
        }

        eventos.forEach(evento => {
            // Clases de badge según categoría y estado
            let badgeCatClass = '';
            switch(evento.categoria) {
                case 'Academico': badgeCatClass = 'badge-academico'; break;
                case 'Tecnologia': badgeCatClass = 'badge-tecnologia'; break;
                case 'Deportes': badgeCatClass = 'badge-deportes'; break;
                case 'Cultural': badgeCatClass = 'badge-cultural'; break;
                default: badgeCatClass = 'badge-default';
            }

            let badgeEstClass = '';
            switch(evento.estado) {
                case 'Activo': badgeEstClass = 'badge-activo'; break;
                case 'Finalizado': badgeEstClass = 'badge-finalizado'; break;
                case 'Cancelado': badgeEstClass = 'badge-inactivo'; break; // asumiendo que tienes una clase inactiva
                default: badgeEstClass = 'badge-default';
            }

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${escapeHTML(evento.nombre)}</td>
                <td>${formatearFecha(evento.fecha)}</td>
                <td>${escapeHTML(evento.lugar)}</td>
                <td><span class="badge ${badgeCatClass}">${escapeHTML(evento.categoria)}</span></td>
                <td><span class="badge ${badgeEstClass}">${escapeHTML(evento.estado)}</span></td>
                <td>
                    <button class="action-btn edit-btn" data-id="${evento.id}" onclick="editarEvento(${evento.id})">
                        <i class="fa-solid fa-pen"></i> Editar
                    </button>
                    <button class="action-btn delete-btn" data-id="${evento.id}" onclick="eliminarEvento(${evento.id})">
                        <i class="fa-solid fa-trash"></i> Eliminar
                    </button>
                </td>
            `;
            tablaEventosBody.appendChild(tr);
        });
    };

    // FILTRADO 
    filterTabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.preventDefault();
            
            // Actualizar clase activa
            filterTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            const filtro = tab.getAttribute('data-filter');
            if (filtro === 'Todos') {
                renderizarEventos(todosLosEventos);
            } else {
                const filtrados = todosLosEventos.filter(ev => ev.categoria === filtro);
                renderizarEventos(filtrados);
            }
        });
    });

    // FORMULARIO MOSTRAR/OCULTAR 
    btnNuevoEvento.addEventListener('click', () => {
        eventoForm.reset();
        document.getElementById('eventoId').value = '';
        formTitulo.textContent = 'Registrar Nuevo Evento';
        formContenedor.style.display = 'block';
        window.scrollTo(0, formContenedor.offsetTop);
    });

    btnCancelarForm.addEventListener('click', () => {
        formContenedor.style.display = 'none';
        eventoForm.reset();
    });

    // GUARDAR EVENTO (CREAR / EDITAR) 
    eventoForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(eventoForm);
        const dataObj = Object.fromEntries(formData.entries());
        const esEdicion = !!dataObj.id;
        
        const url = esEdicion ? 'index.php?accion=editarEvento' : 'index.php?accion=crearEvento';

        try {
            const resp = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(dataObj)
            });

            const data = await resp.json();

            if (data.success) {
                mostrarFeedback(data.message, 'exito');
                formContenedor.style.display = 'none';
                eventoForm.reset();
                cargarEventos(); // Recargar la tabla
            } else {
                mostrarFeedback(data.error, 'error');
            }
        } catch (error) {
            console.error('Error al guardar evento:', error);
            mostrarFeedback('Error de red al guardar el evento.', 'error');
        }
    });

    // ELIMINAR EVENTO 
    window.eliminarEvento = async (id) => {
        if (!confirm('¿Estás seguro de que deseas eliminar este evento?')) return;

        try {
            const resp = await fetch('index.php?accion=eliminarEvento', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ id })
            });

            const data = await resp.json();

            if (data.success) {
                mostrarFeedback(data.message, 'exito');
                cargarEventos(); // Recargar tabla
            } else {
                mostrarFeedback(data.error, 'error');
            }
        } catch (error) {
            console.error('Error al eliminar evento:', error);
            mostrarFeedback('Error de red al eliminar el evento.', 'error');
        }
    };

    // EDITAR EVENTO 
    window.editarEvento = (id) => {
        const evento = todosLosEventos.find(ev => ev.id == id);
        if (!evento) return;

        document.getElementById('eventoId').value = evento.id;
        document.getElementById('eventoNombre').value = evento.nombre;
        document.getElementById('eventoFecha').value = evento.fecha;
        document.getElementById('eventoLugar').value = evento.lugar;
        document.getElementById('eventoCategoria').value = evento.categoria;
        document.getElementById('eventoEstado').value = evento.estado;
        document.getElementById('eventoDescripcion').value = evento.descripcion;

        formTitulo.textContent = 'Editar Evento';
        formContenedor.style.display = 'block';
        window.scrollTo(0, formContenedor.offsetTop);
    };

    // UTILIDADES 
    const mostrarFeedback = (mensaje, tipo) => {
        eventoFeedback.textContent = mensaje;
        eventoFeedback.style.display = 'block';
        if (tipo === 'exito') {
            eventoFeedback.style.color = '#27ae60'; 
        } else {
            eventoFeedback.style.color = '#c0392b'; 
        }

        setTimeout(() => {
            eventoFeedback.style.display = 'none';
        }, 4000);
    };

    const escapeHTML = (str) => {
        if (!str) return '';
        return str.replace(/[&<>'"]/g, 
            tag => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                "'": '&#39;',
                '"': '&quot;'
            }[tag] || tag)
        );
    };

    const formatearFecha = (fechaString) => {
        if (!fechaString) return '';
        // Asume formato YYYY-MM-DD 
        const partes = fechaString.split('-');
        if (partes.length === 3) {
            return `${partes[2]}/${partes[1]}/${partes[0]}`; // DD/MM/YYYY
        }
        return fechaString;
    };

    cargarEventos();
});
