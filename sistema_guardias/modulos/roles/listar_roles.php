<?php
require_once "../../includes/conexion.php";
require_once "../../includes/auth.php";
require_once "../../includes/funciones.php";

if (!es_admin()) {
    header("Location: listar_guardias.php?error=no_permiso");
    exit;
}

// Obtener todos los roles
$roles = [];
$sql = "SELECT id_rol, nombre_rol FROM roles_guardia ORDER BY nombre_rol";
$result = $conn->query($sql);
if ($result) {
    $roles = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Roles de Guardia</title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body class="bg-light">
<?php include "../../includes/navbar.php"; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4><i class="bi bi-people-fill me-2"></i> Roles de Guardia</h4>
                        <a href="editar_rol.php" class="btn btn-light btn-sm">
                            <i class="bi bi-plus-circle"></i> Nuevo Rol
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success">
                            <?php
                            switch ($_GET['success']) {
                                case 'creado': echo "Rol creado exitosamente"; break;
                                case 'actualizado': echo "Rol actualizado exitosamente"; break;
                                case 'eliminado': echo "Rol eliminado exitosamente"; break;
                            }
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger">
                            <?php
                            switch ($_GET['error']) {
                                case 'rol_en_uso': echo "No se puede eliminar el rol porque está en uso"; break;
                                default: echo "Ocurrió un error"; break;
                            }
                            ?>
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre del Rol</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($roles as $rol): ?>
                                <tr>
                                    <td><?= htmlspecialchars($rol['id_rol']) ?></td>
                                    <td><?= htmlspecialchars($rol['nombre_rol']) ?></td>
                                    <td>
                                        <a href="editar_rol.php?id=<?= $rol['id_rol'] ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil"></i> Editar
                                        </a>
                                        <button onclick="confirmarEliminar(<?= $rol['id_rol'] ?>)" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i> Eliminar
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar eliminación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Estás seguro que deseas eliminar este rol? Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="deleteLink" class="btn btn-danger">Eliminar</a>
            </div>
        </div>
    </div>
</div>

<script>
function confirmarEliminar(id) {
    document.getElementById('deleteLink').href = 'eliminar_rol.php?id=' + id;
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    modal.show();
}
</script>
</body>
</html>