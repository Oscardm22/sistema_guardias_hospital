<?php
require_once "../../includes/conexion.php";
require_once "../../includes/auth.php"; // Para verificar sesión

// Eliminar guardia (si recibe parámetro ?delete=id)
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM guardias WHERE id_guardia = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// Manejo de navegación por semanas
$fecha_referencia = isset($_GET['semana']) ? $_GET['semana'] : date('Y-m-d');
$lunes_semana = date('Y-m-d', strtotime('monday this week', strtotime($fecha_referencia)));
$domingo_semana = date('Y-m-d', strtotime('sunday this week', strtotime($fecha_referencia)));

// Obtener guardias de la semana seleccionada
$sql = "SELECT g.*, 
        p_entrega.nombre as nombre_entrega, p_entrega.grado as grado_entrega,
        p_recibe.nombre as nombre_recibe, p_recibe.grado as grado_recibe
        FROM guardias g
        LEFT JOIN personal p_entrega ON g.id_personal_entrega = p_entrega.id_personal
        LEFT JOIN personal p_recibe ON g.id_personal_recibe = p_recibe.id_personal
        WHERE DATE(g.fecha_inicio) BETWEEN ? AND ?
        ORDER BY g.fecha_inicio";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $lunes_semana, $domingo_semana);
$stmt->execute();
$result = $stmt->get_result();

// Organizar guardias por día
$guardias_por_dia = array();
while ($guardia = $result->fetch_assoc()) {
    $dia_semana = date('N', strtotime($guardia['fecha_inicio']));
    $guardias_por_dia[$dia_semana][] = $guardia;
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
    <link href="../../assets/css/style_listar_guardias.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include "../../includes/navbar.php"; ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0"><i class="bi bi-calendar-week"></i> Horario Semanal</h2>
            <div>
                <span class="badge bg-success badge-leyenda">Diurna</span>
                <span class="badge bg-dark badge-leyenda">Nocturna</span>
            </div>
        </div>

        <div class="semana-navegacion d-flex justify-content-between align-items-center mb-3">
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

        <div class="d-flex justify-content-end mb-3">
            <a href="crear_guardia.php" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Nueva Guardia
            </a>
        </div>

        <div class="card shadow">
            <div class="card-body p-2">
                <div class="horario-container">
                    <div class="horario-semanal">
                        <!-- Encabezados de columnas -->
                        <div class="hora-col"></div>
                        <?php 
                        $dias_semana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                        foreach ($dias_semana as $index => $dia): 
                            $fecha_dia = date('d/m', strtotime($lunes_semana." +{$index} days"));
                        ?>
                            <div class="dia-header">
                                <div><?= $dia ?></div>
                                <div style="font-size:0.7rem"><?= $fecha_dia ?></div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Filas por hora -->
                        <?php for ($hora = 0; $hora < 24; $hora++): ?>
    <div class="hora-col">
        <?= str_pad($hora, 2, '0', STR_PAD_LEFT) ?>:00
    </div>
    <?php for ($dia = 1; $dia <= 7; $dia++): ?>
        <div class="celda-guardia" id="hora-<?= $hora ?>-dia-<?= $dia ?>">
            <?php if (isset($guardias_por_dia[$dia])): ?>
                <?php foreach ($guardias_por_dia[$dia] as $guardia): 
                    $hora_inicio = (int)date('H', strtotime($guardia['fecha_inicio']));
                    $hora_fin = (int)date('H', strtotime($guardia['fecha_fin']));
                    
                    // Mostrar en todas las celdas que abarca la guardia
                    if ($hora >= $hora_inicio && $hora <= $hora_fin):
                        // Solo mostrar contenido en la primera hora
                        $mostrar_contenido = ($hora == $hora_inicio);
                        
                        $inicio_ts = strtotime($guardia['fecha_inicio']);
                        $fin_ts = strtotime($guardia['fecha_fin']);
                        if ($fin_ts < $inicio_ts) {
                            $fin_ts += 86400; // sumar 24h si cruza medianoche
                        }
                        $duracion_min = ($fin_ts - $inicio_ts) / 60;
                        
                        // Calcular altura solo para la primera celda
                        $height_px = ($hora == $hora_inicio) ? ($duracion_min / 60) * 30 : 30;
                        $top_offset = ($hora == $hora_inicio) ? (date('i', strtotime($guardia['fecha_inicio'])) / 60) * 30 : 0;
                        
                        $tipo_class = strtolower($guardia['tipo_guardia']);
                        $tooltip_text = htmlspecialchars(
                            "De: {$guardia['grado_entrega']} {$guardia['nombre_entrega']}\n" .
                            "A: {$guardia['grado_recibe']} {$guardia['nombre_recibe']}\n" .
                            "Hora: " . date('H:i', strtotime($guardia['fecha_inicio'])) . " - " . 
                            date('H:i', strtotime($guardia['fecha_fin']))
                        );
                ?>
                    <div class="guardia-bloque <?= $tipo_class ?> <?= $mostrar_contenido ? '' : 'sin-contenido' ?>" 
     style="top: <?= $top_offset ?>px; height: <?= $height_px ?>px;"
     data-bs-toggle="tooltip" 
     title="<?= $tooltip_text ?>"
     onclick="window.location='editar_guardia.php?id=<?= $guardia['id_guardia'] ?>'">
    <?php if ($mostrar_contenido): ?>
        <div class="contenido-guardia">
            <span class="hora-guardia">
                <?= date('H:i', strtotime($guardia['fecha_inicio'])) ?> - <?= date('H:i', strtotime($guardia['fecha_fin'])) ?>
            </span>
            <span class="nombre-guardia">
                <?= htmlspecialchars($guardia['grado_entrega'] . ' ' . $guardia['nombre_entrega']) ?>
            </span>
        </div>
    <?php endif; ?>
</div>
                <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endfor; ?>
<?php endfor; ?>
                    </div>
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