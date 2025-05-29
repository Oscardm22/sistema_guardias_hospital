<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/funciones/funciones_personal.php';
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/../../includes/funciones/funciones_autenticacion.php';

if (!es_admin()) {
    header('HTTP/1.0 403 Forbidden');
    exit('Acceso restringido a administradores');
}

// Configuración de paginación
$registrosPorPagina = 10;
$paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginaActual - 1) * $registrosPorPagina;

try {
    // Obtener el total de registros
    $totalRegistros = PersonalFunciones::contarPersonal($conn);
    
    // Calcular total de páginas
    $totalPaginas = ceil($totalRegistros / $registrosPorPagina);
    
    // Obtener los registros de la página actual
    $personal = PersonalFunciones::listarPersonal($conn, $registrosPorPagina, $offset);
} catch (Exception $e) {
    error_log("Error en listar_personal.php: " . $e->getMessage());
    $_SESSION['error'] = 'Error al cargar la lista de personal';
    $personal = [];
    $totalRegistros = 0;
    $totalPaginas = 1;
}

$titulo = 'Gestión de Personal';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titulo); ?> - Sistema Hospitalario</title>
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
        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            margin: 0 2px;
        }
        .badge-active {
            background-color: #198754;
        }
        .badge-inactive {
            background-color: #6c757d;
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
        .grado-badge {
            background-color: #6f42c1;
            color: white;
        }
        .pagination .page-item.active .page-link {
    background-color: #6f42c1;
    border-color: #2c3e50;
    color: white;
}
.pagination .page-link {
    color: #2c3e50;
}
    </style>
</head>
<body class="bg-light">
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>

    <!-- Modal de Confirmación de Eliminación -->
    <div class="modal fade" id="confirmarEliminacionModal" tabindex="-1" aria-labelledby="confirmarEliminacionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="confirmarEliminacionModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro que desea eliminar el siguiente miembro del personal?</p>
                    <div class="alert alert-warning">
                        <p class="mb-1"><strong>Nombre:</strong> <span id="personalNombre"></span></p>
                        <p class="mb-1"><strong>Grado:</strong> <span id="personalGrado"></span></p>
                        <p class="mb-1"><strong>Estado:</strong> <span id="personalEstado"></span></p>
                    </div>
                    <p class="text-danger"><small>Esta acción no se puede deshacer.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <form id="formEliminarPersonal" method="POST" action="proceso_eliminar_personal.php">
                        <input type="hidden" name="id" id="personalId">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash-alt me-1"></i> Confirmar Eliminación
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-4">
        <!-- Encabezado con botón -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0 text-primary">
                <i class="fas fa-users me-2"></i><?php echo htmlspecialchars($titulo); ?>
            </h2>
            <a href="crear_personal.php" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i> Nuevo Personal
            </a>
        </div>

        <!-- Mensajes de retroalimentación -->
        <?php if (isset($_SESSION['exito'])): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($_SESSION['exito']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['exito']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo htmlspecialchars($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Tarjeta contenedora -->
        <div class="card card-shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i> Listado de Personal</h5>
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-header">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Grado</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($personal)): ?>
                                <?php foreach ($personal as $miembro): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($miembro['id_personal']); ?></td>
                                    <td><?php echo htmlspecialchars($miembro['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($miembro['apellido']); ?></td>
                                    <td>
                                        <span class="badge grado-badge"><?php echo htmlspecialchars($miembro['grado']); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $miembro['estado'] ? 'badge-active' : 'badge-inactive'; ?>">
                                            <?php echo $miembro['estado'] ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end">
                                            <a href="editar_personal.php?id=<?php echo $miembro['id_personal']; ?>" 
                                               class="btn btn-action btn-warning me-2"
                                               title="Editar personal">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-action btn-danger btn-eliminar-personal"
                                                    title="Eliminar personal"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#confirmarEliminacionModal"
                                                    data-personal-id="<?php echo $miembro['id_personal']; ?>"
                                                    data-personal-nombre="<?php echo htmlspecialchars($miembro['nombre'] . ' ' . htmlspecialchars($miembro['apellido'])); ?>"
                                                    data-personal-grado="<?php echo htmlspecialchars($miembro['grado']); ?>"
                                                    data-personal-estado="<?php echo $miembro['estado'] ? 'Activo' : 'Inactivo'; ?>">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="empty-state">
                                        <i class="fas fa-user-slash"></i>
                                        <h5 class="mt-2">No hay personal registrado</h5>
                                        <p class="mb-0">Comienza agregando nuevo personal</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pie de tabla con paginación -->
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">Mostrando <?php echo count($personal); ?> de <?php echo $totalRegistros; ?> registros</small>
                    
                    <?php if ($totalPaginas > 1): ?>
                    <nav aria-label="Paginación de personal">
                        <ul class="pagination pagination-sm mb-0">
                            <!-- Botón Anterior -->
                            <li class="page-item <?php echo $paginaActual <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $paginaActual - 1; ?>" aria-label="Anterior">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            
                            <!-- Números de página -->
                            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                <li class="page-item <?php echo $i == $paginaActual ? 'active' : ''; ?>">
                                    <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <!-- Botón Siguiente -->
                            <li class="page-item <?php echo $paginaActual >= $totalPaginas ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $paginaActual + 1; ?>" aria-label="Siguiente">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Configurar el modal con los datos del personal a eliminar
        document.querySelectorAll('.btn-eliminar-personal').forEach(btn => {
            btn.addEventListener('click', function() {
                const personalId = this.getAttribute('data-personal-id');
                const personalNombre = this.getAttribute('data-personal-nombre');
                const personalGrado = this.getAttribute('data-personal-grado');
                const personalEstado = this.getAttribute('data-personal-estado');
                
                document.getElementById('personalId').value = personalId;
                document.getElementById('personalNombre').textContent = personalNombre;
                document.getElementById('personalGrado').textContent = personalGrado;
                document.getElementById('personalEstado').textContent = personalEstado;
            });
        });

        // Mejorar la experiencia del modal al enviar el formulario
        document.getElementById('formEliminarPersonal').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Eliminando...';
        });
    </script>
</body>
</html>