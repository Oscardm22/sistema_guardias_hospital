<?php
require_once "../../includes/conexion.php";
require_once "../../includes/auth.php";
require_once "../../includes/funciones/funciones_autenticacion.php";
require_once "../../includes/funciones/funciones_servicios.php";

if (!es_admin()) {
    header("Location: listar_servicios.php?error=permisos");
    exit;
}

// Obtener ID del servicio a editar
$id_servicio = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_servicio <= 0) {
    header("Location: listar_servicios.php?error=id_invalido");
    exit;
}

// Obtener datos del servicio
$servicio = obtenerServicioPorId($conn, $id_servicio);
if (!$servicio) {
    header("Location: listar_servicios.php?error=servicio_no_encontrado");
    exit;
}

// Obtener personal para el select de responsables
$personal = [];
try {
    $stmt = $conn->query("SELECT id_personal, nombre, apellido FROM personal WHERE estado = 1 ORDER BY nombre");
    $personal = $stmt->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    error_log("Error al obtener personal: " . $e->getMessage());
}

$titulo_pagina = "Editar Registro de Servicio";
$error = isset($_GET['error']) ? $_GET['error'] : null;
$errores = isset($_GET['errores']) && is_array($_GET['errores']) ? $_GET['errores'] : [];

$mensajes_error = [
    'tipo_invalido' => 'Tipo de servicio no válido',
    'medida_invalida' => 'La medida debe ser un número positivo',
    'porcentaje_invalido' => 'El porcentaje debe estar entre 0 y 100',
    'unidad_invalida' => 'Unidad de medida no válida',
    'responsable_invalido' => 'Responsable no válido',
    'error_actualizacion' => 'Error al actualizar el servicio',
    'error_bd' => 'Error en la base de datos',
    'responsable_requerido' => 'Debe seleccionar un responsable'
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .required-field::after {
            content: " *";
            color: red;
        }
        .card-header {
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-light">
    <?php include "../../includes/navbar.php"; ?>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Mostrar múltiples errores si existen -->
                <?php if (!empty($errores)): ?>
                    <div class="alert alert-danger mb-4">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php 
                        foreach ($errores as $error) {
                            echo htmlspecialchars($mensajes_error[$error] ?? 'Error desconocido') . '<br>';
                        }
                        ?>
                    </div>
                <?php elseif ($error): ?>
                    <div class="alert alert-danger mb-4">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= htmlspecialchars($mensajes_error[$error] ?? 'Error desconocido') ?>
                    </div>
                <?php endif; ?>

                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-edit me-2"></i> <?= htmlspecialchars($titulo_pagina) ?></h4>
                    </div>
                    <div class="card-body">
                        <form action="proceso_editar_servicio.php" method="post" id="formServicio">
                            <input type="hidden" name="id_servicio" value="<?= htmlspecialchars($servicio['id_servicio']) ?>">

                            <div class="card mb-4 shadow-sm">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="fas fa-tint me-2"></i> Tipo de Servicio</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="tipo" class="form-label required-field">Tipo de Servicio</label>
                                        <select class="form-select" id="tipo" name="tipo" required>
                                            <option value="">Seleccione...</option>
                                            <option value="agua" <?= $servicio['tipo'] == 'agua' ? 'selected' : '' ?>>Nivel de Agua</option>
                                            <option value="combustible" <?= $servicio['tipo'] == 'combustible' ? 'selected' : '' ?>>Combustible Generador</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-4 shadow-sm">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="fas fa-ruler me-2"></i> Medición</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="medida" class="form-label required-field">Medida</label>
                                        <input type="number" step="0.01" class="form-control" id="medida" name="medida" 
                                               value="<?= htmlspecialchars($servicio['medida']) ?>" required>
                                        <small class="text-muted">Para agua: nivel en litros o porcentaje. Para combustible: cantidad en litros.</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="unidad" class="form-label required-field">Unidad de Medida</label>
                                        <select class="form-select" id="unidad" name="unidad" required>
                                            <option value="">Seleccione...</option>
                                            <option value="litros" <?= $servicio['unidad'] == 'litros' ? 'selected' : '' ?>>Litros</option>
                                            <option value="porcentaje" <?= $servicio['unidad'] == 'porcentaje' ? 'selected' : '' ?>>Porcentaje</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-4 shadow-sm">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="fas fa-clipboard me-2"></i> Observaciones</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="observaciones" class="form-label">Observaciones</label>
                                        <textarea class="form-control" id="observaciones" name="observaciones" rows="3"><?= htmlspecialchars($servicio['observaciones']) ?></textarea>
                                        <small class="text-muted">Ej: "Tanque llenado completamente", "Fuga detectada", etc.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-4 shadow-sm">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="fas fa-user-tie me-2"></i> Responsable</h5>
                                </div>
                                <div class="card-body">
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
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                <button type="submit" class="btn btn-primary me-md-2">
                                    <i class="fas fa-save me-2"></i> Actualizar
                                </button>
                                <a href="listar_servicios.php" class="btn btn-secondary">
                                    <i class="fas fa-times-circle me-2"></i> Cancelar
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
        // Validación cliente
        document.getElementById('formServicio').addEventListener('submit', function(e) {
            const tipo = document.getElementById('tipo').value;
            const unidad = document.getElementById('unidad').value;
            const medida = parseFloat(document.getElementById('medida').value);
            
            // Validación para porcentaje (agua)
            if (unidad === 'porcentaje' && (medida < 0 || medida > 100)) {
                e.preventDefault();
                alert('El porcentaje debe estar entre 0 y 100');
                document.getElementById('medida').focus();
                return;
            }
            
            // Validación para combustible
            if (tipo === 'combustible' && medida < 0) {
                e.preventDefault();
                alert('La medida de combustible no puede ser negativa');
                document.getElementById('medida').focus();
                return;
            }
            
            // Validación para litros (ambos tipos)
            if (unidad === 'litros' && medida < 0) {
                e.preventDefault();
                alert('La medida no puede ser negativa');
                document.getElementById('medida').focus();
            }
        });

        // Cambiar opciones de unidad según tipo de servicio
        document.getElementById('tipo').addEventListener('change', function() {
            const tipo = this.value;
            const unidadSelect = document.getElementById('unidad');
            
            if (tipo === 'combustible') {
                // Para combustible, ocultar porcentaje
                Array.from(unidadSelect.options).forEach(option => {
                    if (option.value === 'porcentaje') {
                        option.style.display = 'none';
                        if (option.selected) {
                            unidadSelect.value = 'litros'; // Cambiar a litros si estaba seleccionado porcentaje
                        }
                    }
                });
            } else {
                // Para agua, mostrar todas las opciones
                Array.from(unidadSelect.options).forEach(option => {
                    option.style.display = '';
                });
            }
        });

        // Ejecutar al cargar para configurar unidades según tipo actual
        document.addEventListener('DOMContentLoaded', function() {
            const tipo = document.getElementById('tipo').value;
            if (tipo === 'combustible') {
                const unidadSelect = document.getElementById('unidad');
                Array.from(unidadSelect.options).forEach(option => {
                    if (option.value === 'porcentaje') {
                        option.style.display = 'none';
                        if (option.selected) {
                            unidadSelect.value = 'litros';
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>