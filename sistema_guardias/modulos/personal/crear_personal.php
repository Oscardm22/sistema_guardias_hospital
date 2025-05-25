<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/funciones/funciones_personal.php';
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/../../includes/funciones/funciones_autenticacion.php';

if (!es_admin()) {
    header('HTTP/1.0 403 Forbidden');
    exit('Acceso restringido a administradores');
}

$titulo = 'Registrar Nuevo Personal';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titulo); ?> - Sistema Hospitalario</title>
    <link rel="icon" href="../../assets/images/favicon.ico" type="image/x-icon">
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .card-shadow {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border: none;
            border-radius: 0.5rem;
        }
        .form-section {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .form-title {
            color: #2c3e50;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .btn-submit {
            padding: 0.5rem 1.5rem;
        }
        .custom-checkbox .custom-control-input:checked~.custom-control-label::before {
            background-color: #2c3e50;
        }
    </style>
</head>
<body class="bg-light">
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>

    <div class="container py-4">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0 text-primary">
                <i class="fas fa-user-plus me-2"></i><?php echo htmlspecialchars($titulo); ?>
            </h2>
            <a href="listar_personal.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Volver al listado
            </a>
        </div>

        <!-- Mensajes de retroalimentación -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo htmlspecialchars($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Tarjeta contenedora del formulario -->
        <div class="card card-shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-id-card me-2"></i> Información del Personal</h5>
            </div>
            
            <div class="card-body">
                <form action="proceso_guardar_personal.php" method="post" class="needs-validation" novalidate>
                    <!-- Sección de información básica -->
                    <div class="form-section">
                        <h5 class="form-title"><i class="fas fa-user me-2"></i>Datos Personales</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                    <div class="invalid-feedback">Por favor ingrese el nombre</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="apellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="apellido" name="apellido" required>
                                    <div class="invalid-feedback">Por favor ingrese el apellido</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección de información profesional -->
                    <div class="form-section">
                        <h5 class="form-title"><i class="fas fa-briefcase me-2"></i>Información Profesional</h5>
                        
                        <div class="mb-3">
                            <label for="grado" class="form-label">Grado/Rango <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-star"></i></span>
                                <input type="text" class="form-control" id="grado" name="grado" required>
                                <div class="invalid-feedback">Por favor ingrese el grado/rango</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="estado" name="estado" checked>
                                <label class="form-check-label" for="estado">Activo</label>
                            </div>
                            <small class="text-muted">Desactive este campo si el personal no está actualmente activo</small>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary btn-submit me-3">
                            <i class="fas fa-save me-2"></i> Guardar Personal
                        </button>
                        <a href="listar_personal.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script>
    // Validación del formulario
    (() => {
        'use strict'
        
        // Seleccionar todos los formularios con la clase needs-validation
        const forms = document.querySelectorAll('.needs-validation')
        
        // Validar cada campo al perder foco
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('blur', () => {
                input.classList.add('validate');
                if (!input.checkValidity()) {
                    input.classList.add('is-invalid');
                } else {
                    input.classList.remove('is-invalid');
                }
            });
        });
        
        // Validar al enviar el formulario
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    // Marcar todos los campos como validados
                    form.querySelectorAll('.form-control').forEach(input => {
                        input.classList.add('validate');
                        if (!input.checkValidity()) {
                            input.classList.add('is-invalid');
                        }
                    });
                }
                
                form.classList.add('was-validated');
            }, false);
        });
    })();

    // Mostrar feedback inmediato al usuario
    document.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('input', () => {
            if (input.classList.contains('validate')) {
                if (input.checkValidity()) {
                    input.classList.remove('is-invalid');
                    input.classList.add('is-valid');
                } else {
                    input.classList.remove('is-valid');
                    input.classList.add('is-invalid');
                }
            }
        });
    });
    </script>
</body>
</html>