// Esperamos a que el DOM cargue completamente con el evento DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {

    // Seleccionamos el formulario de login por su ID
    const formulario = document.getElementById('loginForm');
    
    // Escuchamos cuando se envía el formulario, cuando el ususario hace clic en el botón de enviar.
    formulario.addEventListener('submit', function(event) {
        
        // Prevenimos que el formulario se envíe de forma tradicional
        event.preventDefault();
        // Obtenemos los valores de los inputs con el .value que extrae el texto.
        const usuario = document.querySelector('input[name="usuario"]').value;
        const password = document.querySelector('input[name="password"]').value;
        
        // Creamos un objeto con los datos del formulario
        const datos = {
            usuario: usuario,
            password: password
        };
        // Enviamos los datos al servidor usando fetch
        // Hacemos la petición al backend
        fetch('Backend/api/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'// Indicamos que enviamos JSON
            },
            body: JSON.stringify(datos)// Convertimos el objeto a JSON
        })
        .then(response=> response.json()) //Espera la respuesta del servidor y convierte el json que envio el backend a un objeto de JavaScript para usarlo.
        .then(data => {
            // Aquí procesamos la respuesta
            if (data.success) {
                // Login exitoso
                alert('Login exitoso');
                window.location.href= 'bienvenida.html'; // Redirige a la página de bienvenida
            } else {
                // Login fallido
                alert('Error: ' + data.mensaje);
            }
        })
        .catch(error => {
            //Manejo el error en caso que falle por el servidor o la red.
            console.error('Error:', error);
            alert('Ocurrió un error al procesar la solicitud.');
        })
    });  
});