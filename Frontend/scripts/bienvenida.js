document.addEventListener('DOMContentLoaded', () => {
    const token = localStorage.getItem('auth_token');
    
    // Si no hay token, forzar al login (aunque login.js ya lo haga)
    if (!token) {
        window.location.href = 'login.html'; 
        return;
    }

    // ⭐ RUTA DE LA API PROTEGIDA ⭐
    const PROJECT_FOLDER = 'Creacion-de-un-Servicio-de-Autenticacion-con-ApiRest-JWT';
    const API_WELCOME_URL = 'http://localhost/' + PROJECT_FOLDER + '/Backend/api/welcome.php';

    // 1. Llamar al endpoint protegido
    fetch(API_WELCOME_URL, {
        method: 'GET',
        headers: {
            // CRÍTICO: Envía el token en la cabecera "Authorization: Bearer <token>"
            'Authorization': `Bearer ${token}` 
        }
    })
    .then(response => {
        // 2. Manejo de error de la API (401/403)
        if (response.status === 401 || response.status === 403) {
            localStorage.removeItem('auth_token'); 
            // Redirige a la página de "Acceso Denegado"
            window.location.href = 'sin_permisos.html'; 
            throw new Error('Acceso denegado o token inválido');
        }
        if (!response.ok) {
            throw new Error(`Error ${response.status} al obtener datos de bienvenida.`);
        }
        return response.json();
    })
    .then(data => {
        // 3. Mostrar datos del usuario (Solo si el token fue válido)
        const usuario = data.datos_usuario; 
        
        document.getElementById('welcome-message').textContent = `¡Bienvenido, ${usuario.username} (Rol: ${usuario.rol})!`;
        document.getElementById('current-time').textContent = `La hora actual es: ${new Date().toLocaleTimeString()}`;
        document.getElementById('user-details').textContent = JSON.stringify(data.datos_usuario, null, 2);
    })
    .catch(error => {
        console.error('Error:', error);
        // Si hay un error, limpiar y volver al login por precaución
        if (!error.message.includes('Acceso denegado')) {
            localStorage.removeItem('auth_token');
            window.location.href = 'login.html'; 
        }
    });

    // 4. Funcionalidad Cerrar Sesión
    document.getElementById('logout-button').addEventListener('click', () => {
        localStorage.removeItem('auth_token'); 
        window.location.href = 'login.html'; 
    });
});