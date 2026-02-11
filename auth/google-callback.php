<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/controller/queries.php';

$client = new Google_Client();
$client->setClientId('TU_CLIENT_ID');
$client->setClientSecret('TU_CLIENT_SECRET');
$client->setRedirectUri('http://localhost/auth/google-callback.php');

// Si Google no devuelve código
if (!isset($_GET['code'])) {
    header("Location: /index2.html");
    exit;
}

// Intercambiar código por token
$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
$client->setAccessToken($token);

// Obtener datos del usuario
$oauth = new Google_Service_Oauth2($client);
$userInfo = $oauth->userinfo->get();

$datos = [
    'email' => $userInfo->email,
    'nombre_completo' => $userInfo->name,
    'provider_id' => $userInfo->id
];

// Login / Registro Google
$respuesta = loginUsuarioGoogle($base, $datos);

if ($respuesta['code'] === false) {
    header("Location: /index2.html?error=google");
    exit;
}

// Guardar sesión (igual que login normal)
$_SESSION['usuario'] = $respuesta['response'];

header("Location: /dashboard/");
exit;
