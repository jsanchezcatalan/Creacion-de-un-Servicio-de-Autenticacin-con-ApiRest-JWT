document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const errorMessage = document.getElementById('error-message');

    // Comprobar si ya existe un token. Si existe, redirigir a bienvenida.html
    if (localStorage.getItem('auth_token')) {
        window.location.href = 'bienvenida.html';
        return;
    }

    loginForm.addEventListener('submit', function(event) {
        event.preventDefault();
        errorMessage.textContent = '';

        // Obtener credenciales del formulario
        const username = this.username.value;
        const password = this.password.value;

        // Requisito: utiliza JavaScript y fetch para enviar las credenciales [cite: 53]
        fetch('../../Backend/api/login.php', { // RUTA AJUSTADA a tu estructura
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ username: username, password: password }),
        })
        .then(response => {
            // Requisito: Si el servidor responde con 401, redirige o maneja el error [cite: 94]
            if (response.status === 401) {
                errorMessage.textContent = 'Usuario o contraseña incorrectos.';
                throw new Error('401 Unauthorized');
            }
            if (!response.ok) {
                // Manejar otros errores HTTP que no sean 401
                errorMessage.textContent = 'Error en el servidor. Código: ' + response.status;
                throw new Error('Server error: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.token) {
                // Requisito: Usa localStorage para almacenar el token [cite: 85]
                localStorage.setItem('auth_token', data.token);
                // Redirige a la pantalla de bienvenida [cite: 72]
                window.location.href = 'bienvenida.html'; 
            } else {
                // Error si la respuesta es 200 pero el servidor indica un fallo lógico
                errorMessage.textContent = data.mensaje || 'Error de autenticación.';
            }
        })
        .catch(error => {
            console.error('Error durante el login:', error);
            // El mensaje de error ya se estableció para el 401
        });
    });
});