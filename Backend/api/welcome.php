<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$headers = getallheaders();

if (!isset($headers["Authorization"]) || strpos($headers["Authorization"], 'Bearer ') !== 0) {
    http_response_code(403);
    echo json_encode(["error" => "Acceso Prohibido. Falta token."]);
    exit;
}

$auth = $headers["Authorization"];
$jwt = str_replace("Bearer ", "", $auth);
$secret = 'MI_SECRETO_SUPER_SEGURO';

$partes = explode(".", $jwt);
if (count($partes) !== 3) {
    http_response_code(403);
    echo json_encode(["error" => "Token malformado."]);
    exit;
}

list($headerB64, $payloadB64, $signatureB64) = $partes;
$payloadJson = base64_decode(strtr($payloadB64, '-_', '+/'));
$payload = json_decode($payloadJson, true);

if (isset($payload["exp"]) && $payload["exp"] < time()) {
    http_response_code(403);
    echo json_encode(["error" => "Token caducado."]);
    exit;
}

$firmaEsperada = rtrim(strtr(base64_encode(
    hash_hmac('sha256', "$headerB64.$payloadB64", $secret, true)
), '+/', '-_'), '=');

if (!hash_equals($firmaEsperada, $signatureB64)) {
    http_response_code(403);
    echo json_encode(["error" => "Firma inválida."]);
    exit;
}

// RESPUESTA CORRECTA CON EL FORMATO QUE ESPERA bienvenida.js
$username = $payload['username'] ?? 'Usuario';
$horaActual = date('H:i:s');

echo json_encode([
    "ok" => true,
    "mensaje" => "Bienvenido, " . $username,
    "hora_actual" => $horaActual,
    "datos_usuario" => [
        "userId" => $payload['userId'],
        "username" => $payload['username'],
        "rol" => $payload['rol']
    ]
]);
?>