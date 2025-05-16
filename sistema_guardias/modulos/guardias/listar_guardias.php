<?php
require_once "../../includes/conexion.php";
require_once "../../includes/auth.php";
require_once "../../includes/funciones.php";

// Eliminar guardia (solo para admin)
if (isset($_GET['delete']) && es_admin()) {
    $id = $_GET['delete'];
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    try {
        // Primero eliminar asignaciones
        $sql = "DELETE FROM asignaciones_guardia WHERE id_guardia = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Luego eliminar la guardia
        $sql = "DELETE FROM guardias WHERE id_guardia = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $conn->commit();
        header('Location: listar_guardias.php?success=1');
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        header('Location: listar_guardias.php?error=eliminacion');
        exit;
    }
}

// --- MANEJO DE MENSAJES --- //
$mensaje = '';
$clase_mensaje = '';

if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case '1':
            $mensaje = 'Guardia eliminada correctamente';
            $clase_mensaje = 'success';
            break;
        case 'guardia_creada':
            $mensaje = 'Guardia creada exitosamente';
            $clase_mensaje = 'success';
            break;
        case 'guardia_actualizada':
            $mensaje = 'Guardia actualizada correctamente';
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
            $mensaje = 'Error al eliminar la guardia';
            $clase_mensaje = 'danger';
            break;
    }
}

// Manejo de navegación por semanas
$fecha_referencia = isset($_GET['semana']) ? $_GET['semana'] : date('Y-m-d');
$lunes_semana = date('Y-m-d', strtotime('monday this week', strtotime($fecha_referencia)));
$domingo_semana = date('Y-m-d', strtotime('sunday this week', strtotime($fecha_referencia)));

// Consulta para guardias colectivas
$sql = "SELECT 
        g.id_guardia,
        g.fecha_inicio,
        g.fecha_fin,
        g.tipo_guardia,
        DATE(g.fecha_inicio) as fecha,
        GROUP_CONCAT(DISTINCT CONCAT(p.nombre, ' ', p.apellido) SEPARATOR '\n') AS equipo,
        COUNT(DISTINCT a.id_personal) AS total_personal
    FROM guardias g
    LEFT JOIN asignaciones_guardia a ON g.id_guardia = a.id_guardia
    LEFT JOIN personal p ON a.id_personal = p.id_personal
    WHERE DATE(g.fecha_inicio) BETWEEN ? AND ?
    GROUP BY g.id_guardia
    ORDER BY g.fecha_inicio";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $lunes_semana, $domingo_semana);
$stmt->execute();
$result = $stmt->get_result();

