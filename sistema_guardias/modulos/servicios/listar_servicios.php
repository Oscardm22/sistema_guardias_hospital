<?php
require_once "../../includes/conexion.php";
require_once "../../includes/auth.php";
require_once "../../includes/funciones/funciones_autenticacion.php";
require_once "../../includes/funciones/funciones_servicios.php";

$mensajes_exito = [
    'registro_exitoso' => 'Servicio registrado correctamente',
    'actualizacion_exitosa' => 'Servicio actualizado correctamente',
    'eliminacion_exitosa' => 'Servicio eliminado correctamente'
];

$mensajes_error = [
    'error_registro' => 'Error al registrar el servicio',
    'error_actualizacion' => 'Error al actualizar el servicio',
    'error_eliminacion' => 'Error al eliminar el servicio',
    'id_invalido' => 'ID de servicio no válido',
    'servicio_no_encontrado' => 'Servicio no encontrado',
    'permisos' => 'No tienes permisos para realizar esta acción'
];

$servicios = obtenerServicios($conn);

// Inicializamos $niveles con estructura vacía
$niveles = [
    'agua' => null,
    'combustible' => null
];

// Obtenemos los niveles actuales
try {
    $nivelesActuales = obtenerNivelesActuales($conn);
    if ($nivelesActuales) {
        $niveles = $nivelesActuales;
    }
} catch (Exception $e) {
    error_log("Error al obtener niveles: " . $e->getMessage());
}

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .card-nivel {
            transition: transform 0.3s;
            height: 100%;
        }
        .card-nivel:hover {
            transform: translateY(-5px);
        }
        .agua-bg {
            background-color: #e3f2fd;
        }
        .combustible-bg {
            background-color: #fff8e1;
        }
        .nivel-valor {
            font-size: 2.5rem;
            font-weight: bold;
        }
        .nivel-unidad {
            font-size: 1.2rem;
            color: #6c757d;
        }

    /* Estilos para los botones de acción */
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .btn-info {
        color: black;
        background-color: #17a2b8;
        border-color: #17a2b8;
    }
    
    .btn-info:hover {
        background-color: #138496;
        border-color: #117a8b;
    }
    
    .btn-warning {
        color: #212529;
        background-color: #ffc107;
        border-color: #ffc107;
    }
    
    .btn-warning:hover {
        background-color: #e0a800;
        border-color: #d39e00;
    }
    
    .btn-danger {
        color: white;
        background-color: #dc3545;
        border-color: #dc3545;
    }
    
    .btn-danger:hover {
        background-color: #c82333;
        border-color: #bd2130;
    }
    
    /* Estilos para el modal de confirmación */
    .modal-danger .modal-header {
        background-color: #dc3545;
        border-bottom: none;
    }
    
    .modal-danger .modal-title {
        font-weight: 500;
    }
    
    .modal-danger .btn-close-white {
        filter: invert(1);
    }
    
    .modal-danger .modal-footer .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
        padding: 0.375rem 1.5rem;
    }
    
    .modal-danger .modal-footer .btn-danger:hover {
        background-color: #bb2d3b;
        border-color: #b02a37;
    }

    .alert {
        margin-bottom: 1rem;
        border-radius: 0.25rem;
    }
    
    .alert-dismissible .btn-close {
        padding: 0.75rem;
    }
    
    /* Asegurar que los mensajes no se superpongan con el navbar */
    .container.mt-3 {
        margin-top: 1rem !important;
    }
    </style>
</head>
<body class="bg-light">
    <?php include "../../includes/navbar.php"; ?>

