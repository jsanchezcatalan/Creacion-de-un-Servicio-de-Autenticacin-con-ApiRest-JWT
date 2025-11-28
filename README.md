
🛡️ Servicio de Autenticación con API RESTful y JWT (Simulado)
Este proyecto desarrolla un servicio de autenticación moderno utilizando una API RESTful implementada en PHP, donde el estado del usuario se mantiene mediante un token almacenado en el cliente (simulando un JSON Web Token - JWT).

📝 Resumen Funcional
La aplicación se compone de una interfaz de usuario HTML/JavaScript y un backend PHP que actúa como API.

Login (HTML/JS): El usuario ingresa credenciales y JavaScript utiliza fetch para enviarlas al servidor.

Manejo del Token: El token recibido se almacena en el cliente usando localStorage.

Pantalla de Bienvenida (HTML/JS): Llama al endpoint protegido (/api/welcome) para obtener y mostrar datos personalizados, incluyendo la hora actual.

Manejo de Errores: Si la API responde con 401 o 403 Forbidden, el cliente JavaScript redirige a la pantalla de "No Tienes Permisos".

Cerrar Sesión: Elimina el token de localStorage y redirige al login.
