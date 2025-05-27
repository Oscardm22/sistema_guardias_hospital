<?php
require_once "../../includes/conexion.php";
require_once "../../includes/auth.php";
require_once "../../includes/funciones/funciones_ordenes.php";
require_once "../../includes/funciones/funciones_autenticacion.php";

// Configuración de filtros
$filtros = [];
if (!empty($_GET['desde'])) {
    $filtros['desde'] = $_GET['desde'];
}

if (!empty($_GET['hasta'])) {
    $filtros['hasta'] = $_GET['hasta'];
}

// Obtener órdenes con filtros
$ordenes = obtenerOrdenes($filtros);

// Manejo de mensajes
// En listar_ordenes.php
$mensaje = '';
$clase_mensaje = '';

// Manejo de mensajes de sesión (solo se muestran una vez)
if (isset($_SESSION['exito_ordenes'])) {
    $mensaje = $_SESSION['exito_ordenes'];
    $clase_mensaje = 'success';
    unset($_SESSION['exito_ordenes']); // Eliminar inmediatamente después de mostrar
} elseif (isset($_SESSION['error_ordenes'])) {
    $mensaje = $_SESSION['error_ordenes'];
    $clase_mensaje = 'danger';
    unset($_SESSION['error_ordenes']);
}

// Mensajes por GET (para compatibilidad)
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'orden_eliminada':
            $mensaje = 'Orden eliminada correctamente';
            $clase_mensaje = 'success';
            break;
        case 'orden_creada':
            $mensaje = 'Orden creada exitosamente';
            $clase_mensaje = 'success';
            break;
        case 'orden_actualizada':
            $mensaje = 'Orden actualizada correctamente';
            $clase_mensaje = 'success';
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Órdenes de Salida - Sistema de Guardias</title>
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
        .badge-vehiculo {
            background-color: #3498db;
            font-size: 0.9em;
        }
        .fecha-hora {
            display: flex;
            flex-direction: column;
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
    <?php include "../../includes/navbar.php"; ?>

    <div class="container py-4">
        <!-- Mensajes de retroalimentación -->
        <?php if ($mensaje): ?>
            <div class="alert alert-<?= $clase_mensaje ?> alert-dismissible fade show" role="alert">
                <?= $mensaje ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <script>
                // Limpiar los parámetros de la URL después de mostrarse
                if (window.history.replaceState) {
                    const url = new URL(window.location);
                    url.searchParams.delete('success');
                    url.searchParams.delete('error');
                    window.history.replaceState(null, '', url.pathname);
                }
            </script>
        <?php endif; ?>

        
        <!-- Encabezado con botón -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0 text-primary">
                <i class="fas fa-car me-2"></i>Órdenes de Salida
            </h2>
            <?php if (es_admin()): ?>
                <a href="crear_orden.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-2"></i> Nueva Orden
                </a>
            <?php endif; ?>
        </div>

        <!-- Tarjeta contenedora -->
        <div class="card card-shadow">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i> Registro de Órdenes</h5>
                    <div class="d-flex">
                        <form method="get" class="row g-2 align-items-center">
                            <div class="col-auto">
                                <input type="date" id="desde" name="desde" class="form-control form-control-sm" 
                                       value="<?= htmlspecialchars($filtros['desde'] ?? '') ?>" placeholder="Desde">
                            </div>
                            <div class="col-auto">
                                <input type="date" id="hasta" name="hasta" class="form-control form-control-sm" 
                                       value="<?= htmlspecialchars($filtros['hasta'] ?? '') ?>" placeholder="Hasta">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-light">
                                    <i class="fas fa-filter me-1"></i> Filtrar
                                </button>
                            </div>
                            <div class="col-auto">
                                <a href="listar_ordenes.php" class="btn btn-sm btn-outline-light">
                                    <i class="fas fa-undo me-1"></i> Limpiar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-header">
                            <tr>
                                <th>ID</th>
                                <th>Vehículo</th>
                                <th>Personal</th>
                                <th>Destino</th>
                                <th>Salida</th>
                                <th>Retorno</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($ordenes->num_rows > 0): ?>
                                <?php while ($orden = $ordenes->fetch_assoc()): ?>
                                <tr class="hover-shadow">
                                    <td><?= $orden['id_orden'] ?></td>
                                    <td>
                                        <span class="badge badge-vehiculo">
                                            <?= htmlspecialchars($orden['placa']) ?>
                                        </span>
                                        <small class="text-muted d-block"><?= htmlspecialchars($orden['marca']) ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($orden['grado']) ?></div>
                                        <?= htmlspecialchars($orden['nombre'] . ' ' . $orden['apellido']) ?>
                                    </td>
                                    <td><?= htmlspecialchars($orden['destino']) ?></td>
                                    <td>
                                        <div class="fecha-hora">
                                            <span class="fecha"><?= date('d/m/Y', strtotime($orden['fecha_salida'])) ?></span>
                                            <span class="hora"><?= date('H:i', strtotime($orden['fecha_salida'])) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($orden['fecha_retorno']): ?>
                                            <div class="fecha-hora">
                                                <span class="fecha"><?= date('d/m/Y', strtotime($orden['fecha_retorno'])) ?></span>
                                                <span class="hora"><?= date('H:i', strtotime($orden['fecha_retorno'])) ?></span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic">Pendiente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end">
                                            <a href="detalle_orden.php?id=<?= $orden['id_orden'] ?>" 
                                               class="btn btn-sm btn-info me-2"
                                               title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (es_admin()): ?>
                                            <a href="editar_orden.php?id=<?= $orden['id_orden'] ?>" 
                                               class="btn btn-sm btn-warning me-2"
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-danger" 
                                                    title="Eliminar"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#confirmDeleteModal"
                                                    data-orden-id="<?= $orden['id_orden'] ?>"
                                                    data-orden-destino="<?= htmlspecialchars($orden['destino']) ?>">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="empty-state">
                                        <i class="fas fa-car-side"></i>
                                        <h5 class="mt-2">No hay órdenes de salida registradas</h5>
                                        <p class="mb-0">Comienza creando una nueva orden</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pie de tabla opcional -->
            <div class="card-footer bg-light">
                <small class="text-muted">Total de órdenes: <?= $ordenes->num_rows ?></small>
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
        // Configurar el modal de eliminación
document.addEventListener('DOMContentLoaded', function() {
    const deleteModal = document.getElementById('confirmDeleteModal');
    const deleteButton = document.getElementById('deleteConfirmButton');
    const modalBody = document.getElementById('modalDeleteBody');
    
    deleteModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const ordenId = button.getAttribute('data-orden-id');
        const ordenDestino = button.getAttribute('data-orden-destino');
        
        // Actualizar el contenido del modal
        modalBody.innerHTML = 
            `¿Está seguro de eliminar la orden con destino a <strong>${ordenDestino}</strong>? Esta acción no se puede deshacer.`;
        
        // Configurar el enlace de eliminación CORREGIDO
        deleteButton.href = `eliminar_orden.php?id=${ordenId}`;
    });

            // Animación para las filas de la tabla
            const filas = document.querySelectorAll('tbody tr');
            filas.forEach((fila, index) => {
                setTimeout(() => {
                    fila.style.opacity = '1';
                    fila.style.transform = 'translateY(0)';
                }, index * 50);
            });
        });
    </script>
</body>
</html>