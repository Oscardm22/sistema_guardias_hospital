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
    <!-- Favicon -->
    <link rel="icon" href="../../assets/images/favicon.ico" type="image/x-icon">
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/styles_personal.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>

    <div class="container mt-4 flex-grow-1">
        <h2 class="mb-4"><?php echo htmlspecialchars($titulo); ?></h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <form action="proceso_guardar_personal.php" method="post" class="needs-validation" novalidate>
            <input type="hidden" name="id" value="<?php echo $miembro['id_personal']; ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nombre" class="form-label">Nombre:</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" 
                           value="<?php echo htmlspecialchars($miembro['nombre']); ?>" required>
                    <div class="invalid-feedback">Por favor ingrese el nombre</div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="apellido" class="form-label">Apellido:</label>
                    <input type="text" class="form-control" id="apellido" name="apellido" 
                           value="<?php echo htmlspecialchars($miembro['apellido']); ?>" required>
                    <div class="invalid-feedback">Por favor ingrese el apellido</div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="grado" class="form-label">Grado/Rango:</label>
                <input type="text" class="form-control" id="grado" name="grado" 
                       value="<?php echo htmlspecialchars($miembro['grado']); ?>" required>
                <div class="invalid-feedback">Por favor ingrese el grado/rango</div>
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="estado" name="estado" 
                       <?php echo $miembro['estado'] ? 'checked' : ''; ?>>
                <label class="form-check-label" for="estado">Activo</label>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Cambios
            </button>
            <a href="listar_personal.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </form>
    </div>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
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
                form.classList.add('was-validated')
            }, false)
        })
    })()
    </script>
</body>
</html>