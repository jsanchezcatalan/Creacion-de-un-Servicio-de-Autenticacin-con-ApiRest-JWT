<!--Reciba los datos JSON
Los compara con un array de usuarios
Devuelva una respuesta JSON (éxito/error)
Si es válido, genere un JWT y devolverlo en la respuesta-->

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
    exit;//Salir del script
}
//Recibimos el JSON que envió JavaScript.
$jsonRecibido = file_get_contents('php://input');//Lee el cuerpo de la petición
//Convertimos el JSON en un array asociativo de PHP
$datos = json_decode($jsonRecibido, true);//El parámetro true hace que sea array asociativo en lugar de objeto.

// Verificamos que llegaron los campos necesarios con isset()
if (!isset($datos['usuario']) || !isset($datos['password'])) {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Faltan datos de usuario o contraseña'
    ]);
    exit;
}

//Array de usuarios válidos (en un caso real, esto vendría de una base de datos)
$usuariosValidos= [
    ['usuario' => 'administrador', 'password' => 'administrador'],
    ['usuario' => 'Jesus', 'password' => 'Jesus'],
    ['usuario' => 'invitado', 'password' => 'invitado']
];

$usuarioEncontrado = false;//Variable para saber si el usuario es válido
// Recorremos el array buscando coincidencias
foreach ($usuariosValidos as $user) {
    if ($user['usuario'] === $datos['usuario'] && $user['password'] === $datos['password']) {
        $usuarioEncontrado = true;
        break; // Salimos del bucle si encontramos coincidencia
    }
}

if ($usuarioEncontrado) {
    // Si el usuario es válido, devolvemos éxito con la estructura que espera el javascript(success y mensaje).
    echo json_encode([
        'success' => true,
        'mensaje' => 'Autenticación exitosa'
    ]);
} else {
    // Si no es válido, devolvemos error con la estructura que espera el javascript(success y mensaje).
    echo json_encode([
        'success' => false,
        'mensaje' => 'Usuario o contraseña incorrectos'
    ]);
}
?>
