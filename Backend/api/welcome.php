<?php
// === CONFIGURACIÓN CORS (CRÍTICO: AÑADIDO MANEJO DE OPTIONS) ===
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8'); // Requisito: usar json_encode [cite: 82]
header('Access-Control-Allow-Methods: GET, OPTIONS'); // Permitir OPTIONS
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// 1. MANEJO DEL PREFLIGHT CORS (Soluciona el 405 en este endpoint)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 2. OBTENER Y VERIFICAR EL TOKEN
$headers = getallheaders();

// Requisito: Enviar el token en la cabecera (Authorization: Bearer <token>) [cite: 85, 86]
if (!isset($headers["Authorization"]) || strpos($headers["Authorization"], 'Bearer ') !== 0) {
    http_response_code(403); // Requisito: Responder con 403 Forbidden [cite: 68]
    echo json_encode(["error" => "Acceso Prohibido. Falta token."]);
    exit;
}

$auth = $headers["Authorization"];
$jwt = str_replace("Bearer ", "", $auth);

// Clave secreta (sincronizada con login.php)
$secret = 'MI_SECRETO_SUPER_SEGURO';

// Lógica de separación de partes y decodificación
$partes = explode(".", $jwt);
if (count($partes) !== 3) {
    http_response_code(403);
    echo json_encode(["error" => "Token malformado."]);
    exit;
}
list($headerB64, $payloadB64, $signatureB64) = $partes;
$payloadJson = base64_decode(strtr($payloadB64, '-_', '+/'));
$payload = json_decode($payloadJson, true);

// Validación de Expiración
if (isset($payload["exp"]) && $payload["exp"] < time()) {
    http_response_code(403);
    echo json_encode(["error" => "Token caducado."]);
    exit;
}

// Validación de Firma
$firmaEsperada = rtrim(strtr(base64_encode(
    hash_hmac('sha256', "$headerB64.$payloadB64", $secret, true)
), '+/', '-_'), '=');

if (!hash_equals($firmaEsperada, $signatureB64)) {
    http_response_code(403);
    echo json_encode(["error" => "Firma inválida."]);
    exit;
}

// 3. RESPUESTA DE ÉXITO (Token válido)
// Requisito: Mensaje personalizado con el nombre de usuario y la hora actual 
$username = $payload['username'] ?? 'Usuario';
$horaActual = date('H:i:s');

echo json_encode([
    "ok" => true,
    "mensaje" => "Bienvenido, " . $username,
    "hora_actual" => $horaActual
]);
?>