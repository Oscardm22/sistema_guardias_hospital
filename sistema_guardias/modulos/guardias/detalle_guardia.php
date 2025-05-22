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

// Obtener información básica de la guardia
$sql_guardia = "SELECT 
                g.id_guardia,
                g.fecha_inicio,
                g.fecha_fin,
                DATE_FORMAT(g.fecha_inicio, '%d/%m/%Y') as fecha_formateada,
                TIMESTAMPDIFF(HOUR, g.fecha_inicio, g.fecha_fin) as horas_guardia
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

// Obtener asignaciones de personal
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
$orden_turnos = ['diurno', 'vespertino', 'nocturno'];
$asignaciones_por_turno = array_fill_keys($orden_turnos, []);

// Organizar asignaciones por turno
foreach ($asignaciones as $asignacion) {
    $turno = $asignacion['turno'] ?? 'completo';
    // Solo agregamos si está en nuestros turnos definidos
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
        .responsable {
            background-color: #e6f7ff;
            border-left: 4px solid #1890ff;
        }
        .badge-turno {
            font-size: 0.9em;
            padding: 5px 10px;
            margin-right: 10px;
        }
        .badge-diurno {
            background-color: #f6ffed;
            border: 1px solid #b7eb8f;
            color: #389e0d;
        }
        .badge-vespertino {
            background-color: #fff7e6;
            border: 1px solid #ffd591;
            color: #d46b08;
        }
        .badge-nocturno {
            background-color: #f9f0ff;
            border: 1px solid #d3adf7;
            color: #722ed1;
        }
        .badge-completo {
            background-color: #e6f7ff;
            border: 1px solid #91d5ff;
            color: #096dd9;
        }
    </style>
</head>
<body class="bg-light">
    <?php include __DIR__.'/../../includes/navbar.php'; ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0 text-primary"><i class="bi bi-calendar-event"></i> 
                Detalles de Guardia
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
                    <button class="btn btn-danger" onclick="confirmarEliminacion()">
                        <i class="bi bi-trash"></i> Eliminar Guardia
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function confirmarEliminacion() {
            if (confirm('¿Estás seguro de eliminar esta guardia y todas sus asignaciones?')) {
                window.location.href = 'eliminar_guardia.php?id=<?= $guardia['id_guardia'] ?>';
            }
        }

        function eliminarAsignacion(id_asignacion) {
    if (!confirm('¿Estás seguro de eliminar esta asignación?')) {
        return;
    }

    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    
    // Mostrar estado de carga
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
    btn.disabled = true;

    fetch('eliminar_asignacion.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + encodeURIComponent(id_asignacion)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw err; });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Recargar la página para ver cambios
            location.reload();
        } else {
            alert(data.message || 'Error al eliminar la asignación');
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message || 'Error al procesar la solicitud');
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
}
    </script>
</body>
</html>