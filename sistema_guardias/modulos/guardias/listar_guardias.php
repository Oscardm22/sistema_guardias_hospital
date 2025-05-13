<?php
require_once "../../includes/conexion.php";
require_once "../../includes/auth.php";
require_once "../../includes/funciones.php";

// Eliminar guardia (solo para admin)
if (isset($_GET['delete']) && es_admin()) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM guardias WHERE id_guardia = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
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
        case 'mismo_personal':
            $mensaje = 'El personal que entrega no puede ser el mismo que recibe';
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

// Consulta para guardias de 24h
$sql = "SELECT g.*, 
        p_entrega.nombre as nombre_entrega, p_entrega.grado as grado_entrega,
        p_recibe.nombre as nombre_recibe, p_recibe.grado as grado_recibe,
        DATE(g.fecha_inicio) as fecha
        FROM guardias g
        LEFT JOIN personal p_entrega ON g.id_personal_entrega = p_entrega.id_personal
        LEFT JOIN personal p_recibe ON g.id_personal_recibe = p_recibe.id_personal
        WHERE DATE(g.fecha_inicio) BETWEEN ? AND ?
        ORDER BY DATE(g.fecha_inicio), p_entrega.nombre";
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
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/styles_listar_guardias.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include "../../includes/navbar.php"; ?>

    <div class="container py-4">
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
    // Definir $tipo aquí antes de usarla
    $tipo = mb_strtolower(trim($guardia['tipo_guardia']));
    $tipo = in_array($tipo, ['diurna', 'nocturna']) ? $tipo : 'nocturna';
    ?>
    <div class="guardia-24h <?= strtolower($guardia['tipo_guardia']) ?>"
         data-bs-toggle="tooltip"
         title="<?= htmlspecialchars(
             "Fecha: " . date('d/m/Y', strtotime($guardia['fecha_inicio'])) . "\n" .
             "Tipo: " . ucfirst($guardia['tipo_guardia']) . "\n" .
             "Recibe: " . $guardia['grado_recibe'] . " " . $guardia['nombre_recibe']
         ) ?>"
         <?php if (es_admin()): ?>
         onclick="window.location='editar_guardia.php?id=<?= $guardia['id_guardia'] ?>'"
         <?php endif; ?>>
        <div class="info-personal">
            <span class="grado-personal"><?= $guardia['grado_entrega'] ?></span>
            <span class="nombre-personal"><?= $guardia['nombre_entrega'] ?></span>
        </div>
        <div class="tipo-guardia">
            <span class="badge <?= $tipo === 'diurna' ? 'diurna-badge' : 'nocturna-badge' ?>">
                <?= ucfirst(substr($tipo, 0, 3)) ?>
            </span>
            <?php if (es_admin()): ?>
            <form action="eliminar_guardia.php" method="POST" class="d-inline">
                <input type="hidden" name="id" value="<?= $guardia['id_guardia'] ?>">
                <button type="submit" class="btn btn-sm btn-danger" 
                        onclick="return confirm('¿Estás seguro de eliminar esta guardia?')">
                    <i class="bi bi-trash"></i> Eliminar
                </button>
            </form>
            <?php endif; ?>
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
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    trigger: 'hover focus'
                });
            });
        });
    </script>
</body>
</html>