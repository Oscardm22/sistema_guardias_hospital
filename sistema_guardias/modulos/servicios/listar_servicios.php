<?php
require_once "../../includes/conexion.php";
require_once "../../includes/auth.php";
require_once "../../includes/funciones/funciones_autenticacion.php";
require_once "../../includes/funciones/funciones_servicios.php";

if (!es_admin()) {
    header("Location: ../../index.php?error=permisos");
    exit;
}

$servicios = obtenerServicios($conn);
$estadisticas = obtenerEstadisticasServicios($conn);
$titulo_pagina = "Gestión de Servicios";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="icon" href="../../assets/images/favicon.ico" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo_pagina) ?></title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .card-servicio {
            transition: transform 0.3s;
        }
        .card-servicio:hover {
            transform: translateY(-5px);
        }
        .agua-bg {
            background-color: #e3f2fd;
        }
        .electricidad-bg {
            background-color: #fff8e1;
        }
    </style>
</head>
<body class="bg-light">
    <?php include "../../includes/navbar.php"; ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-speedometer2 me-2"></i> <?= htmlspecialchars($titulo_pagina) ?></h2>
            <a href="registrar_servicio.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i> Nuevo Registro
            </a>
        </div>

        <!-- Estadísticas -->
        <div class="row mb-4">
            <?php foreach ($estadisticas as $est): ?>
            <div class="col-md-4 mb-3">
                <div class="card card-servicio <?= strtolower($est['tipo']) ?>-bg h-100">
                    <div class="card-body">
                        <h5 class="card-title text-capitalize"><?= htmlspecialchars($est['tipo']) ?></h5>
                        <div class="d-flex justify-content-between">
                            <div>
                                <small class="text-muted">Promedio</small>
                                <h4><?= round($est['promedio'], 2) ?></h4>
                            </div>
                            <div>
                                <small class="text-muted">Máximo</small>
                                <h4><?= round($est['maximo'], 2) ?></h4>
                            </div>
                            <div>
                                <small class="text-muted">Mínimo</small>
                                <h4><?= round($est['minimo'], 2) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Listado de registros -->
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i> Registros Recientes</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Medida</th>
                                <th>Unidad</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($servicios) > 0): ?>
                                <?php foreach ($servicios as $servicio): ?>
                                <tr>
                                    <td class="text-capitalize"><?= htmlspecialchars($servicio['tipo']) ?></td>
                                    <td><?= htmlspecialchars($servicio['medida']) ?></td>
                                    <td><?= htmlspecialchars($servicio['unidad']) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($servicio['fecha_registro'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <div class="alert alert-info mb-0">
                                            <i class="bi bi-info-circle me-2"></i>
                                            No hay registros de servicios
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include "../../includes/footer.php"; ?>
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>

</body>
</html>