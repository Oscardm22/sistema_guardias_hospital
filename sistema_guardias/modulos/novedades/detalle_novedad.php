<?php
require_once __DIR__.'/../../includes/conexion.php';
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/funciones/funciones_novedades.php';
require_once __DIR__.'/../../includes/funciones/funciones_ui.php';
require_once __DIR__.'/../../includes/funciones/funciones_autenticacion.php';

// Función auxiliar para manejar mensajes de sesión
function obtener_mensaje_sesion($key) {
    if (!isset($_SESSION[$key])) {
        return '';
    }
    
    $mensaje = $_SESSION[$key];
    unset($_SESSION[$key]);
    
    if (is_array($mensaje)) {
        return $mensaje['mensaje'] ?? $mensaje['titulo'] ?? '';
    }
    
    return $mensaje;
}

// Obtener mensajes de sesión
$mensaje_exito = obtener_mensaje_sesion('exito');
$mensaje_error = obtener_mensaje_sesion('error');

// Validar ID de novedad
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No se especificó la novedad a visualizar";
    header('Location: listar_novedades.php');
    exit;
}

$id_novedad = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$novedad = obtener_novedad($conn, $id_novedad);

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
        <!-- Mensajes de éxito -->
        <?php if (!empty($mensaje_exito)): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4">
                <i class="fas fa-check-circle me-2"></i>
                <?= htmlspecialchars($mensaje_exito) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- Mensajes de error -->
        <?php if (!empty($mensaje_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= htmlspecialchars($mensaje_error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <h2 class="mb-4 text-primary">Detalle de Novedad</h2>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5>Información General</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Fecha de Registro:</strong> <?= formatear_fecha($novedad['fecha_registro'] ?? '', 'd/m/Y H:i') ?></p>
                        <p><strong>Área:</strong> <?= formatear_area_novedad($novedad['area'] ?? '') ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Guardia Relacionada:</strong> <?= formatear_fecha($novedad['fecha_guardia'] ?? '', 'd/m/Y') ?></p>
                        <p><strong>Reportado por:</strong> <?= htmlspecialchars(
                            ($novedad['grado'] ?? '') . ' ' . 
                            ($novedad['nombre_personal'] ?? '') . ' ' . 
                            ($novedad['apellido_personal'] ?? '')
                        ) ?></p>
                    </div>
                </div>

                <hr>

                <h5>Descripción</h5>
                <div class="p-3 bg-light rounded">
                    <?= nl2br(htmlspecialchars($novedad['descripcion'] ?? '')) ?>
                </div>
            </div>
            <div class="card-footer text-right">
                <a href="listar_novedades.php" class="btn btn-secondary">Volver al Listado</a>
                <?php if (puede_editar_novedad($id_novedad, $conn)): ?>
                    <a href="editar_novedad.php?id=<?= $id_novedad ?>" class="btn btn-primary">Editar</a>
                <?php endif; ?>
                <?php if (puede_eliminar_novedad($novedad['id_novedad'], obtener_id_personal_usuario(), $conn)): ?>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmarEliminarModal">
                        <i class="fas fa-trash-alt me-1"></i> Eliminar
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Modal de Confirmación -->
        <div class="modal fade" id="confirmarEliminarModal" tabindex="-1" aria-labelledby="confirmarEliminarModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="confirmarEliminarModalLabel">
                            <i class="fas fa-exclamation-triangle me-2"></i>Confirmar Eliminación
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>¿Está seguro que desea eliminar permanentemente esta novedad?</p>
                        <div class="alert alert-warning">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-circle fs-4 me-3"></i>
                                <div>
                                    <strong>Esta acción no puede deshacerse</strong>
                                    <p class="mb-0">Todos los datos relacionados se perderán permanentemente</p>
                                </div>
                            </div>
                        </div>
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Detalles de la Novedad</h6>
                                <ul class="list-unstyled mb-0">
                                    <li><strong>ID:</strong> <?= $novedad['id_novedad'] ?? '' ?></li>
                                    <li><strong>Área:</strong> <?= htmlspecialchars($novedad['area'] ?? '') ?></li>
                                    <li><strong>Fecha:</strong> <?= formatear_fecha($novedad['fecha_registro'] ?? '', 'd/m/Y H:i') ?></li>
                                    <li><strong>Reportado por:</strong> <?= htmlspecialchars(
                                        ($novedad['grado'] ?? '') . ' ' . 
                                        ($novedad['nombre_personal'] ?? '') . ' ' . 
                                        ($novedad['apellido_personal'] ?? '')
                                    ) ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </button>
                        <a href="eliminar_novedad.php?id=<?= $id_novedad ?>" class="btn btn-danger">
                            <i class="fas fa-trash-alt me-1"></i> Confirmar Eliminación
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <?php include __DIR__.'/../../includes/footer.php'; ?>
</body>
</html>