<?php
// === CONFIGURACIÓN CORS ===
header('Access-Control-Allow-Origin: *'); 
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST, OPTIONS'); 
header('Access-Control-Allow-Headers: Content-Type, Authorization'); 

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { 
    http_response_code(200); 
    exit; 
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    http_response_code(405); 
    echo json_encode(['success' => false, 'mensaje' => 'Método no permitido']); 
    exit; 
}

// === LÓGICA DE LOGIN ===
$jsonRecibido = file_get_contents('php://input');
$datos = json_decode($jsonRecibido, true);

$username = $datos['username'] ?? null;
$password = $datos['password'] ?? null;

if (!isset($username) || !isset($password)) { 
    http_response_code(400); 
    echo json_encode(['success' => false, 'mensaje' => 'Faltan datos de usuario o contraseña']); 
    exit;
}

// ARRAY DE USUARIOS VÁLIDOS
$usuariosValidos = [
    ['id' => 1, 'rol' => 'admin', 'username' => 'admin', 'password' => '1234'], 
    ['id' => 2, 'rol' => 'user', 'username' => 'user', 'password' => 'abcd'], 
    ['id' => 3, 'rol' => 'user', 'username' => 'jesus', 'password' => '5678'], 
];

$usuarioEncontrado = null;
foreach ($usuariosValidos as $user) {
    if ($user['username'] === $username && $user['password'] === $password) {
        $usuarioEncontrado = $user;
        break; 
    }
}

$secret = 'MI_SECRETO_SUPER_SEGURO';

function base64url_encode($data) { 
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
}

if ($usuarioEncontrado !== null) {
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];
    $ahora = time();
    $payload = [
        "userId" => $usuarioEncontrado['id'],
        "rol" => $usuarioEncontrado['rol'],
        "username" => $usuarioEncontrado['username'], 
        "iat" => $ahora,
        "exp" => $ahora + 3600 
    ];

    $header_b64 = base64url_encode(json_encode($header));
    $payload_b64 = base64url_encode(json_encode($payload));
    $signature = hash_hmac('sha256', "$header_b64.$payload_b64", $secret, true);
    $sig_b64 = base64url_encode($signature);
    $jwt = "$header_b64.$payload_b64.$sig_b64";

    echo json_encode([
        'success' => true, 
        'mensaje' => 'Autenticación exitosa', 
        'token' => $jwt
    ]);

} else {
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'mensaje' => 'Usuario o contraseña incorrectos'
    ]);
}
exit;
?>