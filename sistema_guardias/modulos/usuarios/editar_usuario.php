<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/funciones/funciones_usuarios.php';
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/../../includes/funciones/funciones_autenticacion.php';

if (!es_admin()) {
    header('HTTP/1.0 403 Forbidden');
    exit('Acceso restringido a administradores');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: listar_usuarios.php');
    exit;
}

$usuario = UsuarioFunciones::obtenerUsuario($conn, $_GET['id']);

if (!$usuario) {
    header('Location: listar_usuarios.php');
    exit;
}

$titulo = 'Editar Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titulo); ?> - Sistema Hospitalario</title>
    <!-- Favicon -->
    <link rel="icon" href="../../assets/images/favicon.ico" type="image/x-icon">
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
            <input type="hidden" name="id" value="<?php echo $usuario['id_usuario']; ?>">
            
            <div class="mb-3">
                <label for="usuario" class="form-label">Nombre de Usuario:</label>
                <input type="text" class="form-control" id="usuario" name="usuario" 
                       value="<?php echo htmlspecialchars($usuario['usuario']); ?>" required>
                <div class="invalid-feedback">Por favor ingrese un nombre de usuario</div>
            </div>
            
            <div class="mb-3">
                <label for="rol" class="form-label">Rol:</label>
                <select class="form-select" id="rol" name="rol" required>
                    <option value="admin" <?php echo $usuario['rol'] === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                    <option value="personal" <?php echo $usuario['rol'] === 'personal' ? 'selected' : ''; ?>>Personal</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="contrasena" class="form-label">Nueva Contraseña (dejar en blanco para no cambiar):</label>
                <input type="password" class="form-control" id="contrasena" name="contrasena">
            </div>
            
            <div class="mb-3">
                <label for="confirmar_contrasena" class="form-label">Confirmar Nueva Contraseña:</label>
                <input type="password" class="form-control" id="confirmar_contrasena" name="confirmar_contrasena">
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Cambios
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