<?php
// === CONFIGURACIÓN CORS (CRÍTICO PARA EL ERROR 405) ===
// Permite acceso desde cualquier origen
header('Access-Control-Allow-Origin: *'); 
// Indica que responderemos con JSON
header('Content-Type: application/json; charset=UTF-8');
// Permite los métodos POST y OPTIONS
header('Access-Control-Allow-Methods: POST, OPTIONS'); 
// Permite los headers Content-Type y Authorization
header('Access-Control-Allow-Headers: Content-Type, Authorization'); 


// 1. MANEJO DEL PREFLIGHT CORS (SOLUCIONA EL ERROR 405)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Si el navegador pide OPTIONS, respondemos con éxito (200) y salimos
    http_response_code(200); 
    exit; 
}


// 2. VERIFICACIÓN DEL MÉTODO (POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Si no es POST (y no fue OPTIONS), devolvemos 405
    http_response_code(405); 
    echo json_encode([
        'success' => false,
        'mensaje' => 'Método no permitido (Debe ser POST)'
    ]);
    exit;
}

// === LÓGICA DE LOGIN ===

// Recibir JSON del cliente
$jsonRecibido = file_get_contents('php://input');
$datos = json_decode($jsonRecibido, true);

// Asumimos que el cliente envía 'username' y 'password'
$username = $datos['username'] ?? null;
$password = $datos['password'] ?? null;

if (!isset($username) || !isset($password)) { 
    http_response_code(400); 
    echo json_encode([
        'success' => false,
        'mensaje' => 'Faltan datos de usuario o contraseña'
    ]);
    exit;
}

[cite_start]// ¡ARRAY DE USUARIOS VÁLIDOS (Incluye tus pruebas y los requisitos del profesor)[cite: 90, 91]!
$usuariosValidos = [
[cite_start]    ['id' => 1, 'rol' => 'admin', 'username' => 'admin', 'password' => '1234'], // Requisito [cite: 90]
    [cite_start]['id' => 2, 'rol' => 'user', 'username' => 'user', 'password' => 'abcd'], // Requisito [cite: 91]
    ['id' => 3, 'rol' => 'user', 'username' => 'jesus', 'password' => '1234'], 
    ['id' => 4, 'rol' => 'admin', 'username' => 'carlos', 'password' => '5678'] 
];

$usuarioEncontrado = null;
foreach ($usuariosValidos as $user) {
    if ($user['username'] === $username && $user['password'] === $password) {
        $usuarioEncontrado = $user;
        break; 
    }
}

// Clave secreta (debe coincidir con welcome.php)
$secret = 'MI_SECRETO_SUPER_SEGURO';

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

[cite_start]if ($usuarioEncontrado !== null) { // Credenciales correctas (Valida usuario [cite: 54])

    // Generación del JWT
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];
    $ahora = time();
    $payload = [
        "userId" => $usuarioEncontrado['id'],
        "rol" => $usuarioEncontrado['rol'],
        "username" => $usuarioEncontrado['username'], 
        "iat" => $ahora,
        "exp" => $ahora + (3600) 
    ];

    $header_b64 = base64url_encode(json_encode($header));
    $payload_b64 = base64url_encode(json_encode($payload));
    $signature = hash_hmac('sha256', "$header_b64.$payload_b64", $secret, true);
    $sig_b64 = base64url_encode($signature);
    $jwt = "$header_b64.$payload_b64.$sig_b64";

    [cite_start]// Responde con un token [cite: 58]
    echo json_encode([
        'success' => true,
        'mensaje' => 'Autenticación exitosa',
        'token' => $jwt
    ]);

} else {
[cite_start]    // Si son incorrectas, responde con 401 Unauthorized [cite: 59]
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'mensaje' => 'Usuario o contraseña incorrectos. 401 Unauthorized.'
    ]);
}
exit;
?>