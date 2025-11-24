document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const errorMessage = document.getElementById('error-message');

    if (localStorage.getItem('auth_token')) { 
        // ⭐ CORRECCIÓN 1: Ruta simple
        window.location.href = 'bienvenida.html'; 
        return;
    }

    // ⭐⭐ RUTA API: Nombre exacto de tu carpeta en htdocs. ⭐⭐
    const PROJECT_FOLDER = 'Creacion-de-un-Servicio-de-Autenticacion-con-ApiRest-JWT';

    // Usamos 'http://localhost/' porque el Frontend está en el puerto 5500.
    const API_LOGIN_URL = 'http://localhost/' + PROJECT_FOLDER + '/Backend/api/login.php';

    loginForm.addEventListener('submit', function(event) {
        event.preventDefault();
        errorMessage.textContent = '';

        const username = this.username.value; 
        const password = this.password.value; 

        fetch(API_LOGIN_URL, { 
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ username: username, password: password }), 
        })
        .then(response => {
            if (response.status === 401) {
                errorMessage.textContent = 'Usuario o contraseña incorrectos.';
            }
            if (!response.ok) {
                throw new Error('Error en el servidor. Código: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.token) { 
                localStorage.setItem('auth_token', data.token);
                // ⭐ CORRECCIÓN 2: Ruta simple
                window.location.href = 'bienvenida.html'; 
            } else if (data.mensaje) {
                errorMessage.textContent = data.mensaje;
            }
        })
        .catch(error => {
            console.error('Error durante el login:', error);
            if (!errorMessage.textContent) { // Evita sobreescribir el 401
                errorMessage.textContent = 'No se pudo conectar con la API. Revise su ruta o XAMPP.';
            }
        });
    });
});