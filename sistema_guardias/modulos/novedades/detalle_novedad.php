<?php
require_once __DIR__.'/../../includes/conexion.php';
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/funciones/funciones_novedades.php';
require_once __DIR__.'/../../includes/funciones/funciones_ui.php';
require_once __DIR__.'/../../includes/funciones/funciones_autenticacion.php';

if (!isset($_GET['id'])) {
    header('Location: listar_novedades.php');
    exit;
}

$id_novedad = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$novedad = obtener_novedad($id_novedad, $conn);

if (!$novedad) {
    $_SESSION['error'] = "Novedad no encontrada";
    header('Location: listar_novedades.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Detalle de Novedad</title>
    <!-- Favicon -->
    <link rel="icon" href="../../assets/images/favicon.ico" type="image/x-icon">
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="../../assets/css/styles_navbar.css" rel="stylesheet" />
    <link href="../../assets/css/styles_listar_novedades.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include __DIR__.'/../../includes/navbar.php'; ?>

    <div class="container mt-4">
        <h2 class="mb-4">Detalle de Novedad</h2>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5>Información General</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Fecha de Registro:</strong> <?= formatear_fecha($novedad['fecha_registro'], 'd/m/Y H:i') ?></p>
                        <p><strong>Área:</strong> <?= formatear_area_novedad($novedad['area']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Guardia Relacionada:</strong> <?= formatear_fecha($novedad['fecha_guardia'], 'd/m/Y') ?></p>
                        <p><strong>Reportado por:</strong> <?= htmlspecialchars($novedad['grado'] . ' ' . $novedad['nombre_personal'] . ' ' . $novedad['apellido']) ?></p>
                    </div>
                </div>

                <hr>

                <h5>Descripción</h5>
                <div class="p-3 bg-light rounded">
                    <?= nl2br(htmlspecialchars($novedad['descripcion'])) ?>
                </div>
            </div>
            <div class="card-footer text-right">
                <a href="listar_novedades.php" class="btn btn-secondary">Volver al Listado</a>
                <?php if (puede_editar_novedad($id_novedad, $conn)): ?>
                    <a href="editar_novedad.php?id=<?= $id_novedad ?>" class="btn btn-primary">Editar</a>
                <?php endif; ?>
                <?php if (puede_eliminar_novedad($novedad['id_novedad'], obtener_id_personal_usuario(), $conn)): ?>
                    <a href="eliminar_novedad.php?id=<?= $id_novedad ?>" class="btn btn-danger" onclick="return confirm('¿Eliminar esta novedad?')">Eliminar</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <?php include __DIR__.'/../../includes/footer.php'; ?>
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>