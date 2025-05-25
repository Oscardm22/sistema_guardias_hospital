<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/funciones/funciones_usuarios.php';
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/../../includes/funciones/funciones_autenticacion.php';

// Verificar permisos de administrador
if (!es_admin()) {
    header('HTTP/1.0 403 Forbidden');
    exit('Acceso restringido a administradores');
}

// Obtener lista de usuarios usando tu conexión actual
try {
    if (!isset($conn) || !($conn instanceof mysqli)) {
        throw new Exception("Conexión a la base de datos no válida");
    }

    $usuarios = UsuarioFunciones::listarUsuarios($conn);
} catch (Exception $e) {
    error_log("Error en listar_usuarios.php: " . $e->getMessage());
    $_SESSION['error'] = 'Error al cargar la lista de usuarios';
    $usuarios = [];
}

$titulo = 'Gestión de Usuarios';
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
        .badge-admin {
            background-color: #6f42c1;
        }
        .badge-personal {
            background-color: #20c997;
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
        .modal-danger .modal-header {
            background-color: #dc3545;
            color: white;
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
                    <p>¿Está seguro que desea eliminar el siguiente usuario?</p>
                    <div class="alert alert-warning">
                        <p class="mb-1"><strong>Usuario:</strong> <span id="usuarioNombre"></span></p>
                        <p class="mb-1"><strong>Rol:</strong> <span id="usuarioRol"></span></p>
                    </div>
                    <p class="text-danger"><small>Esta acción no se puede deshacer.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <form id="formEliminarUsuario" method="POST" action="proceso_eliminar_usuario.php">
                        <input type="hidden" name="id" id="usuarioId">
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
                <i class="fas fa-users-cog me-2"></i><?php echo htmlspecialchars($titulo); ?>
            </h2>
            <a href="crear_usuario.php" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i> Nuevo Usuario
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
                <h5 class="mb-0"><i class="fas fa-list me-2"></i> Listado de Usuarios</h5>
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-header">
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($usuarios)): ?>
                                <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($usuario['id_usuario']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($usuario['usuario']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $usuario['rol'] === 'admin' ? 'badge-admin' : 'badge-personal'; ?>">
                                            <?php echo $usuario['rol'] === 'admin' ? 'Administrador' : 'Personal'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo ($usuario['activo'] ?? 1) ? 'badge-active' : 'badge-inactive'; ?>">
                                            <?php echo ($usuario['activo'] ?? 1) ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end">
                                            <a href="editar_usuario.php?id=<?php echo $usuario['id_usuario']; ?>" 
                                               class="btn btn-action btn-warning me-2"
                                               title="Editar usuario">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($usuario['id_usuario'] != ($_SESSION['usuario_id'] ?? null)): ?>
                                            <button class="btn btn-action btn-danger btn-eliminar-usuario"
                                                    title="Eliminar usuario"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#confirmarEliminacionModal"
                                                    data-usuario-id="<?php echo $usuario['id_usuario']; ?>"
                                                    data-usuario-nombre="<?php echo htmlspecialchars($usuario['usuario']); ?>"
                                                    data-usuario-rol="<?php echo $usuario['rol'] === 'admin' ? 'Administrador' : 'Personal'; ?>">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="empty-state">
                                        <i class="fas fa-user-slash"></i>
                                        <h5 class="mt-2">No hay usuarios registrados</h5>
                                        <p class="mb-0">Comienza agregando un nuevo usuario</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pie de tabla opcional -->
            <div class="card-footer bg-light">
                <small class="text-muted">Total de usuarios: <?php echo count($usuarios); ?></small>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Configurar el modal con los datos del usuario a eliminar
        document.querySelectorAll('.btn-eliminar-usuario').forEach(btn => {
            btn.addEventListener('click', function() {
                const usuarioId = this.getAttribute('data-usuario-id');
                const usuarioNombre = this.getAttribute('data-usuario-nombre');
                const usuarioRol = this.getAttribute('data-usuario-rol');
                
                document.getElementById('usuarioId').value = usuarioId;
                document.getElementById('usuarioNombre').textContent = usuarioNombre;
                document.getElementById('usuarioRol').textContent = usuarioRol;
            });
        });

        // Mejorar la experiencia del modal al enviar el formulario
        document.getElementById('formEliminarUsuario').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Eliminando...';
        });
    </script>
</body>
</html>