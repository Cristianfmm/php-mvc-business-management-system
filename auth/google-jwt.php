<?php
$root = realpath($_SERVER["DOCUMENT_ROOT"]);

// includes
require_once $root . '/controller/constants.php';
require_once $root . '/controller/queries.php';

// sesión segura
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// asegurar acceso a la config
$base = 'auth_system_db';

// leer JWT
$data = json_decode(file_get_contents("php://input"), true);
$jwt = $data['credential'] ?? null;

if (!$jwt) {
    echo json_encode(['success' => false]);
    exit;
}

// decodificar JWT
$parts = explode('.', $jwt);
$payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

// datos usuario
$datos = [
    'email' => $payload['email'],
    'nombre_completo' => $payload['name'] ?? 'Usuario Google',
    'provider_id' => $payload['sub']
];

// login / registro
$respuesta = loginUsuarioGoogle($base, $datos);

// guardar sesión
$_SESSION['usuario'] = $respuesta['response'];

echo json_encode(['success' => true]);
