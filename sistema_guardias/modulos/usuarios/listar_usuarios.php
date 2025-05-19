<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/funciones/funciones_usuarios.php';
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/../../includes/funciones/funciones_autenticacion.php';

// Verificar permisos de administrador
if (!es_admin()) {
    header('HTTP/1.0 403 Forbidden');
    exit('Acceso restringido a administradores');
}

// Obtener lista de usuarios usando tu conexión actual
try {
    if (!isset($conn) || !($conn instanceof mysqli)) {
        throw new Exception("Conexión a la base de datos no válida");
    }

    $usuarios = UsuarioFunciones::listarUsuarios($conn);
} catch (Exception $e) {
    error_log("Error en listar_usuarios.php: " . $e->getMessage());
    $_SESSION['error'] = 'Error al cargar la lista de usuarios';
    $usuarios = [];
}

$titulo = 'Gestión de Usuarios';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titulo); ?> - Sistema Hospitalario</title>
    <!-- Favicon -->
    <link rel="icon" href="../../assets/images/favicon.ico" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="/assets/css/all.min.css" rel="stylesheet">
    <!-- Estilos personalizados -->
    <link href="../../assets/css/styles_usuarios.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>

    <div class="container mt-4 flex-grow-1">
        <h2 class="mb-4">Gestión de Usuarios</h2>
        
        <!-- Mostrar mensajes de éxito/error -->
        <?php if (isset($_SESSION['exito'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['exito']); ?></div>
            <?php unset($_SESSION['exito']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <a href="crear_usuario.php" class="btn btn-primary mb-3">
            <i class="fas fa-plus"></i> Nuevo Usuario
        </a>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="custom-thead">
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($usuarios)): ?>
                        <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario['id_usuario']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['usuario']); ?></td>
                            <td><?php echo $usuario['rol'] === 'admin' ? 'Administrador' : 'Personal'; ?></td>
                            <td>
                                <a href="editar_usuario.php?id=<?php echo $usuario['id_usuario']; ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <?php if ($usuario['id_usuario'] != ($_SESSION['usuario_id'] ?? null)): ?>
                                <a href="eliminar_usuario.php?id=<?php echo $usuario['id_usuario']; ?>" class="btn btn-sm btn-danger" 
                                   onclick="return confirm('¿Estás seguro de eliminar este usuario?');">
                                    <i class="fas fa-trash"></i> Eliminar
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No se encontraron usuarios</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>