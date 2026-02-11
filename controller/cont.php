<?php 
$root = realpath($_SERVER["DOCUMENT_ROOT"]);

include("constants.php");
include("queries.php");

// ==================== USUARIOS / AUTH ====================

function get_RegistrarUsuario($base, $data) {
    try {
        // Validaciones
        if (empty($data['nombre_completo']) || empty($data['email']) || empty($data['password'])) {
            return json_encode(array("code" => 400, "response" => "Todos los campos son requeridos"));
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return json_encode(array("code" => 400, "response" => "Email inválido"));
        }
        
        if (strlen($data['password']) < 6) {
            return json_encode(array("code" => 400, "response" => "La contraseña debe tener al menos 6 caracteres"));
        }
        
        $result = registrarUsuario($base, $data);
        return json_encode($result);
    } catch (Exception $th) {
        return json_encode(array("code" => 500, "response" => $th->getMessage()));
    }
}

function get_LoginUsuario($base, $data) {
    try {
        // Validaciones
        if (empty($data['email']) || empty($data['password'])) {
            return json_encode(array("code" => 400, "response" => "Email y contraseña son requeridos"));
        }
        
        $result = loginUsuario($base, $data);
        
        // Si el login es exitoso, iniciar sesión
        if ($result['code'] === 200) {
            $usuario = $result['response'];
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre_completo'] = $usuario['nombre_completo'];
            $_SESSION['email'] = $usuario['email'];
            $_SESSION['rol'] = $usuario['rol'];
            $_SESSION['logged_in'] = true;
        }
        
        return json_encode($result);
    } catch (Exception $th) {
        return json_encode(array("code" => 500, "response" => $th->getMessage()));
    }
}

function get_LogoutUsuario() {
    try {
        session_destroy();
        return json_encode(array("code" => 200, "response" => "Sesión cerrada exitosamente"));
    } catch (Exception $th) {
        return json_encode(array("code" => 500, "response" => $th->getMessage()));
    }
}

function get_VerificarSesion($base) {
    try {
        if (!verificarSesion()) {
            return json_encode(array("code" => 401, "response" => "No autorizado"));
        }
        
        $result = verificarSesionUsuario($base, $_SESSION['usuario_id']);
        return json_encode($result);
    } catch (Exception $th) {
        return json_encode(array("code" => 500, "response" => $th->getMessage()));
    }
}

function get_RecuperarPassword($base, $data) {
    try {
        if (empty($data['email'])) {
            return json_encode(array("code" => 400, "response" => "Email es requerido"));
        }
        
        $result = recuperarPassword($base, $data['email']);
        return json_encode($result);
    } catch (Exception $th) {
        return json_encode(array("code" => 500, "response" => $th->getMessage()));
    }
}

function get_ObtenerUsuarios($base) {
    try {
        if (!verificarSesion()) {
            return json_encode(array("code" => 401, "response" => "No autorizado"));
        }
        
        $result = obtenerUsuarios($base);
        return json_encode($result);
    } catch (Exception $th) {
        return json_encode(array("code" => 500, "response" => $th->getMessage()));
    }
}

// ==================== ROUTER ====================
$base = isset($_POST['base']) ? $_POST['base'] : 'auth_system_db';

if (!isset($_POST['param'])) {
    echo json_encode(array("code" => 400, "response" => "Parámetro no especificado"));
    exit;
}

switch ($_POST['param']) {
    // AUTH
    case 'registrarUsuario':
        echo get_RegistrarUsuario($base, $_POST['data']);
        break;
    
    case 'loginUsuario':
        echo get_LoginUsuario($base, $_POST['data']);
        break;
    
    case 'logoutUsuario':
        echo get_LogoutUsuario();
        break;
    
    case 'verificarSesion':
        echo get_VerificarSesion($base);
        break;
    
    case 'recuperarPassword':
        echo get_RecuperarPassword($base, $_POST['data']);
        break;
    
    case 'obtenerUsuarios':
        echo get_ObtenerUsuarios($base);
        break;
    
    default:
        echo json_encode(array("code" => 400, "response" => "Parámetro no válido"));
        break;
}
?>