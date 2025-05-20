<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/funciones/funciones_personal.php';
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/../../includes/funciones/funciones_autenticacion.php';

if (!es_admin()) {
    header('HTTP/1.0 403 Forbidden');
    exit('Acceso restringido a administradores');
}

try {
    $personal = PersonalFunciones::listarPersonal($conn);
} catch (Exception $e) {
    error_log("Error en listar_personal.php: " . $e->getMessage());
    $_SESSION['error'] = 'Error al cargar la lista de personal';
    $personal = [];
}

$titulo = 'Gestión de Personal';
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
        
        <?php if (isset($_SESSION['exito'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['exito']); ?></div>
            <?php unset($_SESSION['exito']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <a href="crear_personal.php" class="btn btn-primary mb-3">
            <i class="fas fa-plus"></i> Nuevo Personal
        </a>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="custom-thead">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Grado</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($personal)): ?>
                        <?php foreach ($personal as $miembro): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($miembro['id_personal']); ?></td>
                            <td><?php echo htmlspecialchars($miembro['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($miembro['apellido']); ?></td>
                            <td><?php echo htmlspecialchars($miembro['grado']); ?></td>
                            <td>
                                <?php if ($miembro['estado']): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="editar_personal.php?id=<?php echo $miembro['id_personal']; ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <a href="eliminar_personal.php?id=<?php echo $miembro['id_personal']; ?>" class="btn btn-sm btn-danger" 
                                   onclick="return confirm('¿Estás seguro de eliminar este miembro del personal?');">
                                    <i class="fas fa-trash"></i> Eliminar
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No se encontró personal registrado</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>
    <!-- Bootstrap JS -->
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>