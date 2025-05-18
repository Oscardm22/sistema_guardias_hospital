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

if (isset($_SESSION['usuario_id']) && $_GET['id'] == $_SESSION['usuario_id']) {
    $_SESSION['error'] = 'No puedes eliminar tu propio usuario';
    header('Location: listar_usuarios.php');
    exit;
}

$usuario = UsuarioFunciones::obtenerUsuario($conn, $_GET['id']);

if (!$usuario) {
    header('Location: listar_usuarios.php');
    exit;
}

$titulo = 'Confirmar Eliminación';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titulo); ?> - Sistema Hospitalario</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/styles_usuarios.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>

    <div class="container mt-4 flex-grow-1">
        <div class="card shadow">
            <div class="card-header bg-danger text-white">
                <h3 class="card-title">Confirmar Eliminación</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <h4><i class="fas fa-exclamation-triangle"></i> ¿Está seguro de eliminar este usuario?</h4>
                    <p>Usuario: <strong><?php echo htmlspecialchars($usuario['usuario']); ?></strong></p>
                    <p>Rol: <strong><?php echo $usuario['rol'] === 'admin' ? 'Administrador' : 'Personal'; ?></strong></p>
                    <p class="text-danger">Esta acción no se puede deshacer.</p>
                </div>
                
                <form action="proceso_eliminar_usuario.php" method="post">
                    <input type="hidden" name="id" value="<?php echo $usuario['id_usuario']; ?>">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Confirmar Eliminación
                    </button>
                    <a href="listar_usuarios.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </form>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>