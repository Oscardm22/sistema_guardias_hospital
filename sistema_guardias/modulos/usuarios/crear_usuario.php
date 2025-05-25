<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/funciones/funciones_usuarios.php';
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/../../includes/funciones/funciones_autenticacion.php';

if (!es_admin()) {
    header('HTTP/1.0 403 Forbidden');
    exit('Acceso restringido a administradores');
}

$titulo = 'Crear Nuevo Usuario';
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
        .card-form {
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: none;
        }
        .card-header-custom {
            background-color: #2c3e50;
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        .btn-save {
            background-color: #2c3e50;
            border-color: #2c3e50;
        }
        .btn-save:hover {
            background-color: #1a252f;
            border-color: #1a252f;
        }
        .form-label {
            font-weight: 500;
        }
        .password-requirements {
            font-size: 0.85rem;
            color: #6c757d;
        }
    </style>
</head>
<body class="bg-light">
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card card-form">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i><?php echo htmlspecialchars($titulo); ?></h4>
                    </div>
                    
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo htmlspecialchars($_SESSION['error']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>
                        
                        <form action="proceso_guardar_usuario.php" method="post" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label for="usuario" class="form-label">Nombre de Usuario</label>
                                <input type="text" class="form-control" id="usuario" name="usuario" required>
                                <div class="invalid-feedback">Por favor ingrese un nombre de usuario válido</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="rol" class="form-label">Rol del Usuario</label>
                                <select class="form-select" id="rol" name="rol" required>
                                    <option value="">Seleccione un rol</option>
                                    <option value="admin">Administrador</option>
                                    <option value="personal">Personal</option>
                                </select>
                                <div class="invalid-feedback">Por favor seleccione un rol</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="contrasena" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                                <div class="password-requirements mt-1">
                                    La contraseña debe tener al menos 8 caracteres
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="confirmar_contrasena" class="form-label">Confirmar Contraseña</label>
                                <input type="password" class="form-control" id="confirmar_contrasena" name="confirmar_contrasena" required>
                                <div class="invalid-feedback">Las contraseñas deben coincidir</div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <a href="listar_usuarios.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary text-white">
                                    <i class="fas fa-save me-2"></i> Crear Usuario
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script>
    // Validación del formulario
    (() => {
        'use strict'
        const forms = document.querySelectorAll('.needs-validation')
        
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                
                // Validar que las contraseñas coincidan
                const contrasena = document.getElementById('contrasena')
                const confirmar = document.getElementById('confirmar_contrasena')
                if (contrasena.value !== confirmar.value) {
                    confirmar.setCustomValidity('Las contraseñas no coinciden')
                    confirmar.reportValidity()
                    event.preventDefault()
                    event.stopPropagation()
                } else {
                    confirmar.setCustomValidity('')
                }
                
                // Validar longitud mínima de contraseña
                if (contrasena.value.length < 8) {
                    contrasena.setCustomValidity('La contraseña debe tener al menos 8 caracteres')
                    contrasena.reportValidity()
                    event.preventDefault()
                    event.stopPropagation()
                } else {
                    contrasena.setCustomValidity('')
                }
                
                form.classList.add('was-validated')
            }, false)
        })
    })()
    </script>
</body>
</html>