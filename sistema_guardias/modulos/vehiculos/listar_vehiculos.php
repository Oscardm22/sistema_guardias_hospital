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

// --- MANEJO DE MENSAJES --- //
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
    <!-- Favicon -->
    <link rel="icon" href="../../assets/images/favicon.ico" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Estilos personalizados -->
    <link href="../../assets/css/styles_listar.css" rel="stylesheet">
    <style>
        .table-vehiculos {
            background-color: white;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .table-vehiculos th {
            background-color: #2c3e50;
            color: white;
            font-weight: 500;
        }
        .badge-placa {
            background-color: #3498db;
            font-size: 0.85em;
            letter-spacing: 0.5px;
        }
        .acciones-cell {
            white-space: nowrap;
        }
        .hover-shadow:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            transition: box-shadow 0.3s ease;
        }
    </style>
</head>
<body class="bg-light">
    <?php include "../../includes/navbar.php"; ?>

    <div class="container py-4">
        <?php if ($mensaje): ?>
            <div class="alert alert-<?= $clase_mensaje ?> alert-dismissible fade show">
                <?= $mensaje ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0 text-primary"><i class="bi bi-truck me-2"></i> Listado de Vehículos</h2>
            <?php if (es_admin()): ?>
                <a href="crear_vehiculo.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i> Nuevo Vehículo
                </a>
            <?php endif; ?>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-vehiculos table-hover mb-0">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Placa</th>
                                <th scope="col">Modelo</th>
                                <th scope="col" class="text-end acciones-cell">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($vehiculos) > 0): ?>
                                <?php foreach ($vehiculos as $vehiculo): ?>
                                <tr class="hover-shadow">
                                    <th scope="row"><?= htmlspecialchars($vehiculo['id_vehiculo']) ?></th>
                                    <td>
                                        <span class="badge badge-placa"><?= htmlspecialchars($vehiculo['placa']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($vehiculo['modelo']) ?></td>
                                    <td class="text-end acciones-cell">
                                        <a href="editar_vehiculo.php?id=<?= $vehiculo['id_vehiculo'] ?>" 
                                           class="btn btn-sm btn-outline-warning me-1"
                                           title="Editar">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <?php if (es_admin()): ?>
                                        <a href="listar_vehiculos.php?delete=<?= $vehiculo['id_vehiculo'] ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           title="Eliminar"
                                           onclick="return confirm('¿Está seguro de eliminar el vehículo con placa <?= htmlspecialchars(addslashes($vehiculo['placa'])) ?>?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <div class="alert alert-info mb-0">
                                            <i class="bi bi-info-circle me-2"></i>
                                            No hay vehículos registrados en el sistema
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

    <!-- Footer -->
    <?php include "../../includes/footer.php"; ?>

    <!-- Bootstrap Bundle with Popper -->
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    
    <!-- Scripts personalizados -->
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
        });
    </script>
</body>
</html>