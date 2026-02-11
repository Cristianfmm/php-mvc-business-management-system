<?php 
$root = realpath($_SERVER["DOCUMENT_ROOT"]);

include("constants.php");
include("queries.php");

// ==================== CERRAR SESION / AUTH ====================


function get_LogoutUsuario() {
    try {
        session_destroy();
        return json_encode(array("code" => 200, "response" => "Sesión cerrada exitosamente"));
    } catch (Exception $th) {
        return json_encode(array("code" => 500, "response" => $th->getMessage()));
    }
}

// ==================== CLIENTES ====================

function get_ObtenerClientes($base) {
    try {
        $result = obtenerClientes($base);
        return json_encode($result);
    } catch (Exception $th) {
        return json_encode(array("code" => 500, "response" => $th->getMessage()));
    }
}

function get_CrearCliente($base, $data) {
    try {
        // Validaciones
        if (empty($data['numero_documento']) || empty($data['nombre_completo'])) {
            return json_encode(array("code" => 400, "response" => "Campos requeridos incompletos"));
        }
        
        $result = crearCliente($base, $data);
        return json_encode($result);
    } catch (Exception $th) {
        return json_encode(array("code" => 500, "response" => $th->getMessage()));
    }
}

// ==================== PRODUCTOS ====================

function get_ObtenerProductos($base) {
    try {
        $result = obtenerProductos($base);
        return json_encode($result);
    } catch (Exception $th) {
        return json_encode(array("code" => 500, "response" => $th->getMessage()));
    }
}

function get_CrearProducto($base, $data) {
    try {
        // Validaciones
        if (empty($data['nombre']) || empty($data['precio_unitario'])) {
            return json_encode(array("code" => 400, "response" => "Campos requeridos incompletos"));
        }
        
        $result = crearProducto($base, $data);
        return json_encode($result);
    } catch (Exception $th) {
        return json_encode(array("code" => 500, "response" => $th->getMessage()));
    }
}

// ==================== FACTURAS ====================

function get_ObtenerFacturas($base, $filtros = []) {
    try {
        $result = obtenerFacturas($base, $filtros);
        return json_encode($result);
    } catch (Exception $th) {
        return json_encode(array("code" => 500, "response" => $th->getMessage()));
    }
}

function get_CrearFactura($base, $data) {
    try {
        // Validaciones
        if (empty($data['cliente_id']) || empty($data['items']) || count($data['items']) == 0) {
            return json_encode(array("code" => 400, "response" => "Debe seleccionar un cliente y al menos un producto"));
        }
        
        // Establecer valores por defecto
        if (empty($data['prefijo'])) $data['prefijo'] = 'FAC';
        if (empty($data['fecha_emision'])) $data['fecha_emision'] = date('Y-m-d');
        if (empty($data['estado'])) $data['estado'] = 'pendiente';
        if (empty($data['metodo_pago'])) $data['metodo_pago'] = 'efectivo';
        
        $result = crearFactura($base, $data);
        return json_encode($result);
    } catch (Exception $th) {
        return json_encode(array("code" => 500, "response" => $th->getMessage()));
    }
}

function get_DetalleFactura($base, $facturaId) {
    try {
        $result = obtenerDetalleFactura($base, $facturaId);
        return json_encode($result);
    } catch (Exception $th) {
        return json_encode(array("code" => 500, "response" => $th->getMessage()));
    }
}

function get_EstadisticasFacturacion($base) {
    try {
        $result = obtenerEstadisticas($base);
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
    case 'logoutUsuario':
        echo get_LogoutUsuario();
        break;
    // CLIENTES
    case 'obtenerClientes':
        echo get_ObtenerClientes($base);
        break;
    
    case 'crearCliente':
        echo get_CrearCliente($base, $_POST['data']);
        break;
    
    // PRODUCTOS
    case 'obtenerProductos':
        echo get_ObtenerProductos($base);
        break;
    
    case 'crearProducto':
        echo get_CrearProducto($base, $_POST['data']);
        break;
    
    // FACTURAS
    case 'obtenerFacturas':
        $filtros = isset($_POST['filtros']) ? $_POST['filtros'] : [];
        echo get_ObtenerFacturas($base, $filtros);
        break;
    
    case 'crearFactura':
        echo get_CrearFactura($base, $_POST['data']);
        break;
    
    case 'detalleFactura':
        echo get_DetalleFactura($base, $_POST['factura_id']);
        break;
    
    case 'estadisticasFacturacion':
        echo get_EstadisticasFacturacion($base);
        break;
    
    default:
        echo json_encode(array("code" => 400, "response" => "Parámetro no válido"));
        break;
}
?>