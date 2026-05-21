fetch('components/sidebar.html')
    .then(response => response.text())
    .then(data => {

        document.getElementById('sidebar-container').innerHTML = data;

        // Detectar página actual
        const currentPage = window.location.pathname
            .split("/")
            .pop()
            .replace(".html", "");

        // Buscar enlace correspondiente
        const activeLink = document.querySelector(
            `[data-page="${currentPage}"]`
        );

        // Activar enlace
        if (activeLink) {
            activeLink.classList.add("active");
        }

    });