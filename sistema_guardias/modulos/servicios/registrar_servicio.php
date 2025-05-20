<?php
require_once "../../includes/conexion.php";
require_once "../../includes/auth.php";
require_once "../../includes/funciones/funciones_autenticacion.php";

if (!es_admin()) {
    header("Location: listar_servicios.php?error=permisos");
    exit;
}

$titulo_pagina = "Registrar Nuevo Servicio";

// Manejo de errores
$error = isset($_GET['error']) ? $_GET['error'] : null;
$mensajes_error = [
    'datos_invalidos' => 'Los datos proporcionados no son válidos',
    'tipo_invalido' => 'Tipo de servicio no válido'
];
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
</head>
<body class="bg-light">
    <?php include "../../includes/navbar.php"; ?>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-plus-circle me-2"></i> <?= htmlspecialchars($titulo_pagina) ?></h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($mensajes_error[$error] ?? 'Error desconocido') ?>
                            </div>
                        <?php endif; ?>

                        <form action="proceso_registrar_servicio.php" method="post">
                            <div class="mb-3">
                                <label for="tipo" class="form-label">Tipo de Servicio *</label>
                                <select class="form-select" id="tipo" name="tipo" required>
                                    <option value="">Seleccione...</option>
                                    <option value="agua">Agua</option>
                                    <option value="electricidad">Electricidad</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="medida" class="form-label">Medida *</label>
                                <input type="number" step="0.01" class="form-control" id="medida" name="medida" required>
                            </div>

                            <div class="mb-3">
                                <label for="unidad" class="form-label">Unidad de Medida *</label>
                                <select class="form-select" id="unidad" name="unidad" required>
                                    <option value="porcentaje">Porcentaje</option>
                                    <option value="litros">Litros</option>
                                    <option value="kWh">Kilovatios-hora (kWh)</option>
                                </select>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-primary me-md-2">
                                    <i class="bi bi-save me-2"></i> Guardar
                                </button>
                                <a href="listar_servicios.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-2"></i> Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "../../includes/footer.php"; ?>
    
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>