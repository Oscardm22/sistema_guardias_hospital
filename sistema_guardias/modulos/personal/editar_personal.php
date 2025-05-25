<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/funciones/funciones_personal.php';
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/../../includes/funciones/funciones_autenticacion.php';

if (!es_admin()) {
    header('HTTP/1.0 403 Forbidden');
    exit('Acceso restringido a administradores');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: listar_personal.php');
    exit;
}

$miembro = PersonalFunciones::obtenerPersonal($conn, $_GET['id']);

if (!$miembro) {
    header('Location: listar_personal.php');
    exit;
}

$titulo = 'Editar Personal';
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
        .input-group-text {
            min-width: 45px;
            justify-content: center;
        }
        .badge-active {
            background-color: #198754;
        }
        .badge-inactive {
            background-color: #6c757d;
        }
    </style>
</head>
<body class="bg-light">
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>

    <div class="container py-4">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0 text-primary">
                <i class="fas fa-user-edit me-2"></i><?php echo htmlspecialchars($titulo); ?>
            </h2>
            <div>
                <a href="listar_personal.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Volver al listado
                </a>
            </div>
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
                <h5 class="mb-0">
                    <i class="fas fa-id-card me-2"></i>
                    Editando: <?php echo htmlspecialchars($miembro['nombre'] . ' ' . $miembro['apellido']); ?>
                </h5>
            </div>
            
            <div class="card-body">
                <form action="proceso_guardar_personal.php" method="post" class="needs-validation" novalidate>
                    <input type="hidden" name="id" value="<?php echo $miembro['id_personal']; ?>">
                    
                    <!-- Sección de información básica -->
                    <div class="form-section">
                        <h5 class="form-title"><i class="fas fa-user me-2"></i>Datos Personales</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                           value="<?php echo htmlspecialchars($miembro['nombre']); ?>" required>
                                    <div class="invalid-feedback">Por favor ingrese el nombre</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="apellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="apellido" name="apellido" 
                                           value="<?php echo htmlspecialchars($miembro['apellido']); ?>" required>
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
                                <input type="text" class="form-control" id="grado" name="grado" 
                                       value="<?php echo htmlspecialchars($miembro['grado']); ?>" required>
                                <div class="invalid-feedback">Por favor ingrese el grado/rango</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="estado" name="estado" 
                                       <?php echo $miembro['estado'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="estado">Activo</label>
                            </div>
                            <small class="text-muted">Desactive este campo si el personal no está actualmente activo</small>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary btn-submit me-3">
                            <i class="fas fa-save me-2"></i> Guardar Cambios
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
    // Validación del formulario mejorada
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
    })();
    </script>
</body>
</html>