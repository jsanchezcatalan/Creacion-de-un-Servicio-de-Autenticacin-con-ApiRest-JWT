document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const errorMessage = document.getElementById('error-message');

    if (localStorage.getItem('auth_token')) { 
        window.location.href = 'bienvenida.html'; 
        return;
    }

    // ⭐⭐ CAMBIA ESTO POR EL NOMBRE EXACTO DE TU CARPETA ⭐⭐
    const PROJECT_FOLDER = 'Creacion-de-un-Servicio-de-Autenticacion-con-ApiRest-JWT'; // ← PON EL NOMBRE COMPLETO AQUÍ
    const API_LOGIN_URL = 'http://localhost/' + PROJECT_FOLDER + '/Backend/api/login.php';

    console.log('URL de la API:', API_LOGIN_URL); // ← Para verificar la ruta

    loginForm.addEventListener('submit', function(event) {
        event.preventDefault();
        console.log('Formulario enviado'); // ← Para verificar que se dispara el evento
        
        errorMessage.textContent = '';

        const username = this.username.value; 
        const password = this.password.value;
        
        console.log('Usuario:', username); // ← Para verificar los datos
        console.log('Contraseña:', password);

        fetch(API_LOGIN_URL, { 
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ username: username, password: password }), 
        })
        .then(response => {
            console.log('Respuesta recibida:', response); // ← Para ver la respuesta
            
            if (response.status === 401) {
                errorMessage.textContent = 'Usuario o contraseña incorrectos.';
                throw new Error('401 Unauthorized');
            }
            if (!response.ok) {
                throw new Error('Error en el servidor. Código: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Datos recibidos:', data); // ← Para ver el JSON
            
            if (data.success && data.token) { 
                localStorage.setItem('auth_token', data.token);
                window.location.href = 'bienvenida.html'; 
            } else if (data.mensaje) {
                errorMessage.textContent = data.mensaje;
            }
        })
        .catch(error => {
            console.error('Error durante el login:', error);
            if (!errorMessage.textContent) {
                errorMessage.textContent = 'No se pudo conectar con la API. Revise su ruta o XAMPP.';
            }
        });
    });
});