<?php
/**
 * Funciones de consultas a la base de datos
 */

// ==================== USUARIOS ====================

function registrarUsuario($base, $datos) {
    try {
        $link = conexionMySQLi($base);
        
        // Validar email único
        $checkEmail = "SELECT id FROM usuarios WHERE email = ?";
        $stmt = $link->prepare($checkEmail);
        $stmt->bind_param("s", $datos['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $link->close();
            return response(false, "El email ya está registrado");
        }
        
        // Hash de contraseña
        $passwordHash = password_hash($datos['password'], PASSWORD_DEFAULT);
        
        $query = "INSERT INTO usuarios (nombre_completo, email, password) VALUES (?, ?, ?)";
        $stmt = $link->prepare($query);
        $stmt->bind_param("sss", $datos['nombre_completo'], $datos['email'], $passwordHash);
        
        if ($stmt->execute()) {
            $usuarioId = $stmt->insert_id;
            $link->close();
            return response([
                'id' => $usuarioId,
                'nombre_completo' => $datos['nombre_completo'],
                'email' => $datos['email']
            ], "Usuario registrado exitosamente");
        } else {
            $link->close();
            return response(false, "Error al registrar usuario");
        }
    } catch (Exception $e) {
        return array('code' => 500, 'response' => $e->getMessage());
    }
}

function loginUsuario($base, $datos) {
    try {
        $link = conexionMySQLi($base);
        
        $query = "SELECT id, nombre_completo, email, password, rol, estado FROM usuarios WHERE email = ?";
        $stmt = $link->prepare($query);
        $stmt->bind_param("s", $datos['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $link->close();
            return response(false, "Email o contraseña incorrectos");
        }
        
        $usuario = $result->fetch_assoc();
        
        // Verificar estado
        if ($usuario['estado'] !== 'activo') {
            $link->close();
            return response(false, "Usuario inactivo. Contacte al administrador");
        }
        
        // Verificar contraseña
        if (!password_verify($datos['password'], $usuario['password'])) {
            $link->close();
            return response(false, "Email o contraseña incorrectos");
        }
        
        // Actualizar último acceso
        $updateQuery = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?";
        $updateStmt = $link->prepare($updateQuery);
        $updateStmt->bind_param("i", $usuario['id']);
        $updateStmt->execute();
        
        $link->close();
        
        // Retornar datos del usuario (sin password)
        unset($usuario['password']);
        return response($usuario, "Login exitoso");
        
    } catch (Exception $e) {
        return array('code' => 500, 'response' => $e->getMessage());
    }
}

function verificarSesionUsuario($base, $usuarioId) {
    try {
        $link = conexionMySQLi($base);
        
        $query = "SELECT id, nombre_completo, email, rol FROM usuarios WHERE id = ? AND estado = 'activo'";
        $stmt = $link->prepare($query);
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $link->close();
            return response(false, "Sesión inválida");
        }
        
        $usuario = $result->fetch_assoc();
        $link->close();
        
        return response($usuario);
        
    } catch (Exception $e) {
        return array('code' => 500, 'response' => $e->getMessage());
    }
}

function recuperarPassword($base, $email) {
    try {
        $link = conexionMySQLi($base);
        
        $query = "SELECT id, nombre_completo FROM usuarios WHERE email = ? AND estado = 'activo'";
        $stmt = $link->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $link->close();
            return response(false, "Email no encontrado");
        }
        
        $usuario = $result->fetch_assoc();
        
        // Generar token de recuperación (aquí deberías implementar el envío por email)
        $token = bin2hex(random_bytes(32));
        
        // Guardar token en la base de datos (necesitarías crear una tabla para esto)
        
        $link->close();
        return response(['token' => $token], "Email de recuperación enviado");
        
    } catch (Exception $e) {
        return array('code' => 500, 'response' => $e->getMessage());
    }
}

function obtenerUsuarios($base) {
    try {
        $link = conexionMySQLi($base);
        
        $query = "SELECT id, nombre_completo, email, rol, estado, fecha_registro, ultimo_acceso 
                  FROM usuarios ORDER BY fecha_registro DESC";
        $result = $link->query($query);
        
        $usuarios = [];
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = $row;
        }
        
        $link->close();
        return response($usuarios);
        
    } catch (Exception $e) {
        return array('code' => 500, 'response' => $e->getMessage());
    }
}
// ==================== GOOGLE LOGIN ====================

// ==================== LOGIN GOOGLE ====================

function loginUsuarioGoogle($base, $datos) {
    try {
        $link = conexionMySQLi($base);

        $query = "SELECT id, estado 
                  FROM usuarios 
                  WHERE email = ?";
        $stmt = $link->prepare($query);
        $stmt->bind_param("s", $datos['email']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {

            $insert = "INSERT INTO usuarios
                       (nombre_completo, email, provider, provider_id, estado)
                       VALUES (?, ?, 'google', ?, 'activo')";

            $stmt = $link->prepare($insert);
            $stmt->bind_param(
                "sss",
                $datos['nombre_completo'],
                $datos['email'],
                $datos['provider_id']
            );

            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                throw new Exception("No se pudo crear el usuario Google");
            }

            $usuarioId = $stmt->insert_id;

        } else {

            $usuario = $result->fetch_assoc();

            if ($usuario['estado'] !== 'activo') {
                return response(false, "Usuario inactivo");
            }

            $usuarioId = $usuario['id'];
        }

        $update = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?";
        $stmt = $link->prepare($update);
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();

        $link->close();

        return response([
            'id' => $usuarioId,
            'email' => $datos['email'],
            'nombre_completo' => $datos['nombre_completo']
        ], "Login Google OK");

    } catch (Exception $e) {
        return response(false, $e->getMessage());
    }
}


?>