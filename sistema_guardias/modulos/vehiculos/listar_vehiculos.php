<?php
require_once "../../includes/conexion.php";
require_once "../../includes/auth.php";
require_once "../../includes/funciones/funciones_vehiculos.php";
require_once "../../includes/funciones/funciones_autenticacion.php";

// Eliminar vehículo (solo para admin)
if (isset($_GET['delete']) && es_admin()) {
    $id = intval($_GET['delete']);
    
    try {
        if(eliminarVehiculo($conn, $id)) {
            header('Location: listar_vehiculos.php?success=vehiculo_eliminado');
            exit;
        } else {
            header('Location: listar_vehiculos.php?error=eliminacion');
            exit;
        }
    } catch (Exception $e) {
        header('Location: listar_vehiculos.php?error=eliminacion');
        exit;
    }
}

// Manejo de mensajes
$mensaje = '';
$clase_mensaje = '';

if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'vehiculo_eliminado':
            $mensaje = 'Vehículo eliminado correctamente';
            $clase_mensaje = 'success';
            break;
        case 'vehiculo_creado':
            $mensaje = 'Vehículo creado exitosamente';
            $clase_mensaje = 'success';
            break;
        case 'vehiculo_actualizado':
            $mensaje = 'Vehículo actualizado correctamente';
            $clase_mensaje = 'success';
            break;
    }
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'no_permiso':
            $mensaje = 'No tienes permisos para esta acción';
            $clase_mensaje = 'danger';
            break;
        case 'eliminacion':
            $mensaje = 'Error al eliminar el vehículo (¿tiene órdenes de salida asociadas?)';
            $clase_mensaje = 'danger';
            break;
    }
}

// Obtener todos los vehículos
$vehiculos = obtener_Vehiculos($conn);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Vehículos - Sistema de Guardias</title>
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
        .table-header {
            background-color: #2c3e50;
            color: white;
        }
        .badge-placa {
            background-color: #3498db;
            font-size: 0.9em;
            letter-spacing: 0.5px;
        }
        .badge-ambulancia {
            background-color: #dc3545;
        }
        .badge-administrativo {
            background-color: #6c757d;
        }
        .badge-combustible {
            font-size: 0.85em;
            padding: 0.35em 0.65em;
        }
        .combustible-lleno { background-color: #198754; }
        .combustible-3-4 { background-color: #28a745; }
        .combustible-medio { background-color: #ffc107; color: #212529; }
        .combustible-1-4 { background-color: #fd7e14; }
        .combustible-reserva { background-color: #dc3545; }
        .combustible-vacio { background-color:rgb(2, 63, 71); }
        .empty-state {
            padding: 3rem;
            text-align: center;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #adb5bd;
        }
        .hover-shadow:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="bg-light">
    <?php include "../../includes/navbar.php"; ?>

    <div class="container py-4">
        <!-- Mensajes de retroalimentación -->
        <?php if ($mensaje): ?>
            <div class="alert alert-<?= $clase_mensaje ?> alert-dismissible fade show mb-4">
                <i class="fas <?= $clase_mensaje == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> me-2"></i>
                <?= $mensaje ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- Encabezado con botón -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0 text-primary">
                <i class="fas fa-car me-2"></i>Listado de Vehículos
            </h2>
            <?php if (es_admin()): ?>
                <a href="crear_vehiculo.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-2"></i> Nuevo Vehículo
                </a>
            <?php endif; ?>
        </div>

        <!-- Tarjeta contenedora -->
        <div class="card card-shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i> Registro de Vehículos</h5>
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-header">
                            <tr>
                                <th>ID</th>
                                <th>Placa</th>
                                <th>Marca</th>
                                <th>Tipo</th>
                                <th>Combustible</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($vehiculos) > 0): ?>
                                <?php foreach ($vehiculos as $vehiculo): ?>
                                <tr class="hover-shadow">
                                    <td><?= htmlspecialchars($vehiculo['id_vehiculo']) ?></td>
                                    <td>
                                        <span class="badge badge-placa"><?= htmlspecialchars($vehiculo['placa']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($vehiculo['marca']) ?></td>
                                    <td>
                                        <span class="badge <?= $vehiculo['tipo'] == 'ambulancia' ? 'badge-ambulancia' : 'badge-administrativo' ?>">
                                            <?= ucfirst($vehiculo['tipo']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $combustibleClass = str_replace('/', '-', strtolower($vehiculo['combustible']));
                                        ?>
                                        <span class="badge badge-combustible combustible-<?= $combustibleClass ?>">
                                            <?= htmlspecialchars($vehiculo['combustible']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $vehiculo['operativo'] ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= $vehiculo['operativo'] ? 'Operativo' : 'No Operativo' ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end">
                                            <a href="editar_vehiculo.php?id=<?= $vehiculo['id_vehiculo'] ?>" 
                                               class="btn btn-sm btn-warning me-2"
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if (es_admin()): ?>
                                            <button class="btn btn-sm btn-danger" 
                                                    title="Eliminar"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#confirmDeleteModal"
                                                    data-vehiculo-id="<?= $vehiculo['id_vehiculo'] ?>"
                                                    data-vehiculo-placa="<?= htmlspecialchars($vehiculo['placa']) ?>">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="empty-state">
                                        <i class="fas fa-car-crash"></i>
                                        <h5 class="mt-2">No hay vehículos registrados</h5>
                                        <p class="mb-0">Comienza agregando un nuevo vehículo</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pie de tabla opcional -->
            <div class="card-footer bg-light">
                <small class="text-muted">Total de vehículos: <?= count($vehiculos) ?></small>
            </div>
        </div>
    </div>

    <?php include "../../includes/footer.php"; ?>

    <!-- Modal de Confirmación de Eliminación -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalDeleteBody">
                    <!-- El contenido se llenará dinámicamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a id="deleteConfirmButton" href="#" class="btn btn-danger">
                        <i class="fas fa-trash-alt me-2"></i> Eliminar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animación al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            const filas = document.querySelectorAll('tbody tr');
            filas.forEach((fila, index) => {
                setTimeout(() => {
                    fila.style.opacity = '1';
                    fila.style.transform = 'translateY(0)';
                }, index * 50);
            });

            // Configurar el modal de eliminación
            const deleteModal = document.getElementById('confirmDeleteModal');
            const deleteButton = document.getElementById('deleteConfirmButton');
            const modalBody = document.getElementById('modalDeleteBody');
            
            deleteModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const vehiculoId = button.getAttribute('data-vehiculo-id');
                const vehiculoPlaca = button.getAttribute('data-vehiculo-placa');
                
                // Actualizar el contenido del modal
                modalBody.textContent = 
                    `¿Está seguro de eliminar el vehículo con placa ${vehiculoPlaca}? Esta acción no se puede deshacer.`;
                
                // Configurar el enlace de eliminación
                deleteButton.href = `listar_vehiculos.php?delete=${vehiculoId}`;
            });
        });
    </script>
</body>
</html>