<?php
require_once "../../includes/conexion.php";
require_once "../../includes/auth.php";
require_once "../../includes/funciones/funciones_autenticacion.php";

if (!es_admin()) {
    header("Location: listar_servicios.php?error=permisos");
    exit;
}

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
                            <div class="mb-3">
                                <label for="tipo" class="form-label required-field">Tipo de Servicio</label>
                                <select class="form-select" id="tipo" name="tipo" required>
                                    <option value="">Seleccione...</option>
                                    <option value="agua" <?= isset($_POST['tipo']) && $_POST['tipo'] == 'agua' ? 'selected' : '' ?>>Nivel de Agua</option>
                                    <option value="combustible" <?= isset($_POST['tipo']) && $_POST['tipo'] == 'combustible' ? 'selected' : '' ?>>Combustible Generador</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="medida" class="form-label required-field">Medida</label>
                                <input type="number" step="0.01" class="form-control" id="medida" name="medida" 
                                       value="<?= htmlspecialchars($_POST['medida'] ?? '') ?>" required>
                                <small class="text-muted">Para agua: nivel en litros o porcentaje. Para combustible: cantidad en litros o galones.</small>
                            </div>

                            <div class="mb-3">
                                <label for="unidad" class="form-label required-field">Unidad de Medida</label>
                                <select class="form-select" id="unidad" name="unidad" required>
                                    <option value="">Seleccione...</option>
                                    <option value="litros" <?= isset($_POST['unidad']) && $_POST['unidad'] == 'litros' ? 'selected' : '' ?>>Litros</option>
                                    <option value="porcentaje" <?= isset($_POST['unidad']) && $_POST['unidad'] == 'porcentaje' ? 'selected' : '' ?>>Porcentaje</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="2"><?= htmlspecialchars($_POST['observaciones'] ?? '') ?></textarea>
                                <small class="text-muted">Ej: "Tanque llenado completamente", "Fuga detectada", etc.</small>
                            </div>

                            <div class="mb-3">
                                <label for="responsable" class="form-label required-field">Responsable</label>
                                <select class="form-select" id="responsable" name="responsable" required>
                                    <option value="">Seleccione responsable...</option>
                                    <?php foreach ($personal as $p): ?>
                                        <option value="<?= htmlspecialchars($p['id_personal']) ?>" 
                                            <?= (isset($servicio['responsable']) && $servicio['responsable'] == $p['id_personal']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?>
                                        </option>
                                    <?php endforeach; ?>
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
    <script>
        // Validación cliente del porcentaje
        document.getElementById('formServicio').addEventListener('submit', function(e) {
            const unidad = document.getElementById('unidad').value;
            const medida = parseFloat(document.getElementById('medida').value);
            
            if (unidad === 'porcentaje' && (medida < 0 || medida > 100)) {
                e.preventDefault();
                alert('El porcentaje debe estar entre 0 y 100');
                document.getElementById('medida').focus();
            }
        });

        // Cambiar opciones de unidad según tipo de servicio
        document.getElementById('tipo').addEventListener('change', function() {
            const tipo = this.value;
            const unidadSelect = document.getElementById('unidad');
            
            if (tipo === 'combustible') {
                // Para combustible, mostrar solo litros y galones
                Array.from(unidadSelect.options).forEach(option => {
                    option.style.display = (option.value === 'porcentaje') ? 'none' : '';
                });
            } else {
                // Para agua, mostrar todas las opciones
                Array.from(unidadSelect.options).forEach(option => {
                    option.style.display = '';
                });
            }
        });
    </script>
</body>
</html>