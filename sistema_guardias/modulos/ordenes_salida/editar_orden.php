<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/funciones/funciones_ordenes.php';
require_once __DIR__ . '/../../includes/funciones/funciones_personal.php';
require_once __DIR__ . '/../../includes/funciones/funciones_vehiculos.php';
require_once __DIR__ . '/../../includes/funciones/funciones_autenticacion.php';

if (!es_admin()) {
    header('Location: /index.php');
    exit;
}

// Verificar que se reciba el ID de la orden
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: listar_ordenes.php');
    exit;
}

$id_orden = (int)$_GET['id'];

// Obtener la orden a editar
$orden = obtenerOrdenPorId($id_orden);

if (!$orden) {
    $_SESSION['error'] = "Orden no encontrada";
    header('Location: listar_ordenes.php');
    exit;
}

// Obtener datos para los select
$personal = PersonalFunciones::obtenerPersonalActivo($conn);
$vehiculos = obtenerVehiculosOperativos($conn);

// Manejo de mensajes de error (si los hay)
$error = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'fechas_invalidas':
            $error = 'La fecha de retorno no puede ser anterior a la de salida';
            break;
        case 'campos_requeridos':
            $error = 'Todos los campos obligatorios deben ser completados';
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Orden #<?= $orden['id_orden'] ?> - Sistema de Guardias</title>
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
        .form-section {
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #eee;
        }
        .form-section:last-child {
            border-bottom: none;
        }
        .form-section-title {
            color: #2c3e50;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        .required-field::after {
            content: " *";
            color: #dc3545;
        }
        .combustible-info {
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }
        .combustible-lleno { color: #198754; }
        .combustible-3-4 { color: #28a745; }
        .combustible-medio { color: #ffc107; }
        .combustible-1-4 { color: #fd7e14; }
        .combustible-reserva { color: #dc3545; }
        .combustible-vacio { color: #6c757d; }
    </style>
</head>
<body class="bg-light">
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0 text-primary">
                <i class="fas fa-car me-2"></i>Editar Orden
            </h2>
            <a href="listar_ordenes.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Volver al listado
            </a>
        </div>

        <!-- Tarjeta del formulario -->
        <div class="card card-shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i> Formulario de Edición</h5>
            </div>
            
            <div class="card-body">
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-4">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <form action="procesar_edicion_orden.php" method="post" id="formOrden">
                    <input type="hidden" name="id_orden" value="<?= $orden['id_orden'] ?>">
                    
                    <!-- Sección Vehículo y Personal -->
                    <div class="form-section">
                        <h6 class="form-section-title"><i class="fas fa-car me-2"></i>Datos del Vehículo y Personal</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="vehiculo" class="form-label required-field">Vehículo</label>
                                    <select class="form-select" id="vehiculo" name="id_vehiculo" required>
                                        <option value="">Seleccionar vehículo</option>
                                        <?php foreach ($vehiculos as $v): ?>
                                        <option value="<?= $v['id_vehiculo'] ?>" 
                                            data-combustible="<?= $v['combustible'] ?>"
                                            data-tipo="<?= $v['tipo'] ?>"
                                            <?= $v['id_vehiculo'] == $orden['id_vehiculo'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($v['marca'] . ' - ' . $v['placa'] . ' (' . ucfirst($v['tipo']) . ')') ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="combustible-info" id="combustibleInfo">
                                        <?php if ($orden['id_vehiculo'] && isset($orden['combustible'])): ?>
                                            <span class="fw-bold"><?= $orden['tipo'] == 'ambulancia' ? 'Ambulancia' : 'Vehículo administrativo' ?></span> | 
                                            Combustible: <span class="combustible-<?= str_replace('/', '-', strtolower($orden['combustible'])) ?>">
                                                <?= strtoupper($orden['combustible']) ?>
                                            </span>
                                        <?php else: ?>
                                            <small class="text-muted">Seleccione un vehículo para ver el nivel de combustible</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="personal" class="form-label required-field">Personal</label>
                                    <select class="form-select" id="personal" name="id_personal" required>
                                        <option value="">Seleccionar personal</option>
                                        <?php foreach ($personal as $p): ?>
                                        <option value="<?= $p['id_personal'] ?>" <?= $p['id_personal'] == $orden['id_personal'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p['grado'] . ' ' . $p['nombre'] . ' ' . $p['apellido']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección Destino y Motivo -->
                    <div class="form-section">
                        <h6 class="form-section-title"><i class="fas fa-map-marker-alt me-2"></i>Detalles del Viaje</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="destino" class="form-label required-field">Destino</label>
                                    <input type="text" class="form-control" id="destino" name="destino" 
                                           value="<?= htmlspecialchars($orden['destino']) ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="motivo" class="form-label required-field">Motivo</label>
                                    <input type="text" class="form-control" id="motivo" name="motivo" 
                                           value="<?= htmlspecialchars($orden['motivo']) ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección Fechas -->
                    <div class="form-section">
                        <h6 class="form-section-title"><i class="far fa-clock me-2"></i>Horarios</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_salida" class="form-label required-field">Fecha/Hora de Salida</label>
                                    <input type="datetime-local" class="form-control" id="fecha_salida" 
                                           name="fecha_salida" value="<?= date('Y-m-d\TH:i', strtotime($orden['fecha_salida'])) ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_retorno" class="form-label">Fecha/Hora de Retorno Estimado</label>
                                    <input type="datetime-local" class="form-control" id="fecha_retorno" 
                                           name="fecha_retorno" value="<?= $orden['fecha_retorno'] ? date('Y-m-d\TH:i', strtotime($orden['fecha_retorno'])) : '' ?>">
                                    <small class="text-muted">(Opcional)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botones de acción -->
                    <div class="d-flex justify-content-end mt-4">
                        <a href="detalle_orden.php?id=<?= $orden['id_orden'] ?>" class="btn btn-outline-secondary me-3">
                            <i class="fas fa-times me-2"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mostrar información de combustible al seleccionar vehículo
            document.getElementById('vehiculo').addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const combustible = selectedOption.getAttribute('data-combustible');
                const tipo = selectedOption.getAttribute('data-tipo');
                const infoElement = document.getElementById('combustibleInfo');
                
                if (combustible && tipo) {
                    const combustibleClass = combustible.replace('/', '-').toLowerCase();
                    const tipoText = tipo === 'ambulancia' ? 'Ambulancia' : 'Vehículo administrativo';
                    
                    infoElement.innerHTML = `
                        <span class="fw-bold">${tipoText}</span> | 
                        Combustible: <span class="combustible-${combustibleClass}">
                            ${combustible.toUpperCase()}
                        </span>
                    `;
                } else {
                    infoElement.innerHTML = '<small class="text-muted">Seleccione un vehículo para ver detalles</small>';
                }
            });

            // Validación de fechas en el cliente
            document.getElementById('formOrden').addEventListener('submit', function(e) {
                const fechaSalida = document.getElementById('fecha_salida').value;
                const fechaRetorno = document.getElementById('fecha_retorno').value;
                
                if (fechaRetorno && new Date(fechaRetorno) < new Date(fechaSalida)) {
                    e.preventDefault();
                    alert('La fecha de retorno no puede ser anterior a la de salida');
                    return false;
                }
                
                return true;
            });
        });
    </script>
</body>
</html>