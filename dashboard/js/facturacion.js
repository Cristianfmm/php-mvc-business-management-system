
let itemsFactura = [];
$(document).ready(function(){
    
    // Determinar qué vista cargar según la página actual
    const currentPage = window.location.pathname.split('/').pop();
    
    if (currentPage === 'index.html' || currentPage === '') {
        // Cargar vista de Login
        $(".auth-body").load("views/body.html?v=1.0", function(){
             initLoginPage(); 
        });
       /*  $(".auth-body").load("views/crearfactura.html?v=1.0", function(){
            cargarClientes();
            cargarProductos();
            $('#fecha_emision').val(new Date().toISOString().split('T')[0]);
            
            // Actualizar precio cuando cambia el producto
            $('#producto_temp').on('change', function() {
                const productoId = $(this).val();
                if (productoId) {
                    const producto = productosGlobales.find(p => p.id == productoId);
                    $('#precio_temp').val(producto.precio_unitario);
                }
            });
            
            // Actualizar descuento
            $('#descuento').on('input', function() {
                calcularTotalesFactura();
            });
       });  */
    } else if (currentPage === 'index2.html') {
        // Cargar vista de Registro
        $(".auth-body").load("views/auth.html?v=1.0", function(){
            /* initRegisterPage(); */
        });
    }


    
    // ==================== LOGOUT (PARA DASHBOARD) ====================
    
    $(document).on('click', '#logoutBtn', function(e) {
        e.preventDefault();
        
        showConfirm('¿Cerrar sesión?', '¿Estás seguro de que quieres salir?')
            .then((result) => {
                if (result.isConfirmed) {
                    showLoading('Cerrando sesión...');
                    
                    logoutUsuario()
                        .done(function(response) {
                            hideLoading();
                            window.location.href = '../index.html';
                        })
                        .fail(function() {
                            hideLoading();
                            showError('Error al cerrar sesión');
                        });
                }
            });
    });

    let productosGlobales = [];
    
   
    $(document).on('click', '#btn-agregar', function(e) {
        e.preventDefault();
        
        const data = {
            cliente_id: $('#cliente_id').val(),
            fecha_emision: $('#fecha_emision').val(),
            fecha_vencimiento: $('#fecha_vencimiento').val(),
            metodo_pago: $('#metodo_pago').val(),
            estado: $('#estado').val(),
            prefijo: $('#prefijo').val(),
            descuento: parseFloat($('#descuento').val()) || 0,
            observaciones: $('#observaciones').val(),
            items: itemsFactura
        };
        
        if (!validarFormularioFactura(data)) {
            return;
        }
        
        showLoading('Creando factura...');
        
        crearFactura(data)
            .done(function(response) {
                hideLoading();
                
                if (response.code === 200) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Factura Creada!',
                        html: `
                            <p>Factura <strong>${response.response.numero_factura}</strong></p>
                            <p>Total: <strong>${formatoMoneda(response.response.total)}</strong></p>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Ver PDF',
                        cancelButtonText: 'Nueva Factura'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            generarPDFFactura(response.response.id);
                        } else {
                            location.reload();
                        }
                    });
                } else {
                    showError(response.response);
                }
            })
            .fail(function() {
                hideLoading();
                showError('Error de conexión');
            });
    });
    
});

