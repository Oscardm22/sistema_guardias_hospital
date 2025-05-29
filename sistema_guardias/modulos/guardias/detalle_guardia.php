<?php
require_once __DIR__.'/../../includes/conexion.php';
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/funciones/funciones_autenticacion.php';
require_once __DIR__.'/../../includes/funciones/funciones_personal.php';
require_once __DIR__.'/../../includes/funciones/funciones_guardias.php';

// Verificar permisos
if (!puede_ver_guardia()) {
    $_SESSION['error'] = "No tienes permisos para ver esta información";
    header('Location: listar_guardias.php');
    exit;
}

// Obtener ID de la guardia
$id_guardia = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_guardia <= 0) {
    $_SESSION['error'] = "Guardia no especificada";
    header('Location: listar_guardias.php');
    exit;
}

// Obtener información básica de la guardia (CONSULTA ACTUALIZADA)
$sql_guardia = "SELECT 
                g.id_guardia,
                g.fecha,
                DATE_FORMAT(g.fecha, '%d/%m/%Y') as fecha_formateada
            FROM guardias g
            WHERE g.id_guardia = ?";

$stmt = $conn->prepare($sql_guardia);
$stmt->bind_param("i", $id_guardia);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Guardia no encontrada";
    header('Location: listar_guardias.php');
    exit;
}

$guardia = $result->fetch_assoc();

// Obtener asignaciones de personal (esta parte se mantiene igual)
$sql_asignaciones = "SELECT 
                    a.id_asignacion,
                    p.id_personal,
                    p.nombre,
                    p.apellido,
                    p.grado,
                    r.nombre_rol,
                    a.turno
                  FROM asignaciones_guardia a
                  JOIN personal p ON a.id_personal = p.id_personal
                  JOIN roles_guardia r ON a.id_rol = r.id_rol
                  WHERE a.id_guardia = ?";

$stmt = $conn->prepare($sql_asignaciones);
$stmt->bind_param("i", $id_guardia);
$stmt->execute();
$asignaciones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Definir orden fijo de turnos
$orden_turnos = ['12h', '24h'];
$asignaciones_por_turno = array_fill_keys($orden_turnos, []);

