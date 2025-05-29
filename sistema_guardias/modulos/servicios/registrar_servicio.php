<?php
require_once "../../includes/conexion.php";
require_once "../../includes/auth.php";
require_once "../../includes/funciones/funciones_autenticacion.php";

$titulo_pagina = "Registrar Nuevo Servicio";

// Inicializar variables
$error = null;
$errores = [];

// Manejo de errores (modificado)
if (isset($_GET['error']) && is_string($_GET['error'])) {
    $error = $_GET['error'];
}

if (isset($_GET['errores']) && is_array($_GET['errores'])) {
    $errores = $_GET['errores'];
}

$mensajes_error = [
    'datos_invalidos' => 'Los datos proporcionados no son válidos',
    'tipo_invalido' => 'Tipo de servicio no válido',
    'medida_invalida' => 'La medida debe ser un número positivo',
    'porcentaje_invalido' => 'El porcentaje debe estar entre 0 y 100',
    'unidad_invalida' => 'Unidad de medida no válida',
    'responsable_invalido' => 'Responsable no válido',
    'responsable_requerido' => 'Debe seleccionar un responsable',
    'error_registro' => 'Error al registrar el servicio',
    'error_bd' => 'Error en la base de datos'
];

// Obtener personal para el select de responsables
$personal = [];
$result = $conn->query("SELECT id_personal, nombre, apellido FROM personal WHERE estado = 1 ORDER BY nombre");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $personal[] = $row;
    }
} else {
    error_log("Error al obtener personal: " . $conn->error);
}
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
        .required-field::after {
            content: " *";
            color: red;
        }
    </style>
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

                        <?php if (isset($_GET['success']) && $_GET['success'] == 'registro_exitoso'): ?>
                            <div class="alert alert-success">
                                Servicio registrado correctamente
                            </div>
                        <?php endif; ?>

                        <form action="proceso_registrar_servicio.php" method="post" id="formServicio">
    <!-- Sección de Agua -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="bi bi-droplet me-2"></i> Nivel de Agua</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="agua_medida" class="form-label required-field">Medida</label>
                <input type="number" step="0.01" class="form-control" id="agua_medida" name="agua[medida]" 
                       value="<?= htmlspecialchars($_POST['agua']['medida'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label for="agua_unidad" class="form-label required-field">Unidad de Medida</label>
                <select class="form-select" id="agua_unidad" name="agua[unidad]" required>
                    <option value="">Seleccione...</option>
                    <option value="litros" <?= isset($_POST['agua']['unidad']) && $_POST['agua']['unidad'] == 'litros' ? 'selected' : '' ?>>Litros</option>
                    <option value="porcentaje" <?= isset($_POST['agua']['unidad']) && $_POST['agua']['unidad'] == 'porcentaje' ? 'selected' : '' ?>>Porcentaje</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="agua_observaciones" class="form-label">Observaciones</label>
                <textarea class="form-control" id="agua_observaciones" name="agua[observaciones]" rows="2"><?= htmlspecialchars($_POST['agua']['observaciones'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Sección de Combustible -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="bi bi-fuel-pump me-2"></i> Combustible Generador</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="combustible_medida" class="form-label required-field">Medida</label>
                <input type="number" step="0.01" class="form-control" id="combustible_medida" name="combustible[medida]" 
                       value="<?= htmlspecialchars($_POST['combustible']['medida'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label for="combustible_unidad" class="form-label required-field">Unidad de Medida</label>
                <select class="form-select" id="combustible_unidad" name="combustible[unidad]" required>
                    <option value="">Seleccione...</option>
                    <option value="litros" <?= isset($_POST['combustible']['unidad']) && $_POST['combustible']['unidad'] == 'litros' ? 'selected' : '' ?>>Litros</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="combustible_observaciones" class="form-label">Observaciones</label>
                <textarea class="form-control" id="combustible_observaciones" name="combustible[observaciones]" rows="2"><?= htmlspecialchars($_POST['combustible']['observaciones'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Responsable común -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="bi bi-person-check me-2"></i> Información Común</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="responsable" class="form-label required-field">Responsable</label>
                <select class="form-select" id="responsable" name="responsable" required>
                    <option value="">Seleccione responsable...</option>
                    <?php foreach ($personal as $p): ?>
                        <option value="<?= htmlspecialchars($p['id_personal']) ?>" 
                            <?= (isset($_POST['responsable']) && $_POST['responsable']) == $p['id_personal'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
        <button type="submit" class="btn btn-primary me-md-2">
            <i class="bi bi-save me-2"></i> Guardar Ambos Servicios
        </button>
        <a href="listar_servicios.php" class="btn btn-secondary">
            <i class="bi bi-x-circle me-2"></i> Cancelar
        </a>
    </div>
</form>

<?php include "../../includes/footer.php"; ?>

<script src="../../assets/js/bootstrap.bundle.min.js"></script>
<script>
    // Validación cliente del porcentaje para agua
    document.getElementById('formServicio').addEventListener('submit', function(e) {
        const aguaUnidad = document.getElementById('agua_unidad').value;
        const aguaMedida = parseFloat(document.getElementById('agua_medida').value);
        
        if (aguaUnidad === 'porcentaje' && (aguaMedida < 0 || aguaMedida > 100)) {
            e.preventDefault();
            alert('El porcentaje de agua debe estar entre 0 y 100');
            document.getElementById('agua_medida').focus();
            return;
        }
        
        // Validación adicional para combustible si es necesario
        const combustibleMedida = parseFloat(document.getElementById('combustible_medida').value);
        if (combustibleMedida < 0) {
            e.preventDefault();
            alert('La medida de combustible no puede ser negativa');
            document.getElementById('combustible_medida').focus();
        }
    });
</script>
</body>
</html>