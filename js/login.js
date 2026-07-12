// Archivo: js/login.js
// Maneja el envío por fetch() en formato JSON para login y registro
// Usa Fetch API moderna y actualiza el DOM sin recargar la página

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const loginFeedback = document.getElementById('loginFeedback');
    const registerFeedback = document.getElementById('registerFeedback');

    // Manejo de login vía AJAX (JSON)
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (loginFeedback) loginFeedback.textContent = '';

            const correo = loginForm.querySelector('input[name="correo"]').value.trim();
            const password = loginForm.querySelector('input[name="password"]').value;

            // Enviar JSON al controlador
            try {
                const resp = await fetch('index.php?accion=autenticar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ correo, password })
                });

                const data = await resp.json();

                if (!resp.ok) {
                    // Mostrar error en la UI sin recargar
                    if (loginFeedback) {
                        loginFeedback.style.color = '#c0392b';
                        loginFeedback.textContent = data.error || 'Error en la autenticación.';
                    }
                    return;
                }

                if (data.success) {
                    // Autenticación correcta. Redirigir al dashboard.
                    // Nota: la operación de login se realizó sin recargar; la navegación posterior
                    // es una navegación intencional hacia el dashboard.
                    window.location.href = 'index.php?accion=dashboard';
                } else {
                    if (loginFeedback) {
                        loginFeedback.style.color = '#c0392b';
                        loginFeedback.textContent = data.error || 'Credenciales inválidas.';
                    }
                }
            } catch (err) {
                console.error('Error de red al autenticar:', err);
                if (loginFeedback) {
                    loginFeedback.style.color = '#c0392b';
                    loginFeedback.textContent = 'Error de red. Intenta nuevamente.';
                }
            }
        });
    }

    // Manejo de registro vía AJAX (JSON)
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (registerFeedback) registerFeedback.textContent = '';

            const nombre = registerForm.querySelector('input[name="nombre"]').value.trim();
            const apellido = registerForm.querySelector('input[name="apellido"]').value.trim();
            const correo = registerForm.querySelector('input[name="correo"]').value.trim();
            const password = registerForm.querySelector('input[name="password"]').value;
            const rol = registerForm.querySelector('select[name="rol"]').value;

            try {
                const resp = await fetch('index.php?accion=registrar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ nombre, apellido, correo, password, rol })
                });

                const data = await resp.json();

                if (!resp.ok) {
                    if (registerFeedback) {
                        registerFeedback.style.color = '#c0392b';
                        registerFeedback.textContent = data.error || 'Error al crear la cuenta.';
                    }
                    return;
                }

                if (data.success) {
                    if (registerFeedback) {
                        registerFeedback.style.color = '#27ae60';
                        registerFeedback.textContent = data.message || 'Cuenta creada correctamente.';
                    }
                    // Mostrar pestaña de login para que el usuario inicie sesión
                    const tabLoginBtn = document.getElementById('tabLoginBtn');
                    if (tabLoginBtn) tabLoginBtn.click();
                } else {
                    if (registerFeedback) {
                        registerFeedback.style.color = '#c0392b';
                        registerFeedback.textContent = data.error || 'No se pudo crear la cuenta.';
                    }
                }
            } catch (err) {
                console.error('Error de red al registrar:', err);
                if (registerFeedback) {
                    registerFeedback.style.color = '#c0392b';
                    registerFeedback.textContent = 'Error de red. Intenta nuevamente.';
                }
            }
        });
    }
});