<!-- Mensajes de éxito/error -->
<div class="container mt-3">
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= htmlspecialchars($mensajes_exito[$_GET['success']] ?? 'Operación exitosa') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($mensajes_error[$_GET['error']] ?? 'Error desconocido') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
</div>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0 text-primary"><i class="bi bi-speedometer2 me-2"></i> <?= htmlspecialchars($titulo_pagina) ?></h2>
            <a href="registrar_servicio.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i> Nuevo Registro
            </a>
        </div>

        <!-- Niveles actuales -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card card-nivel agua-bg">
                    <div class="card-body text-center">
                        <h5 class="card-title">Nivel Actual de Agua</h5>
                        <?php if (!empty($niveles['agua'])): ?>
                            <div class="nivel-valor"><?= htmlspecialchars($niveles['agua']['medida']) ?></div>
                            <div class="nivel-unidad"><?= htmlspecialchars($niveles['agua']['unidad']) ?></div>
                            <small class="text-muted">Última actualización: <?= date('d/m/Y H:i', strtotime($niveles['agua']['fecha_registro'])) ?></small>
                        <?php else: ?>
                            <div class="alert alert-warning mb-0">No hay registros de agua</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="card card-nivel combustible-bg">
                    <div class="card-body text-center">
                        <h5 class="card-title">Nivel Actual de Combustible</h5>
                        <?php if (!empty($niveles['combustible'])): ?>
                            <div class="nivel-valor"><?= htmlspecialchars($niveles['combustible']['medida']) ?></div>
                            <div class="nivel-unidad"><?= htmlspecialchars($niveles['combustible']['unidad']) ?></div>
                            <small class="text-muted">Última actualización: <?= date('d/m/Y H:i', strtotime($niveles['combustible']['fecha_registro'])) ?></small>
                        <?php else: ?>
                            <div class="alert alert-warning mb-0">No hay registros de combustible</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficas de Niveles -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <strong>Historial Nivel de Agua</strong>
                    </div>
                    <div class="card-body">
                        <canvas id="graficaAgua"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <strong>Historial Nivel de Combustible</strong>
                    </div>
                    <div class="card-body">
                        <canvas id="graficaCombustible"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Listado de registros -->
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i> Historial de Registros</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Medida</th>
                                <th>Unidad</th>
                                <th>Responsable</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($servicios) > 0): ?>
                                <?php foreach ($servicios as $servicio): ?>
                                <tr>
                                    <td class="text-capitalize">
                                        <?= htmlspecialchars($servicio['tipo'] == 'agua' ? 'Nivel de Agua' : 'Combustible') ?>
                                        <?php if (!empty($servicio['observaciones'])): ?>
                                            <i class="bi bi-info-circle text-primary" title="<?= htmlspecialchars($servicio['observaciones']) ?>"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($servicio['medida']) ?></td>
                                    <td><?= htmlspecialchars($servicio['unidad']) ?></td>
                                    <td>
                                        <?= !empty($servicio['nombre_responsable']) ? 
                                            htmlspecialchars($servicio['nombre_responsable'].' '.$servicio['apellido']) : 
                                            'N/A' ?>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($servicio['fecha_registro'])) ?></td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <!-- Botón para ver observaciones (modal) -->
                                            <button type="button" class="btn btn-sm btn-info view-obs" 
                                                    data-obs="<?= htmlspecialchars($servicio['observaciones'] ?? 'Sin observaciones') ?>"
                                                    title="Ver observaciones">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if (es_admin()): ?>
                                            <a href="editar_servicio.php?id=<?= htmlspecialchars($servicio['id_servicio']) ?>" 
                                            class="btn btn-sm btn-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#confirmarEliminarModal<?= htmlspecialchars($servicio['id_servicio']) ?>" 
                                                    title="Eliminar">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Modal de confirmación para eliminar -->
                                        <div class="modal fade" id="confirmarEliminarModal<?= htmlspecialchars($servicio['id_servicio']) ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title">
                                                            <i class="fas fa-exclamation-triangle me-2"></i>Confirmar Eliminación
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>¿Está seguro que desea eliminar este registro de servicio?</p>
                                                        <div class="alert alert-warning">
                                                            <i class="fas fa-exclamation-circle me-2"></i>
                                                            Esta acción no se puede deshacer
                                                        </div>
                                                        <div class="card bg-light">
                                                            <div class="card-body">
                                                                <p class="mb-1"><strong>Tipo:</strong> <?= htmlspecialchars($servicio['tipo'] == 'agua' ? 'Nivel de Agua' : 'Combustible') ?></p>
                                                                <p class="mb-1"><strong>Medida:</strong> <?= htmlspecialchars($servicio['medida']) ?> <?= htmlspecialchars($servicio['unidad']) ?></p>
                                                                <p class="mb-0"><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($servicio['fecha_registro'])) ?></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                            <i class="fas fa-times me-1"></i> Cancelar
                                                        </button>
                                                        <a href="eliminar_servicio.php?id=<?= htmlspecialchars($servicio['id_servicio']) ?>" class="btn btn-danger">
                                                            <i class="fas fa-trash-alt me-1"></i> Eliminar
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
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
    <script>
        // Mostrar observaciones en modal
        document.querySelectorAll('.view-obs').forEach(btn => {
            btn.addEventListener('click', function() {
                const obs = this.getAttribute('data-obs');
                document.getElementById('obsText').textContent = obs;
                var modal = new bootstrap.Modal(document.getElementById('obsModal'));
                modal.show();
            });
        });
    </script>

<script>
    const datosAgua = <?= json_encode(obtenerDatosGraficos($conn, 'agua')) ?>;
    const datosCombustible = <?= json_encode(obtenerDatosGraficos($conn, 'combustible')) ?>;

    const labelsAgua = datosAgua.map(item => item.fecha);
    const medidasAgua = datosAgua.map(item => item.medida);

    const labelsCombustible = datosCombustible.map(item => item.fecha);
    const medidasCombustible = datosCombustible.map(item => item.medida);

    new Chart(document.getElementById('graficaAgua'), {
        type: 'line',
        data: {
            labels: labelsAgua,
            datasets: [{
                label: 'Nivel de Agua',
                data: medidasAgua,
                fill: false,
                borderColor: 'blue',
                backgroundColor: 'blue',
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    new Chart(document.getElementById('graficaCombustible'), {
        type: 'line',
        data: {
            labels: labelsCombustible,
            datasets: [{
                label: 'Nivel de Combustible',
                data: medidasCombustible,
                fill: false,
                borderColor: 'orange',
                backgroundColor: 'orange',
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>

    <!-- Modal para observaciones -->
    <div class="modal fade" id="obsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Observaciones</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="obsText"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>