// Organizar guardias por fecha
$guardias_por_fecha = array();
while ($guardia = $result->fetch_assoc()) {
    $fecha = $guardia['fecha'];
    $guardias_por_fecha[$fecha][] = $guardia;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horario Semanal de Guardias</title>
    <!-- Favicon -->
    <link rel="icon" href="../../assets/images/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/styles_listar_guardias.css" rel="stylesheet">
    <style>
        /* Estilos para guardias colectivas */
        .guardia-24h {
            padding: 8px;
            margin-bottom: 5px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .info-guardia {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .equipo-guardia {
            font-size: 0.8em;
            color: #555;
        }
        
        .diurna-badge {
            background-color: #28a745;
            color: white;
        }
        
        .nocturna-badge {
            background-color: #007bff;
            color: white;
        }
        
        .completo-badge {
            background-color: #6c757d;
            color: white;
        }
        
        /* Tooltip mejorado */
        .tooltip-inner {
            max-width: 300px;
            text-align: left;
            white-space: pre-wrap;
        }
        
        /* Celda de día */
        .dia-celda {
            min-height: 100px;
            border-right: 1px solid #dee2e6;
            border-bottom: 1px solid #dee2e6;
            padding: 5px;
        }
    </style>
</head>
<body class="bg-light">
    <?php include "../../includes/navbar.php"; ?>

    <div class="container py-4">
        <?php if ($mensaje): ?>
            <div class="alert alert-<?= $clase_mensaje ?>"><?= $mensaje ?></div>
        <?php endif; ?>
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0"><i class="bi bi-calendar-week"></i> Horario Semanal</h2>
        </div>

        <!-- Navegación entre semanas -->
        <div class="card mb-3">
            <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="?semana=<?= date('Y-m-d', strtotime($lunes_semana.' -1 week')) ?>" 
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-chevron-left"></i> Semana anterior
                    </a>
                    <h5 class="mb-0 text-center">
                        <?= date('d/m/Y', strtotime($lunes_semana)) ?> - <?= date('d/m/Y', strtotime($domingo_semana)) ?>
                    </h5>
                    <a href="?semana=<?= date('Y-m-d', strtotime($lunes_semana.' +1 week')) ?>" 
                       class="btn btn-sm btn-outline-secondary">
                        Semana siguiente <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Mostrar botón "Nueva Guardia" solo para admin -->
        <?php if (es_admin()): ?>
        <div class="d-flex justify-content-end mb-3">
            <a href="crear_guardia.php" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Nueva Guardia
            </a>
        </div>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-body p-0">
                <div class="horario-semanal">
                    <!-- Encabezados de días -->
                    <?php 
                    $dias_semana = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
                    foreach ($dias_semana as $index => $dia): 
                        $fecha_dia = date('d/m', strtotime($lunes_semana." +{$index} days"));
                    ?>
                        <div class="dia-header">
                            <div><?= $dia ?></div>
                            <div class="fecha-dia"><?= $fecha_dia ?></div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Guardias por día -->
                    <?php for ($dia = 0; $dia < 7; $dia++): ?>
                        <?php 
                        $fecha_actual = date('Y-m-d', strtotime($lunes_semana." +{$dia} days"));
                        $guardias_dia = $guardias_por_fecha[$fecha_actual] ?? [];
                        ?>
                        <div class="dia-celda">
                            <?php foreach ($guardias_dia as $guardia): ?>
                                <?php
                                $tipo = mb_strtolower(trim($guardia['tipo_guardia']));
                                $tipo = in_array($tipo, ['diurna', 'nocturna', '24h']) ? $tipo : 'nocturna';
                                $color_fondo = $tipo === 'diurna' ? '#D4EDDA' : ($tipo === 'nocturna' ? '#C2DFFF' : '#E2E3E5');
                                ?>
                                <div class="guardia-24h position-relative"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    data-bs-boundary="viewport"
                                    title="<?= htmlspecialchars(
                                        "Fecha: " . date('d/m/Y', strtotime($guardia['fecha_inicio'])) . "\n" .
                                        "Tipo: " . ucfirst($guardia['tipo_guardia']) . "\n" .
                                        "Equipo (" . $guardia['total_personal'] . "):\n" . $guardia['equipo']
                                    ) ?>"
                                    <?php if (es_admin()): ?>
                                    onclick="handleCellClick(event, <?= $guardia['id_guardia'] ?>)"
                                    style="cursor: <?= es_admin() ? 'pointer' : 'default' ?>; background-color: <?= $color_fondo ?>;"
                                    <?php endif; ?>>
                                    
                                    <?php if (es_admin()): ?>
                                    <div class="position-absolute top-0 end-0 p-1">
                                        <form action="eliminar_guardia.php" method="POST" class="d-inline form-eliminar">
                                            <input type="hidden" name="id" value="<?= $guardia['id_guardia'] ?>">
                                            <button type="submit" 
                                                    class="btn btn-sm btn-eliminar"
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-placement="top"
                                                    data-bs-boundary="viewport"
                                                    title="Eliminar guardia"
                                                    onclick="event.stopPropagation()">
                                                <i class="bi bi-trash text-danger"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="info-guardia">
                                        <div class="tipo-guardia">
                                            <span class="badge <?= $tipo === 'diurna' ? 'diurna-badge' : ($tipo === 'nocturna' ? 'nocturna-badge' : 'completo-badge') ?>">
                                                <?= ucfirst($tipo === '24h' ? '24h' : substr($tipo, 0, 3)) ?>
                                            </span>
                                        </div>
                                        <div class="equipo-guardia">
                                            <small><?= $guardia['total_personal'] ?> miembros</small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Activar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Manejar clic en guardia
        function handleCellClick(event, idGuardia) {
            // Evitar que se active cuando se hace clic en el botón de eliminar
            if (event.target.closest('.btn-eliminar')) {
                return;
            }
            
            // Redirigir a la página de detalles/edición
            window.location.href = `detalle_guardia.php?id=${idGuardia}`;
        }
    </script>
</body>
</html>