// Organizar asignaciones por turno
foreach ($asignaciones as $asignacion) {
    $turno = $asignacion['turno'] ?? 'completo';
    if (in_array($turno, $orden_turnos)) {
        $asignaciones_por_turno[$turno][] = $asignacion;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="icon" href="../../assets/images/favicon.ico" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Guardia #<?= $guardia['id_guardia'] ?></title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .card-header {
            font-weight: bold;
        }
        .badge-turno {
            font-size: 0.9em;
            padding: 5px 10px;
            margin-right: 10px;
        }
        .badge-12h {
            background-color: #f6ffed;
            border: 1px solid #b7eb8f;
            color: #389e0d;
        }
        .badge-24h {
            background-color: #e6f7ff;
            border: 1px solid #91d5ff;
            color: #096dd9;
        }
        .modal-danger .modal-header {
            background-color: #dc3545;
            color: white;
        }
        .toast.show {
            animation: fadeIn 0.3s;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-light">
    <?php include __DIR__.'/../../includes/navbar.php'; ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0 text-primary">
                <i class="bi bi-calendar-event"></i> 
                Detalle de Guardia
            </h2>
            <a href="listar_guardias.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="card mb-4 shadow">
            <div class="card-header bg-primary text-white">
                Información General
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <p><strong>Fecha:</strong> <?= $guardia['fecha_formateada'] ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                Personal Asignado
            </div>
            <div class="card-body p-0">
                <?php foreach ($orden_turnos as $turno): ?>
                    <?php $miembros = $asignaciones_por_turno[$turno]; ?>
                    <?php if (!empty($miembros)): ?>
                        <div class="border-bottom p-3">
                            <h5>
                                <span class="badge badge-turno badge-<?= $turno ?>">
                                    <?= ucfirst($turno) ?>
                                </span>
                                <small class="text-muted"><?= count($miembros) ?> miembros</small>
                            </h5>
                            
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Grado</th>
                                            <th>Rol</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($miembros as $miembro): ?>
                                            <tr>
                                                <td>
                                                    <?= htmlspecialchars($miembro['nombre'].' '.$miembro['apellido']) ?>
                                                </td>
                                                <td><?= htmlspecialchars($miembro['grado']) ?></td>
                                                <td><?= htmlspecialchars($miembro['nombre_rol']) ?></td>
                                                <td>
                                                    <?php if (puede_editar_guardia()): ?>
                                                        <button class="btn btn-sm btn-outline-danger" 
                                                                onclick="eliminarAsignacion(<?= $miembro['id_asignacion'] ?>)">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (puede_ver_guardia()): ?>
            <div class="mt-4">
                <a href="generar_pdf.php?id=<?= $guardia['id_guardia'] ?>" class="btn btn-danger me-2">
                    <i class="bi bi-file-earmark-pdf"></i> Generar PDF
                </a>
                <?php if (puede_editar_guardia()): ?>
                    <a href="editar_guardia.php?id=<?= $guardia['id_guardia'] ?>" class="btn btn-primary me-2">
                        <i class="bi bi-pencil"></i> Editar Guardia
                    </a>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#eliminarGuardiaModal">
                        <i class="bi bi-trash"></i> Eliminar Guardia
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal para eliminar guardia -->
    <div class="modal fade" id="eliminarGuardiaModal" tabindex="-1" aria-labelledby="eliminarGuardiaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="eliminarGuardiaModalLabel">Confirmar eliminación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro que deseas eliminar esta guardia y todas sus asignaciones?</p>
                    <p class="fw-bold">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a href="eliminar_guardia.php?id=<?= $guardia['id_guardia'] ?>" class="btn btn-danger" id="confirmarEliminarGuardia">
                        <i class="bi bi-trash"></i> Eliminar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para eliminar asignación -->
    <div class="modal fade" id="eliminarAsignacionModal" tabindex="-1" aria-labelledby="eliminarAsignacionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="eliminarAsignacionModalLabel">Confirmar eliminación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro que deseas eliminar esta asignación?</p>
                    <p class="fw-bold">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmarEliminarAsignacion">
                        <i class="bi bi-trash"></i> Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Variable para almacenar el ID de la asignación a eliminar
        let asignacionAEliminar = null;
        
        // Función para mostrar modal de eliminar asignación
        function eliminarAsignacion(id_asignacion) {
            asignacionAEliminar = id_asignacion;
            const modal = new bootstrap.Modal(document.getElementById('eliminarAsignacionModal'));
            modal.show();
        }
        
        // Configurar el botón de confirmación para eliminar asignación
        document.getElementById('confirmarEliminarAsignacion').addEventListener('click', function() {
            if (!asignacionAEliminar) return;
            
            const btn = this;
            const modal = bootstrap.Modal.getInstance(document.getElementById('eliminarAsignacionModal'));
            const originalHtml = btn.innerHTML;
            
            // Mostrar estado de carga
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
            btn.disabled = true;

            fetch('eliminar_asignacion.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + encodeURIComponent(asignacionAEliminar)
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw err; });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Cerrar modal y recargar
                    modal.hide();
                    location.reload();
                } else {
                    mostrarError(data.message || 'Error al eliminar la asignación');
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarError(error.message || 'Error al procesar la solicitud');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            });
        });

        // Función para mostrar errores en un toast
        function mostrarError(mensaje) {
            // Eliminar toast existente si hay uno
            const toastExistente = document.querySelector('.toast-container');
            if (toastExistente) {
                toastExistente.remove();
            }
            
            const toastHTML = `
                <div class="toast-container position-fixed bottom-0 end-0 p-3">
                    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header bg-danger text-white">
                            <strong class="me-auto">Error</strong>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            ${mensaje}
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', toastHTML);
            
            // Auto-ocultar después de 5 segundos
            setTimeout(() => {
                const toast = document.querySelector('.toast.show');
                if (toast) {
                    const bsToast = bootstrap.Toast.getInstance(toast) || new bootstrap.Toast(toast);
                    bsToast.hide();
                }
            }, 5000);
        }
    </script>
</body>
</html>