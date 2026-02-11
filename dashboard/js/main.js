/**
 * Funciones principales AJAX
 */

// Configuración base
const BASE_DB = "auth_system_db";

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


const BASE_FACTURACION = "facturacion_db";

// ==================== CLIENTES ====================

function obtenerClientes() {
    return $.ajax({
        url: 'controller/cont.php',
        type: 'POST',
        dataType: 'json',
        data: { 
            base: BASE_FACTURACION, 
            param: "obtenerClientes"
        }
    });
}

function crearCliente(data) {
    return $.ajax({
        url: 'controller/cont.php',
        type: 'POST',
        dataType: 'json',
        data: { 
            base: BASE_FACTURACION, 
            param: "crearCliente",
            data: data
        }
    });
}

// ==================== PRODUCTOS ====================

function obtenerProductos() {
    return $.ajax({
        url: 'controller/cont.php',
        type: 'POST',
        dataType: 'json',
        data: { 
            base: BASE_FACTURACION, 
            param: "obtenerProductos"
        }
    });
}

function crearProducto(data) {
    return $.ajax({
        url: 'controller/cont.php',
        type: 'POST',
        dataType: 'json',
        data: { 
            base: BASE_FACTURACION, 
            param: "crearProducto",
            data: data
        }
    });
}

// ==================== FACTURAS ====================

function obtenerFacturas(filtros = {}) {
    return $.ajax({
        url: 'controller/cont.php',
        type: 'POST',
        dataType: 'json',
        data: { 
            base: BASE_FACTURACION, 
            param: "obtenerFacturas",
            filtros: filtros
        }
    });
}

function crearFactura(data) {
    return $.ajax({
        url: 'controller/cont.php',
        type: 'POST',
        dataType: 'json',
        data: { 
            base: BASE_FACTURACION, 
            param: "crearFactura",
            data: data
        }
    });
}

function detalleFactura(facturaId) {
    return $.ajax({
        url: 'controller/cont.php',
        type: 'POST',
        dataType: 'json',
        data: { 
            base: BASE_FACTURACION, 
            param: "detalleFactura",
            factura_id: facturaId
        }
    });
}

function estadisticasFacturacion() {
    return $.ajax({
        url: 'controller/cont.php',
        type: 'POST',
        dataType: 'json',
        data: { 
            base: BASE_FACTURACION, 
            param: "estadisticasFacturacion"
        }
    });
}

// ==================== UTILIDADES ====================

function formatoMoneda(valor) {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0
    }).format(valor);
}

function formatoFecha(fecha) {
    const f = new Date(fecha + 'T00:00:00');
    return f.toLocaleDateString('es-CO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function calcularTotales(items) {
    let subtotal = 0;
    let ivaTotal = 0;
    
    items.forEach(item => {
        const itemSubtotal = item.cantidad * item.precio_unitario;
        const itemIva = itemSubtotal * (item.iva_porcentaje / 100);
        subtotal += itemSubtotal;
        ivaTotal += itemIva;
    });
    
    const total = subtotal + ivaTotal;
    
    return {
        subtotal: subtotal,
        iva: ivaTotal,
        total: total
    };
}

function generarPDFFactura(facturaId) {
    // Esta función abrirá una nueva ventana con el PDF de la factura
    window.open(`generar_pdf.php?factura_id=${facturaId}`, '_blank');
}

function validarFormularioFactura(data) {
    if (!data.cliente_id) {
        showWarning('Debe seleccionar un cliente');
        return false;
    }
    
    if (!data.items || data.items.length === 0) {
        showWarning('Debe agregar al menos un producto');
        return false;
    }
    
    for (let item of data.items) {
        if (item.cantidad <= 0) {
            showWarning('La cantidad debe ser mayor a 0');
            return false;
        }
        if (item.precio_unitario <= 0) {
            showWarning('El precio debe ser mayor a 0');
            return false;
        }
    }
    
    return true;
}