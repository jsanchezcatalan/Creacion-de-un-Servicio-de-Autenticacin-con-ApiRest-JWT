document.addEventListener('DOMContentLoaded', () => {
    const token = localStorage.getItem('auth_token');
    
    if (!token) {
        window.location.href = 'login.html'; 
        return;
    }

    const PROJECT_FOLDER = 'Creacion-de-un-Servicio-de-Autenticacion-con-ApiRest-JWT';
    const API_WELCOME_URL = 'http://localhost/' + PROJECT_FOLDER + '/Backend/api/welcome.php';

    fetch(API_WELCOME_URL, {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}` 
        }
    })
    .then(response => {
        if (response.status === 401 || response.status === 403) {
            localStorage.removeItem('auth_token'); 
            window.location.href = 'sin_permisos.html'; 
            throw new Error('Acceso denegado o token inválido');
        }
        if (!response.ok) {
            throw new Error(`Error ${response.status} al obtener datos de bienvenida.`);
        }
        return response.json();
    })
    .then(data => {
        const usuario = data.datos_usuario; 
        
        document.getElementById('welcomeMessage').textContent =
            `¡Bienvenido, ${usuario.username} (Rol: ${usuario.rol})!`;

        document.getElementById('serverTime').textContent =
            `La hora actual es: ${data.hora_actual}`;
    })
    .catch(error => {
        console.error('Error:', error);
        if (!error.message.includes('Acceso denegado')) {
            localStorage.removeItem('auth_token');
            window.location.href = 'login.html'; 
        }
    });

    document.getElementById('logoutButton').addEventListener('click', () => {
        localStorage.removeItem('auth_token'); 
        window.location.href = 'login.html'; 
    });
});
