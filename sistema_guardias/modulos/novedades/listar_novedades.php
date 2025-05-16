<?php
// Activar reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir archivos necesarios
require_once __DIR__.'/../../includes/conexion.php';
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/funciones/funciones_autenticacion.php';
require_once __DIR__.'/../../includes/funciones/funciones_novedades.php';
require_once __DIR__.'/../../includes/funciones/funciones_ui.php';

// Verificar conexión
if (!isset($conn)) {
    die("Error de conexión a la base de datos");
}

// Obtener parámetros de búsqueda
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;

// Validar fechas
if ($fecha_inicio && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_inicio)) {
    $_SESSION['error'] = "Formato de fecha de inicio no válido (YYYY-MM-DD)";
    $fecha_inicio = null;
}

if ($fecha_fin && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_fin)) {
    $_SESSION['error'] = "Formato de fecha de fin no válido (YYYY-MM-DD)";
    $fecha_fin = null;
}

// Configuración de paginación
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 10;

// Preparar filtros
$filtros = [
    'pagina' => $pagina,
    'por_pagina' => $por_pagina
];

if ($fecha_inicio) $filtros['fecha_desde'] = $fecha_inicio . ' 00:00:00';
if ($fecha_fin) $filtros['fecha_hasta'] = $fecha_fin . ' 23:59:59';

// Obtener datos con filtros
$resultado = listar_novedades_filtradas($filtros, $conn);
$novedades = $resultado['novedades'];
$total_novedades = $resultado['total'];
$total_paginas = $resultado['paginas'];

// Iniciar el buffer de salida
ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Novedades</title>
    <!-- Favicon -->
    <link rel="icon" href="../../assets/images/favicon.ico" type="image/x-icon">
    <!-- Incluir CSS de Bootstrap y personalizado -->
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/styles_navbar.css" rel="stylesheet">
    <link href="../../assets/css/styles_listar_novedades.css" rel="stylesheet">
    <!-- Font Awesome para iconos -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap Datepicker CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <style>
        .datepicker {
            z-index: 1151 !important;
        }
        .filter-card {
            margin-bottom: 20px;
        }
        .filter-header {
            background-color: #6c757d;
            color: white;
        }
    </style>
</head>
<body class="bg-light"> 
    <?php include "../../includes/navbar.php"; ?>

    <div class="container mt-5 pt-3">
        <h2 class="mb-4">Listado de Novedades</h2>
        
        <!-- Card de filtros -->
        <div class="card filter-card">
            <div class="card-header filter-header bg-primary text-white">
                <i class="fas fa-filter"></i> Filtros de Búsqueda
            </div>
            <div class="card-body">
                <form method="get" action="" class="row g-3">
                    <div class="col-md-4">
                        <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                        <div class="input-group date" id="datepicker_inicio">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="fecha_fin" class="form-label">Fecha Fin</label>
                        <div class="input-group date" id="datepicker_fin">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                        <?php if ($fecha_inicio || $fecha_fin): ?>
                            <a href="listar_novedades.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <?php if (puede_crear_novedad()): ?>
        <div class="mb-3">
            <a href="registrar_novedad.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Registrar Nueva Novedad
            </a>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <?php if (empty($novedades)): ?>
            <div class="alert alert-info">No hay novedades registradas <?= ($fecha_inicio || $fecha_fin) ? 'con los filtros aplicados' : '' ?></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Área</th>
                            <th>Descripción</th>
                            <th>Guardia</th>
                            <th>Reportado por</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($novedades as $novedad): ?>
                        <tr>
                            <td><?= formatear_fecha($novedad['fecha_registro']) ?></td>
                            <td><?= formatear_area_novedad($novedad['area']) ?></td>
                            <td><?= substr($novedad['descripcion'], 0, 50) ?>...</td>
                            <td><?= formatear_fecha($novedad['fecha_guardia']) ?></td>
                            <td><?= htmlspecialchars($novedad['grado'] . ' ' . $novedad['nombre_personal'] . ' ' . $novedad['apellido']) ?></td>
                            <td>
                                <a href="detalle_novedad.php?id=<?= $novedad['id_novedad'] ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if (puede_editar_novedad($novedad['id_novedad'], $conn)): ?>
                                <a href="editar_novedad.php?id=<?= $novedad['id_novedad'] ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (puede_eliminar_novedad($novedad['id_novedad'], obtener_id_personal_usuario(), $conn)): ?>
                                <a href="eliminar_novedad.php?id=<?= $novedad['id_novedad'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar esta novedad?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if($pagina > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?pagina=<?= $pagina-1 ?><?= $fecha_inicio ? '&fecha_inicio='.urlencode($fecha_inicio) : '' ?><?= $fecha_fin ? '&fecha_fin='.urlencode($fecha_fin) : '' ?>">Anterior</a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for($i = 1; $i <= $total_paginas; $i++): ?>
                    <li class="page-item <?= ($i == $pagina) ? 'active' : '' ?>">
                        <a class="page-link" href="?pagina=<?= $i ?><?= $fecha_inicio ? '&fecha_inicio='.urlencode($fecha_inicio) : '' ?><?= $fecha_fin ? '&fecha_fin='.urlencode($fecha_fin) : '' ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if($pagina < $total_paginas): ?>
                    <li class="page-item">
                        <a class="page-link" href="?pagina=<?= $pagina+1 ?><?= $fecha_inicio ? '&fecha_inicio='.urlencode($fecha_inicio) : '' ?><?= $fecha_fin ? '&fecha_fin='.urlencode($fecha_fin) : '' ?>">Siguiente</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <!-- Incluir footer -->
    <?php include __DIR__.'/../../includes/footer.php'; ?>

    <!-- Scripts JS -->
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/guardia.js"></script>
    <!-- jQuery (necesario para Bootstrap y Datepicker) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Datepicker JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.es.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Inicializar datepicker
        $('#datepicker_inicio, #datepicker_fin').datepicker({
            format: 'yyyy-mm-dd',
            language: 'es',
            autoclose: true,
            todayHighlight: true,
            orientation: "bottom auto"
        });
        
        // Validación de fechas
        $('form').submit(function() {
            const fechaInicio = $('#fecha_inicio').val();
            const fechaFin = $('#fecha_fin').val();
            
            if (fechaInicio && fechaFin && new Date(fechaInicio) > new Date(fechaFin)) {
                alert('La fecha de inicio no puede ser mayor que la fecha de fin');
                return false;
            }
            return true;
        });
    });
    </script>
</body>
</html>
<?php
// Limpiar el buffer y mostrar el contenido
ob_end_flush();
?>