document.addEventListener('DOMContentLoaded', () => {
    const newsGrid = document.getElementById('newsGrid');
    const feedback = document.getElementById('noticiasFeedback');
    const btnRecargar = document.getElementById('btnRecargarNoticias');

    const mostrarFeedback = (mensaje, tipo = 'info') => {
        if (!feedback) return;
        feedback.textContent = mensaje;
        feedback.style.display = 'block';
        feedback.style.color = tipo === 'error' ? '#dc2626' : '#0f766e';
        setTimeout(() => {
            feedback.style.display = 'none';
        }, 3000);
    };

    const escapeHTML = (value) => {
        if (!value) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    };

    const formatearFecha = (fecha) => {
        if (!fecha) return '';
        const [anio, mes, dia] = fecha.split('-');
        return `${dia}/${mes}/${anio}`;
    };

    const renderizarNoticias = (eventos) => {
        if (!newsGrid) return;

        if (!eventos || eventos.length === 0) {
            newsGrid.innerHTML = '<p style="grid-column: 1 / -1; text-align: center; color: #6b7280;">No hay eventos registrados por el momento.</p>';
            return;
        }

        newsGrid.innerHTML = eventos.map((evento) => {
            const categoria = evento.categoria || 'General';
            const fecha = formatearFecha(evento.fecha);
            const descripcion = evento.descripcion ? evento.descripcion : 'Más información próximamente.';
            const estado = evento.estado || 'Activo';

            return `
                <article class="news-card card-hover">
                    <img src="https://placehold.co/800x200?text=Evento+Universitario&font=roboto"
     alt="Evento universitario">
                    <div class="news-content">
                        <h3 class="news-title">${escapeHTML(evento.nombre)}</h3>
                        <p class="news-description">${escapeHTML(descripcion)}</p>
                        <p style="margin-bottom: 8px; color: #1FB9DE; font-weight: 600;">${escapeHTML(categoria)} · ${escapeHTML(estado)}</p>
                        <p style="margin-bottom: 12px; color: #6b7280; font-size: 0.9rem;"><i class="fa-solid fa-calendar-days"></i> ${escapeHTML(fecha)} · ${escapeHTML(evento.lugar || 'Lugar por definir')}</p>
                    </div>
                </article>
            `;
        }).join('');
    };

    const cargarNoticias = async () => {
        try {
            const response = await fetch(`index.php?accion=listarEventos&_t=${Date.now()}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                mostrarFeedback(data.error || 'No se pudieron cargar los eventos.', 'error');
                return;
            }

            renderizarNoticias(data.data || []);
        } catch (error) {
            console.error('Error al cargar eventos:', error);
            mostrarFeedback('Error de red al cargar eventos.', 'error');
        }
    };

    if (btnRecargar) {
        btnRecargar.addEventListener('click', () => cargarNoticias());
    }

    cargarNoticias();

    setInterval(() => {
        cargarNoticias();
    }, 10000);
});
