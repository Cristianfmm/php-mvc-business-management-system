<?php
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);
ini_set("session.gc_maxlifetime", "144440");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * CONEXIÓN PDO
 */
function conexionPDO($db) {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $charset = "utf8mb4";

    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$db;charset=$charset",
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        die("Error PDO: " . $e->getMessage());
    }
}

/**
 * CONEXIÓN MySQLi
 */
function conexionMySQLi($db) {
    $host = "localhost";
    $user = "root";
    $pass = "";
    
    try {
        $link = new mysqli($host, $user, $pass, $db);
        
        if ($link->connect_error) {
            die("Error de conexión: " . $link->connect_error);
        }
        
        $link->set_charset("utf8mb4");
        return $link;
    } catch (Exception $e) {
        die("Error MySQLi: " . $e->getMessage());
    }
}

/**
 * Función de respuesta estándar
 */
function response($result, $message = null) {
    if (!isset($result)) {
        return array('code' => 500, 'response' => "Error en la petición");
    } else if (is_array($result) && empty($result)) {
        return array('code' => 200, 'response' => $result);
    } else if ($result === false) {
        return array('code' => 400, 'response' => $message ?? "Error al procesar");
    } else {
        return array('code' => 200, 'response' => $result);
    }
}

/**
 * Verificar sesión de usuario
 */
function verificarSesion() {
    if (!isset($_SESSION['usuario_id'])) {
        return false;
    }
    return true;
}

/**
 * Verificar rol de usuario
 */
function verificarRol($rolesPermitidos) {
    if (!isset($_SESSION['rol'])) {
        return false;
    }
    return in_array($_SESSION['rol'], $rolesPermitidos);
}
?>