<?php
//Permitimos que javascript pueda hacer peticiones a este archivo.
header('Access-Control-Allow-Origin: *');//Permitir acceso desde cualquier origen(CORS)
header('Content-Type: application/json; charset=UTF-8');//Indica que responderemos con Json
header('Access-Control-Allow-Methods: POST');//Permitimos solo el método POST
header('Access-Control-Allow-Headers: Content-Type');//Permitimos solo el header Content-Type

// Verificamos que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Si no es POST, devolvemos un error
    echo json_encode([
        'success' => false,
        'mensaje' => 'Método no permitido'
    ]);
    http_response_code(405); // [MODIFICACIÓN] Añadimos el código 405
    exit;//Salir del script
}

//Recibimos el JSON que envió JavaScript.
$jsonRecibido = file_get_contents('php://input');//Lee el cuerpo de la petición
//Convertimos el JSON en un array asociativo de PHP
$datos = json_decode($jsonRecibido, true);//El parámetro true hace que sea array asociativo en lugar de objeto.

// Verificamos que llegaron los campos necesarios con isset()
if (!isset($datos['usuario']) || !isset($datos['password'])) {
    http_response_code(400); // [MODIFICACIÓN] Añadimos el código 400
    echo json_encode([
        'success' => false,
        'mensaje' => 'Faltan datos de usuario o contraseña'
    ]);
    exit;
}

//Array de usuarios válidos (en un caso real, esto vendría de una base de datos)
//Añadimos 'id' y 'rol' a los datos de usuario
$usuariosValidos= [
    ['id' => 1, 'rol' => 'admin', 'usuario' => 'administrador', 'password' => 'administrador'],
    ['id' => 2, 'rol' => 'user', 'usuario' => 'Jesus', 'password' => 'Jesus'],
    ['id' => 3, 'rol' => 'guest', 'usuario' => 'invitado', 'password' => 'invitado']
];

$usuarioEncontrado = null;// Inicializamos a null para almacenar los datos del usuario

// Recorremos el array buscando coincidencias
foreach ($usuariosValidos as $user) {
    if ($user['usuario'] === $datos['usuario'] && $user['password'] === $datos['password']) {
        $usuarioEncontrado = $user; // Guardamos el array de usuario encontrado
        break; // Salimos del bucle si encontramos coincidencia
    }
}

// Clave secreta: en producción guárdala fuera del código (env var, vault)
$secret = 'mi_secreto_super_largo_y_fuerte_@@@_CAMBIAR_EN_PROD';

/* ============
   FUNCIÓN JWT
   ============ */
// función necesaria para codificar en base64URL (no estaba en tu código pero es obligatoria)
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

if ($usuarioEncontrado !== null) { // Si encontramos un usuario válido

    // Generamos un token JWT simple (en un caso real, usaríamos una librería para JWT)
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];

    // Payload JSON estructurado
    $ahora = time();// Tiempo actual en segundos.
    $payload = [
        "userId" => $usuarioEncontrado['id'],
        "rol" => $usuarioEncontrado['rol'],
        "usuario" => $usuarioEncontrado['usuario'],
        "iat" => $ahora,             // Issued At (creado ahora)
        "exp" => $ahora + (3600)    // Expiración en 1 hora (3600 segundos)
    ];

    // codifica
    $header_b64 = base64url_encode(json_encode($header));
    $payload_b64 = base64url_encode(json_encode($payload));

    // firma con HMAC SHA256
    $signature = hash_hmac('sha256', "$header_b64.$payload_b64", $secret, true);
    $sig_b64 = base64url_encode($signature);

    // token final
    $jwt = "$header_b64.$payload_b64.$sig_b64";

    // devuelve JSON
    echo json_encode(['token' => $jwt, 'payload' => $payload]);

    // Si el usuario es válido, devolvemos éxito con la estructura que espera el javascript.
    echo json_encode([
        'success' => true,
        'mensaje' => 'Autenticación exitosa',
        'token' => $jwt// agregamos el token al JSON de respuesta.
    ]);

} else {
    // Si no encontramos un usuario válido, devolvemos error 401 Unauthorized
    http_response_code(401);

    // Si no es válido, devolvemos error con la estructura que espera el javascript(success y mensaje).
    echo json_encode([
        'success' => false,
        'mensaje' => 'Usuario o contraseña incorrectos. 401 Unauthorized.' // [MODIFICACIÓN] Mensaje actualizado
    ]);
}
exit;
?>
