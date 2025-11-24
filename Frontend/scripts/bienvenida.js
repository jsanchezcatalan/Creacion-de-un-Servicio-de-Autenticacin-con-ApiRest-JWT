document.addEventListener('DOMContentLoaded', () => {
    const token = localStorage.getItem('auth_token');
    const welcomeMessage = document.getElementById('welcomeMessage');
    const serverTime = document.getElementById('serverTime');
    const logoutButton = document.getElementById('logoutButton');

    // Si no hay token, redirige a sin_permisos.html [cite: 67]
    if (!token) {
        window.location.href = 'sin_permisos.html';
        return;
    }

    // Requisito: Obtiene los datos del usuario llamando a un endpoint protegido [cite: 64]
    fetch('../../Backend/api/welcome.php', { // RUTA AJUSTADA a tu estructura
        method: 'GET',
        headers: {
            // Requisito: Envía el token en las cabeceras HTTP (Authorization: Bearer <token>) 
            'Authorization': `Bearer ${token}`
        }
    })
    .then(response => {
        // Requisito: Si el servidor responde con 401 o 403, redirige [cite: 94]
        if (response.status === 401 || response.status === 403) {
            // Limpia el token (por si está caducado o inválido) [cite: 72]
            localStorage.removeItem('auth_token');
            // Requisito: El cliente redirige automáticamente a la página de error [cite: 69]
            window.location.href = 'sin_permisos.html';
            throw new Error('Acceso no autorizado/prohibido.');
        }
        if (!response.ok) {
            throw new Error('Error al obtener datos del servidor.');
        }
        return response.json();
    })
    .then(data => {
        // Muestra el mensaje personalizado 
        welcomeMessage.textContent = `¡Bienvenido, ${data.username}!`;
        // Muestra la hora actual 
        serverTime.textContent = `Hora actual del servidor: ${data.hora_actual}`;
    })
    .catch(error => {
        console.error('Error fetching welcome data:', error);
        // Si hay otro tipo de error, por seguridad, redirigimos
        if (error.message !== 'Acceso no autorizado/prohibido.') {
             localStorage.removeItem('auth_token');
             window.location.href = 'sin_permisos.html';
        }
    });

    // Funcionalidad para Cerrar Sesión
    logoutButton.addEventListener('click', function() {
        // Requisito: Al hacer clic, elimina el token almacenado [cite: 72]
        localStorage.removeItem('auth_token');
        // Requisito: Redirige al usuario a la pantalla de login [cite: 72]
        window.location.href = 'login.html';
    });
});