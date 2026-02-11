/**
 * Funciones principales AJAX
 */

// Configuración base
const BASE_DB = "auth_system_db";

// ==================== USUARIOS / AUTH ====================

function registrarUsuario(data) {
    return $.ajax({
        url: 'controller/cont.php',
        type: 'POST',
        dataType: 'json',
        data: { 
            base: BASE_DB, 
            param: "registrarUsuario", 
            data: data 
        }
    });
}

function loginUsuario(data) {
    return $.ajax({
        url: 'controller/cont.php',
        type: 'POST',
        dataType: 'json',
        data: { 
            base: BASE_DB, 
            param: "loginUsuario", 
            data: data 
        }
    });
}

function logoutUsuario() {
    return $.ajax({
        url: 'controller/cont.php',
        type: 'POST',
        dataType: 'json',
        data: { 
            base: BASE_DB, 
            param: "logoutUsuario" 
        }
    });
}

function verificarSesion() {
    return $.ajax({
        url: 'controller/cont.php',
        type: 'POST',
        dataType: 'json',
        data: { 
            base: BASE_DB, 
            param: "verificarSesion" 
        }
    });
}

function recuperarPassword(email) {
    return $.ajax({
        url: 'controller/cont.php',
        type: 'POST',
        dataType: 'json',
        data: { 
            base: BASE_DB, 
            param: "recuperarPassword",
            data: { email: email }
        }
    });
}

function obtenerUsuarios() {
    return $.ajax({
        url: 'controller/cont.php',
        type: 'POST',
        dataType: 'json',
        data: { 
            base: BASE_DB, 
            param: "obtenerUsuarios"
        }
    });
}

