<?php
// Permite acceso desde cualquier origen
header('Access-Control-Allow-Origin: *');
// Requisito: Utiliza json_encode para devolver respuestas en formato JSON [cite: 82]
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$headers = getallheaders();

// Clave secreta (DEBE coincidir con login.php)
$secret = 'MI_SECRETO_SUPER_SEGURO';

// Función base64url_encode (mantener la misma que en login.php)
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// Función base64url_decode (necesaria para la firma)
function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

// Requisito: Token de autenticación en la cabecera (Authorization: Bearer <token>) [cite: 85, 86]
if (!isset($headers["Authorization"]) || !preg_match('/Bearer\s(\S+)/', $headers["Authorization"], $matches)) {
    // Si falta el token (No Autenticado), devolvemos 403 para forzar la redirección en el cliente [cite: 94]
    http_response_code(403);
    echo json_encode(["error" => "Falta token. 403 Forbidden"]);
    exit;
}

$jwt = $matches[1];

// Separar token
$partes = explode(".", $jwt);

if (count($partes) !== 3) {
    http_response_code(403); // Token malformado.
    echo json_encode(["error" => "Token malformado. 403 Forbidden"]);
    exit;
}

list($headerB64, $payloadB64, $signatureB64) = $partes;

// Decodificar payload
$payloadJson = base64url_decode($payloadB64);
$payload = json_decode($payloadJson, true);

// 1. Validación de Expiración
if (isset($payload["exp"]) && $payload["exp"] < time()) {
    http_response_code(403); // Token caducado. 403 Forbidden [cite: 68]
    echo json_encode(["error" => "Token caducado. 403 Forbidden"]);
    exit;
}

// 2. Validación de Firma
$firmaEsperada = base64url_encode(
    hash_hmac('sha256', "$headerB64.$payloadB64", $secret, true)
);

if (!hash_equals($firmaEsperada, $signatureB64)) {
    http_response_code(403); // Firma inválida. 403 Forbidden [cite: 68]
    echo json_encode(["error" => "Token inválido (Firma). 403 Forbidden"]);
    exit;
}

// Requisito: Todo correcto. Responde con datos del usuario si el token es válido 
echo json_encode([
    "ok" => true,
    "mensaje" => "Acceso permitido",
    "username" => $payload["username"], // Nombre para el mensaje personalizado 
    "hora_actual" => date('H:i:s') // Requisito: La hora actual 
]);
?>