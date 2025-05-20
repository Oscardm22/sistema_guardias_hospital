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
$vehiculo = obtenerVehiculoPorId($conexion, $id_vehiculo);

if (!$vehiculo) {
    header("Location: listar_vehiculos.php");
    exit;
}

$titulo_pagina = "Confirmar Eliminación";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo_pagina) ?> - Sistema de Guardias</title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include "../../includes/navbar.php"; ?>

    <div class="container py-4">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i> Confirmar Eliminación</h4>
            </div>
            <div class="card-body">
                <p>¿Está seguro que desea eliminar permanentemente este vehículo?</p>
                
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Placa: <?= htmlspecialchars($vehiculo['placa']) ?></h5>
                        <p class="card-text">Modelo: <?= htmlspecialchars($vehiculo['modelo']) ?></p>
                    </div>
                </div>
                
                <form action="proceso_eliminar_vehiculo.php" method="post">
                    <input type="hidden" name="id_vehiculo" value="<?= $vehiculo['id_vehiculo'] ?>">
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-danger me-2">
                            <i class="bi bi-trash me-2"></i>Confirmar Eliminación
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
</body>
</html>