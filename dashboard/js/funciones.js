
// ==================== UTILIDADES ====================

function showLoading(message = 'Cargando...') {
    Swal.fire({
        title: message,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

function hideLoading() {
    Swal.close();
}

function showSuccess(message, timer = 2000) {
    return Swal.fire({
        icon: 'success',
        title: '¡Éxito!',
        text: message,
        timer: timer,
        showConfirmButton: false
    });
}

function showError(message) {
    return Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message,
        confirmButtonColor: '#d33'
    });
}

function showWarning(message) {
    return Swal.fire({
        icon: 'warning',
        title: 'Atención',
        text: message,
        confirmButtonColor: '#f0ad4e'
    });
}

function showConfirm(title, text) {
    return Swal.fire({
        title: title,
        text: text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar'
    });
}

function validarEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validarPassword(password) {
    return password.length >= 6;
}

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    if (input.type === "password") {
        input.type = "text";
    } else {
        input.type = "password";
    }
}

// factura


function eliminarItem(index) {
    itemsFactura.splice(index, 1);
    renderizarItems();
    calcularTotalesFactura();
}

function renderizarItems() {
    let html = '';
    
    if (itemsFactura.length === 0) {
        html = '<tr><td colspan="6" class="text-center text-muted">No hay items agregados</td></tr>';
    } else {
        itemsFactura.forEach((item, index) => {
            html += `
                <tr>
                    <td>${item.producto_nombre}</td>
                    <td class="text-center">${item.cantidad}</td>
                    <td class="text-end">${formatoMoneda(item.precio_unitario)}</td>
                    <td class="text-center">${item.iva_porcentaje}%</td>
                    <td class="text-end">${formatoMoneda(item.subtotal)}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-eliminar btn-sm" onclick="eliminarItem(${index})">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                            </svg>
                        </button>
                    </td>
                </tr>
            `;
        });
    }
    
    $('#tablaItems').html(html);
}

function calcularTotalesFactura() {
    const totales = calcularTotales(itemsFactura);
    const descuento = parseFloat($('#descuento').val()) || 0;
    const totalFinal = totales.total - descuento;
    
    $('#subtotalDisplay').text(formatoMoneda(totales.subtotal));
    $('#ivaDisplay').text(formatoMoneda(totales.iva));
    $('#totalDisplay').text(formatoMoneda(totalFinal));
}

function cargarClientes() {
    obtenerClientes().done(function(response) {
        if (response.code === 200) {
            let html = '<option value="">Seleccione un cliente...</option>';
            response.response.forEach(cliente => {
                html += `<option value="${cliente.id}">${cliente.nombre_completo} - ${cliente.numero_documento}</option>`;
            });
            $('#cliente_id').html(html);
        }
    });
}

function cargarProductos() {
    obtenerProductos().done(function(response) {
        if (response.code === 200) {
            productosGlobales = response.response;
            let html = '<option value="">Seleccione...</option>';
            response.response.forEach(prod => {
                html += `<option value="${prod.id}">${prod.nombre} - ${formatoMoneda(prod.precio_unitario)}</option>`;
            });
            $('#producto_temp').html(html);
        }
    });
}

function agregarItem() {
    const productoId = $('#producto_temp').val();
    const cantidad = parseFloat($('#cantidad_temp').val());
    
    if (!productoId || cantidad <= 0) {
        showWarning('Seleccione un producto y cantidad válida');
        return;
    }
    
    const producto = productosGlobales.find(p => p.id == productoId);
    const subtotal = cantidad * producto.precio_unitario;
    
    const item = {
        producto_id: producto.id,
        producto_nombre: producto.nombre,
        cantidad: cantidad,
        precio_unitario: producto.precio_unitario,
        iva_porcentaje: producto.iva_porcentaje,
        tipo: producto.tipo,
        subtotal: subtotal
    };
    
    itemsFactura.push(item);
    renderizarItems();
    calcularTotalesFactura();
    
    // Limpiar campos
    $('#producto_temp').val('');
    $('#cantidad_temp').val(1);
    $('#precio_temp').val('');
}

