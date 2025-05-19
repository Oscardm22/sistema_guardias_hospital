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
    <!-- Favicon -->
    <link rel="icon" href="../../assets/images/favicon.ico" type="image/x-icon">
    <title><?php echo htmlspecialchars($titulo); ?> - Sistema Hospitalario</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/styles_usuarios.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>

    <div class="container mt-4 flex-grow-1">
        <h2 class="mb-4"><?php echo htmlspecialchars($titulo); ?></h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <form action="proceso_guardar_usuario.php" method="post" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="usuario" class="form-label">Nombre de Usuario:</label>
                <input type="text" class="form-control" id="usuario" name="usuario" required>
                <div class="invalid-feedback">Por favor ingrese un nombre de usuario</div>
            </div>
            
            <div class="mb-3">
                <label for="rol" class="form-label">Rol:</label>
                <select class="form-select" id="rol" name="rol" required>
                    <option value="">Seleccione un rol</option>
                    <option value="admin">Administrador</option>
                    <option value="personal">Personal</option>
                </select>
                <div class="invalid-feedback">Por favor seleccione un rol</div>
            </div>
            
            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña:</label>
                <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                <div class="invalid-feedback">Por favor ingrese una contraseña</div>
            </div>
            
            <div class="mb-3">
                <label for="confirmar_contrasena" class="form-label">Confirmar Contraseña:</label>
                <input type="password" class="form-control" id="confirmar_contrasena" name="confirmar_contrasena" required>
                <div class="invalid-feedback">Las contraseñas deben coincidir</div>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Usuario
            </button>
            <a href="listar_usuarios.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </form>
    </div>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>

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
                
                form.classList.add('was-validated')
            }, false)
        })
    })()
    </script>
</body>
</html>