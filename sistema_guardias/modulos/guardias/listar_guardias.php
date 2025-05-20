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
        case 'guardia_eliminada':
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

// Manejo de navegación por meses
$mes_referencia = isset($_GET['mes']) ? $_GET['mes'] : date('Y-m');
$anio_actual = date('Y', strtotime($mes_referencia));
$mes_actual = date('m', strtotime($mes_referencia));

// Obtener primer y último día del mes
$primer_dia_mes = date('Y-m-01', strtotime($mes_referencia));
$ultimo_dia_mes = date('Y-m-t', strtotime($mes_referencia));

// Consulta para guardias del mes
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
$stmt->bind_param("ss", $primer_dia_mes, $ultimo_dia_mes);
$stmt->execute();
$result = $stmt->get_result();

// Organizar guardias por fecha
$guardias_por_fecha = array();
while ($guardia = $result->fetch_assoc()) {
    $fecha = $guardia['fecha'];
    $guardias_por_fecha[$fecha][] = $guardia;
}

// Obtener días del mes
$total_dias_mes = date('t', strtotime($primer_dia_mes));
$primer_dia_semana = date('N', strtotime($primer_dia_mes)); // 1 (lunes) a 7 (domingo)

// Obtener nombre del mes localizado
if (class_exists('IntlDateFormatter')) {
    $formatter = new IntlDateFormatter(
        'es_ES', // o tu locale preferido
        IntlDateFormatter::LONG,
        IntlDateFormatter::NONE,
        null,
        null,
        'MMMM yyyy'
    );
    $nombre_mes = $formatter->format(strtotime($primer_dia_mes));
} else {
    // Solución alternativa si no tienes la extensión intl
    $meses = [
        1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];
    $nombre_mes = $meses[(int)date('n', strtotime($primer_dia_mes))] . ' ' . date('Y', strtotime($primer_dia_mes));
}
?>

<?php
// [El código PHP anterior se mantiene exactamente igual hasta la parte del HTML]
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horario Mensual de Guardias</title>
    <!-- Favicon -->
    <link rel="icon" href="../../assets/images/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/styles_listar_guardias.css" rel="stylesheet">
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
            <h2 class="mb-0 text-primary"><i class="bi bi-calendar3 me-2"></i> Horario Mensual</h2>
        </div>

        <!-- Navegación entre meses -->
        <div class="card mb-4">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="?mes=<?= date('Y-m', strtotime($primer_dia_mes.' -1 month')) ?>" 
                       class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-chevron-left"></i> Mes anterior
                    </a>
                    <h4 class="mb-0 text-center text-primary fw-bold">
                        <?= $nombre_mes ?>
                    </h4>
                    <a href="?mes=<?= date('Y-m', strtotime($primer_dia_mes.' +1 month')) ?>" 
                       class="btn btn-outline-primary btn-sm">
                        Mes siguiente <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Mostrar botón "Nueva Guardia" solo para admin -->
        <?php if (es_admin()): ?>
        <div class="d-flex justify-content-end mb-4">
            <a href="crear_guardia.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i> Nueva Guardia
            </a>
        </div>
        <?php endif; ?>

        <div class="card shadow-lg">
            <div class="card-body p-0 overflow-hidden">
                <div class="calendario-grid">
                    <!-- Encabezados de días de la semana -->
                    <?php 
                    $dias_semana = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
                    foreach ($dias_semana as $dia): ?>
                        <div class="dia-semana-header"><?= $dia ?></div>
                    <?php endforeach; ?>

                    <!-- Días vacíos al inicio si el mes no comienza en lunes -->
                    <?php for ($i = 1; $i < $primer_dia_semana; $i++): ?>
                        <div class="dia-celda dia-otro-mes"></div>
                    <?php endfor; ?>

                    <!-- Días del mes -->
                    <?php for ($dia = 1; $dia <= $total_dias_mes; $dia++): ?>
                        <?php
                        $fecha_actual = date('Y-m-d', strtotime($primer_dia_mes . ' +' . ($dia-1) . ' days'));
                        $es_hoy = $fecha_actual == date('Y-m-d');
                        $guardias_dia = $guardias_por_fecha[$fecha_actual] ?? [];
                        ?>
                        <div class="dia-celda <?= $es_hoy ? 'dia-actual' : '' ?>">
                            <div class="numero-dia"><?= $dia ?></div>
                            
                            <?php foreach ($guardias_dia as $guardia): ?>
                                <?php
                                $tipo = mb_strtolower(trim($guardia['tipo_guardia']));
                                $tipo = in_array($tipo, ['diurna', 'nocturna', '24h']) ? $tipo : 'nocturna';
                                $clase_guardia = $tipo === 'diurna' ? 'guardia-diurna' : 
                                               ($tipo === 'nocturna' ? 'guardia-nocturna' : 'guardia-completa');
                                ?>
                                <div class="guardia-24h position-relative <?= $clase_guardia ?>"
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
                                    style="cursor: <?= es_admin() ? 'pointer' : 'default' ?>;"
                                    <?php endif; ?>>
                                    
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

                    <!-- Días vacíos al final para completar la última semana -->
                    <?php 
                    $ultimo_dia_semana = date('N', strtotime($ultimo_dia_mes));
                    $dias_restantes = 7 - $ultimo_dia_semana;
                    for ($i = 0; $i < $dias_restantes; $i++): ?>
                        <div class="dia-celda dia-otro-mes"></div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Activar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Manejar clic en guardia
        function handleCellClick(event, idGuardia) {
            if (event.target.closest('.btn-eliminar')) {
                return;
            }
            
            window.location.href = `detalle_guardia.php?id=${idGuardia}`;
        }
        
        // Animación al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            const celdas = document.querySelectorAll('.dia-celda');
            celdas.forEach((celda, index) => {
                setTimeout(() => {
                    celda.style.opacity = '1';
                }, index * 50);
            });
        });
    </script>
</body>
</html>