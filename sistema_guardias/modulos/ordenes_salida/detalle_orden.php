<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/funciones/funciones_ordenes.php';
require_once __DIR__ . '/../../includes/funciones/funciones_autenticacion.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: listar_ordenes.php');
    exit;
}

$orden = obtenerOrdenPorId((int)$_GET['id']);

if (!$orden) {
    $_SESSION['error'] = "Orden no encontrada";
    header('Location: listar_ordenes.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Orden #<?= $orden['id_orden'] ?> - Sistema de Guardias</title>
    <link rel="icon" href="../../assets/images/favicon.ico" type="image/x-icon">
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .card-shadow {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border: none;
            border-radius: 0.5rem;
        }
        .info-card {
            border-left: 4px solid #0d6efd;
            margin-bottom: 1.5rem;
        }
        .info-card .card-body {
            padding: 1.25rem;
        }
        .info-card h5 {
            color: #2c3e50;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 0.5rem;
        }
        .info-item {
            margin-bottom: 0.75rem;
        }
        .info-item:last-child {
            margin-bottom: 0;
        }
        .info-label {
            font-weight: 600;
            color: #495057;
            display: inline-block;
            min-width: 120px;
        }
        .fecha-hora {
            display: inline-block;
        }
        .fecha-hora .fecha {
            font-weight: 500;
        }
        .fecha-hora .hora {
            font-size: 0.85em;
            color: #6c757d;
        }
    </style>
</head>
<body class="bg-light">
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0 text-primary">
                <i class="fas fa-file-alt me-2"></i>Detalle de Orden
            </h2>
        </div>

        <!-- Tarjeta principal -->
        <div class="card card-shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Información General</h5>
            </div>
            
            <div class="card-body">
                <div class="row">
                    <!-- Columna izquierda -->
                    <div class="col-md-6">
                        <!-- Tarjeta de vehículo -->
                        <div class="card info-card">
                            <div class="card-body">
                                <h5><i class="fas fa-car me-2"></i>Vehículo Asignado</h5>
                                
                                <div class="info-item">
                                    <span class="info-label">Placa:</span>
                                    <span class="badge bg-primary"><?= htmlspecialchars($orden['placa']) ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">Marca:</span>
                                    <?= htmlspecialchars($orden['marca']) ?>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">Tipo:</span>
                                    <span class="badge <?= $orden['tipo'] == 'ambulancia' ? 'bg-danger' : 'bg-secondary' ?>">
                                        <?= ucfirst(htmlspecialchars($orden['tipo'])) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tarjeta de detalles del viaje -->
                        <div class="card info-card">
                            <div class="card-body">
                                <h5><i class="fas fa-route me-2"></i>Detalles del Viaje</h5>
                                
                                <div class="info-item">
                                    <span class="info-label">Destino:</span>
                                    <?= htmlspecialchars($orden['destino']) ?>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">Motivo:</span>
                                    <?= htmlspecialchars($orden['motivo']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Columna derecha -->
                    <div class="col-md-6">
                        <!-- Tarjeta de personal -->
                        <div class="card info-card">
                            <div class="card-body">
                                <h5><i class="fas fa-user-tie me-2"></i>Personal Asignado</h5>
                                
                                <div class="info-item">
                                    <span class="info-label">Nombre:</span>
                                    <?= htmlspecialchars($orden['grado'] . ' ' . $orden['nombre'] . ' ' . $orden['apellido']) ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tarjeta de horarios -->
                        <div class="card info-card">
                            <div class="card-body">
                                <h5><i class="far fa-clock me-2"></i>Horarios</h5>
                                
                                <div class="info-item">
                                    <span class="info-label">Salida:</span>
                                    <div class="fecha-hora">
                                        <span class="fecha"><?= date('d/m/Y', strtotime($orden['fecha_salida'])) ?></span>
                                        <span class="hora"><?= date('H:i', strtotime($orden['fecha_salida'])) ?></span>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">Retorno:</span>
                                    <?php if ($orden['fecha_retorno']): ?>
                                        <div class="fecha-hora">
                                            <span class="fecha"><?= date('d/m/Y', strtotime($orden['fecha_retorno'])) ?></span>
                                            <span class="hora"><?= date('H:i', strtotime($orden['fecha_retorno'])) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic">Pendiente</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Botones de acción -->
                <div class="d-flex justify-content-end mt-4">
                    <a href="listar_ordenes.php" class="btn btn-outline-primary me-3">
                        <i class="fas fa-arrow-left me-2"></i> Volver al listado
                    </a>
                    
                    <?php if (es_admin()): ?>
                    <a href="editar_orden.php?id=<?= $orden['id_orden'] ?>" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i> Editar
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>