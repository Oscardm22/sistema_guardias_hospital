<?php
require_once "../../includes/conexion.php";
require_once "../../includes/auth.php";
require_once "../../includes/funciones/funciones_autenticacion.php";
require_once "../../includes/funciones/funciones_vehiculos.php";

if (!es_admin()) {
    $_SESSION['error'] = "No tienes permisos para esta acción";
    header("Location: listar_vehiculos.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: listar_vehiculos.php");
    exit;
}

$id_vehiculo = intval($_GET['id']);
$vehiculo = obtenerVehiculoPorId($conn, $id_vehiculo);

if (!$vehiculo) {
    header("Location: listar_vehiculos.php");
    exit;
}

$titulo_pagina = "Editar Vehículo";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Favicon -->
    <link rel="icon" href="../../assets/images/favicon.ico" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo_pagina) ?> - Sistema de Guardias</title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/styles_listar.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include "../../includes/navbar.php"; ?>

    <div class="container py-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i> Editar Vehículo</h4>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <form action="proceso_guardar_vehiculo.php" method="post">
                    <input type="hidden" name="id_vehiculo" value="<?= $vehiculo['id_vehiculo'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Placa Actual</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($vehiculo['placa']) ?>" readonly>
                        <small class="text-muted">La placa no puede modificarse</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="modelo" class="form-label">Modelo</label>
                        <input type="text" class="form-control" id="modelo" name="modelo" 
                               value="<?= htmlspecialchars($vehiculo['modelo']) ?>" required>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-save me-2"></i>Guardar Cambios
                        </button>
                        <a href="listar_vehiculos.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include "../../includes/footer.php"; ?>

    <!-- Bootstrap Bundle with Popper -->
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>