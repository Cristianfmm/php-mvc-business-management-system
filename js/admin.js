$(document).ready(function(){
    
    // Determinar qué vista cargar según la página actual
    const currentPage = window.location.pathname.split('/').pop();
    
    if (currentPage === 'index.html' || currentPage === '') {
        // Cargar vista de Login
        $(".auth-body").load("views/login.html?v=1.0", function(){
            initLoginPage();
        });
    } else if (currentPage === 'index2.html') {
        // Cargar vista de Registro
        $(".auth-body").load("views/auth.html?v=1.0", function(){
            initRegisterPage();
        });
    }
    
    // ==================== FUNCIONES DE INICIALIZACIÓN ====================
    
    function initLoginPage() {
        // ==================== LOGIN ====================
        
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            
            const email = $('#loginEmail').val().trim();
            const password = $('#loginPassword').val();
            
            // Validaciones
            if (!email || !password) {
                showWarning('Por favor complete todos los campos');
                return;
            }
            
            if (!validarEmail(email)) {
                showWarning('Por favor ingrese un email válido');
                return;
            }
            
            // Mostrar loading
            /* showLoading('Iniciando sesión...'); */
            
            // Enviar datos
            loginUsuario({ email, password })
                .done(function(response) {
                    hideLoading();
                    
                    if (response.code === 200) {
                        showSuccess('¡Bienvenido!', 1500).then(() => {
                            // Redirigir al dashboard
                            window.location.href = 'dashboard';
                        });
                    } else {
                        showError(response.response || 'Error al iniciar sesión');
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    hideLoading();
                    showError('Error de conexión: ' + errorThrown);
                });
        });
        
        // ==================== RECUPERAR CONTRASEÑA ====================
        
        $('#forgotPasswordLink').on('click', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Recuperar Contraseña',
                input: 'email',
                inputLabel: 'Ingresa tu email',
                inputPlaceholder: 'tu@email.com',
                showCancelButton: true,
                confirmButtonText: 'Enviar',
                cancelButtonText: 'Cancelar',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Debes ingresar un email';
                    }
                    if (!validarEmail(value)) {
                        return 'Email no válido';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading('Enviando email...');
                    
                    recuperarPassword(result.value)
                        .done(function(response) {
                            hideLoading();
                            
                            if (response.code === 200) {
                                showSuccess('Se ha enviado un email con las instrucciones para recuperar tu contraseña');
                            } else {
                                showError(response.response || 'Error al enviar el email');
                            }
                        })
                        .fail(function() {
                            hideLoading();
                            showError('Error de conexión');
                        });
                }
            });
        });
        
        // ==================== NAVEGACIÓN ====================
        
        $('#registerLink').on('click', function(e) {
            e.preventDefault();
            window.location.href = 'index2.html';
        });
        
        // Inicializar validaciones
        initValidations();
        
        // Verificar sesión
        checkSession();
    }
    
    function initRegisterPage() {
        // ==================== REGISTRO ====================
        
        $('#registerForm').on('submit', function(e) {
            e.preventDefault();
            
            const nombre_completo = $('#registerName').val().trim();
            const email = $('#registerEmail').val().trim();
            const password = $('#registerPassword').val();
            
            // Validaciones
            if (!nombre_completo || !email || !password) {
                showWarning('Por favor complete todos los campos');
                return;
            }
            
            if (nombre_completo.length < 3) {
                showWarning('El nombre debe tener al menos 3 caracteres');
                return;
            }
            
            if (!validarEmail(email)) {
                showWarning('Por favor ingrese un email válido');
                return;
            }
            
            if (!validarPassword(password)) {
                showWarning('La contraseña debe tener al menos 6 caracteres');
                return;
            }
            
            // Mostrar loading
            showLoading('Registrando usuario...');
            
            // Enviar datos
            registrarUsuario({ nombre_completo, email, password })
                .done(function(response) {
                    hideLoading();
                    
                    if (response.code === 200) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Registro exitoso!',
                            text: 'Tu cuenta ha sido creada. Ahora puedes iniciar sesión',
                            confirmButtonText: 'Ir a Login'
                        }).then(() => {
                            window.location.href = 'index.html';
                        });
                    } else {
                        showError(response.response || 'Error al registrar usuario');
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    hideLoading();
                    showError('Error de conexión: ' + errorThrown);
                });
        });
        
        // ==================== NAVEGACIÓN ====================
        
        $('#loginBtn').on('click', function(e) {
            e.preventDefault();
            window.location.href = 'index.html';
        });
        
        // Inicializar validaciones
        initValidations();
        
        // Verificar sesión
        checkSession();
    }
    
    // ==================== VERIFICAR SESIÓN ====================
    
    function checkSession() {
        verificarSesion()
            .done(function(response) {
                if (response.code === 200) {
                    // Si hay sesión activa y estamos en login/registro, redirigir al dashboard
                    const currentPage = window.location.pathname.split('/').pop();
                    if (currentPage === 'index.html' || currentPage === 'index2.html' || currentPage === '') {
                        window.location.href = 'dashboard';
                    }
                }
            });
    }
    
    // ==================== VALIDACIONES EN TIEMPO REAL ====================
    
    function initValidations() {
        // Validación de email en tiempo real
        $(document).on('input', '#loginEmail, #registerEmail', function() {
            const $input = $(this);
            const email = $input.val().trim();
            const $container = $input.closest('.mb-3');
            
            // IMPORTANTE: Remover TODOS los elementos de validación del contenedor completo
            $container.find('.validation-icon').remove();
            $container.find('.auth-feedback').remove();
            
            if (email.length === 0) {
                $input.removeClass('is-valid is-invalid');
                return;
            }
            
            if (validarEmail(email)) {
                $input.removeClass('is-invalid').addClass('is-valid');
                
                // Verificar si es input sin icono
                /* if (!$input.hasClass('auth-input-with-icon')) {
                    $input.after('<span class="validation-icon valid">✓</span>');
                } else {
                    $input.parent().after('<span class="validation-icon valid" style="right: 20px;">✓</span>');
                } */
                
                $container.append('<div class="auth-feedback valid-feedback">Email válido</div>');
            } else {
                $input.removeClass('is-valid').addClass('is-invalid shake');
                
                // Verificar si es input sin icono
                /* if (!$input.hasClass('auth-input-with-icon')) {
                    $input.after('<span class="validation-icon invalid">✕</span>');
                } else {
                    $input.parent().after('<span class="validation-icon invalid" style="right: 20px;">✕</span>');
                } */
                
                $container.append('<div class="auth-feedback invalid-feedback">Por favor ingresa un email válido</div>');
                
                setTimeout(() => $input.removeClass('shake'), 500);
            }
        });
        
        // Validación de contraseña en tiempo real
        $(document).on('input', '#loginPassword, #registerPassword', function() {
            const $input = $(this);
            const password = $input.val();
            const $container = $input.closest('.mb-3, .mb-4');
            const isRegister = $input.attr('id') === 'registerPassword';
            
            // IMPORTANTE: Remover TODOS los elementos de validación del contenedor completo
            $container.find('.validation-icon').remove();
            $container.find('.auth-feedback').remove();
            
            if (password.length === 0) {
                $input.removeClass('is-valid is-invalid');
                return;
            }
            
            if (password.length >= 6) {
                $input.removeClass('is-invalid').addClass('is-valid');
                
                // Verificar si es input sin icono
                /* if (!$input.hasClass('auth-input-with-icon')) {
                    if ($container.find('.password-toggle-icon').length) {
                        $input.after('<span class="validation-icon valid" style="right: 50px;">✓</span>');
                    } else {
                        $input.after('<span class="validation-icon valid">✓</span>');
                    }
                } else {
                    $input.parent().after('<span class="validation-icon valid" style="right: 20px;">✓</span>');
                } */
                
                if (isRegister) {
                    $container.append('<div class="auth-feedback valid-feedback">Contraseña segura</div>');
                }
            } else {
                $input.removeClass('is-valid').addClass('is-invalid shake');
                
                // Verificar si es input sin icono
               /*  if (!$input.hasClass('auth-input-with-icon')) {
                    if ($container.find('.password-toggle-icon').length) {
                        $input.after('<span class="validation-icon invalid" style="right: 50px;">✕</span>');
                    } else {
                        $input.after('<span class="validation-icon invalid">✕</span>');
                    }
                } else {
                    $input.parent().after('<span class="validation-icon invalid" style="right: 20px;">✕</span>');
                } */
                
                $container.append('<div class="auth-feedback invalid-feedback">La contraseña debe tener al menos 6 caracteres</div>');
                
                setTimeout(() => $input.removeClass('shake'), 500);
            }
        });
        
        // Validación de nombre completo en tiempo real
        $(document).on('input', '#registerName', function() {
            const $input = $(this);
            const nombre = $input.val().trim();
            const $container = $input.closest('.mb-3');
            
            // IMPORTANTE: Remover TODOS los elementos de validación del contenedor completo
            $container.find('.validation-icon').remove();
            $container.find('.auth-feedback').remove();
            
            if (nombre.length === 0) {
                $input.removeClass('is-valid is-invalid');
                return;
            }
            
            if (nombre.length >= 3) {
                $input.removeClass('is-invalid').addClass('is-valid');
                /* $input.parent().after('<span class="validation-icon valid" style="right: 20px;">✓</span>'); */
                $container.append('<div class="auth-feedback valid-feedback">Nombre válido</div>');
            } else {
                $input.removeClass('is-valid').addClass('is-invalid shake');
                /* $input.parent().after('<span class="validation-icon invalid" style="right: 20px;">✕</span>'); */
                $container.append('<div class="auth-feedback invalid-feedback">El nombre debe tener al menos 3 caracteres</div>');
                
                setTimeout(() => $input.removeClass('shake'), 500);
            }
        });
        
        // Prevenir espacios al inicio del email
        $(document).on('keypress', '#loginEmail, #registerEmail', function(e) {
            if (e.which === 32 && this.value.length === 0) {
                e.preventDefault();
            }
        });
        
        // Limpiar validaciones al hacer submit
        $(document).on('submit', '#loginForm, #registerForm', function() {
            $(this).find('.auth-input').removeClass('is-valid is-invalid');
            $(this).find('.validation-icon').remove();
            $(this).find('.auth-feedback').remove();
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
                            window.location.href = 'index.html';
                        })
                        .fail(function() {
                            hideLoading();
                            showError('Error al cerrar sesión');
                        });
                }
            });
    });
    
});

