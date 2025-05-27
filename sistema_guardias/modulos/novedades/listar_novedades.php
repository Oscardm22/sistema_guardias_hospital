<?php

require_once __DIR__.'/../../includes/conexion.php';
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/funciones/funciones_autenticacion.php';
require_once __DIR__.'/../../includes/funciones/funciones_novedades.php';
require_once __DIR__.'/../../includes/funciones/funciones_ui.php';

// Verificar conexión
if (!isset($conn)) {
    die("Error de conexión a la base de datos");
}

// Obtener parámetro de búsqueda (fecha única)
$fecha = isset($_GET['fecha']) ? trim($_GET['fecha']) : null;

// Validar fecha
if ($fecha && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    $_SESSION['error'] = [
        'titulo' => 'Error',
        'mensaje' => 'Formato de fecha no válido (debe ser YYYY-MM-DD)',
        'tipo' => 'danger'
    ];
    $fecha = null;
}

// Configuración de paginación
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 10;

// Preparar filtros para la consulta
$filtros = [
    'pagina' => $pagina,
    'por_pagina' => $por_pagina
];

// Si hay fecha válida, establecer rango para todo el día
if ($fecha) {
    $filtros['fecha_desde'] = $fecha . ' 00:00:00';
    $filtros['fecha_hasta'] = $fecha . ' 23:59:59';
}

// Obtener datos con filtros
$resultado = listar_novedades_filtradas($filtros, $conn);
$novedades = $resultado['novedades'];
$total_novedades = $resultado['total'];
$total_paginas = $resultado['paginas'];

// Función para formatear fecha en español
function formatear_fecha_esp($fecha) {
    if (empty($fecha)) return '';
    $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    
    $timestamp = strtotime($fecha);
    $dia_semana = $dias[date('w', $timestamp)];
    $dia = date('d', $timestamp);
    $mes = $meses[date('n', $timestamp) - 1];
    $anio = date('Y', $timestamp);
    
    return "$dia_semana, $dia de $mes de $anio";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Novedades</title>
    <!-- Favicon -->
    <link rel="icon" href="../../assets/images/favicon.ico" type="image/x-icon">
    <!-- Incluir CSS -->
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/styles_navbar.css" rel="stylesheet">
    <link href="../../assets/css/styles_listar_novedades.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap Datepicker CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Estilos para los mensajes debajo del navbar */
        .alert-messages {
            margin-top: 20px;
            margin-bottom: 0;
        }
        .alert-messages .alert {
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .alert-dismissible .btn-close {
            position: absolute;
            top: 50%;
            right: 1rem;
            transform: translateY(-50%);
        }
        .fa-check-circle {
            color: #28a745;
        }
        .fa-exclamation-circle {
            color: #dc3545;
        }
        
        /* Estilos existentes */
        .datepicker {
            z-index: 1151 !important;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .filter-card {
            margin-bottom: 25px;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .filter-header {
            background: linear-gradient(135deg, #4361ee, #3a0ca3);
            color: white;
            font-weight: 500;
            padding: 15px 20px;
            border-radius: 10px 10px 0 0;
        }
        .datepicker-button {
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 10px 25px;
            font-size: 1.1rem;
            border-radius: 8px;
            border: 2px solid #4361ee;
            background-color: white;
            color: #4361ee;
            font-weight: 500;
        }
        .datepicker-button:hover {
            background-color: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .datepicker-button i {
            margin-right: 8px;
        }
        .hidden-field {
            display: none;
        }
        .btn-limpiar {
            padding: 10px 25px;
            font-size: 1.1rem;
            border-radius: 8px;
            margin-left: 10px;
        }
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .table th {
            background-color: #4361ee;
            color: white;
            font-weight: 500;
            text-align: center;
            vertical-align: middle;
        }
        .table td {
            vertical-align: middle;
        }
        .modal-danger .modal-header {
            background-color: #dc3545;
            border-bottom: none;
        }
        .modal-danger .modal-title {
            font-weight: 500;
        }
        .modal-danger .btn-close-white {
            filter: invert(1);
        }
        .modal-danger .modal-footer .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            padding: 0.375rem 1.5rem;
        }
        .modal-danger .modal-footer .btn-danger:hover {
            background-color: #bb2d3b;
            border-color: #b02a37;
        }
    </style>
</head>
<body class="bg-light"> 
    <?php include "../../includes/navbar.php"; ?>

    <!-- Contenedor para mensajes debajo del navbar -->
    <div class="container alert-messages">
        <?php if (isset($_SESSION['exito'])): ?>
            <div class="alert alert-<?= $_SESSION['exito']['tipo'] ?> alert-dismissible fade show">
                <div class="d-flex align-items-center">
                    <i class="fas <?= $_SESSION['exito']['tipo'] === 'success' ? 'fa-check-circle' : 'fa-info-circle' ?> fs-3 me-3"></i>
                    <div>
                        <h5 class="alert-heading mb-1"><?= htmlspecialchars($_SESSION['exito']['titulo']) ?></h5>
                        <p class="mb-0"><?= htmlspecialchars($_SESSION['exito']['mensaje']) ?></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['exito']); endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-<?= $_SESSION['error']['tipo'] ?> alert-dismissible fade show">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle fs-3 me-3"></i>
                    <div>
                        <h5 class="alert-heading mb-1"><?= htmlspecialchars($_SESSION['error']['titulo']) ?></h5>
                        <p class="mb-0"><?= htmlspecialchars($_SESSION['error']['mensaje']) ?></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); endif; ?>
    </div>

    <div class="container mt-4">
        <h2 class="mb-4 text-primary"><i class="fas fa-list-alt me-2"></i> Listado de Novedades</h2>
        
        <!-- Card de filtros simplificada -->
        <div class="card filter-card">
            <div class="card-header filter-header">
                <i class="fas fa-calendar-day me-2"></i> Filtrar por fecha
            </div>
            <div class="card-body text-center">
                <form method="get" action="" id="filtroForm">
                    <!-- Campo oculto para la fecha -->
                    <input type="text" id="fecha" name="fecha" class="hidden-field" value="<?= htmlspecialchars($fecha) ?>">
                    
                    <!-- Botón para abrir el datepicker -->
                    <button type="button" id="datepickerButton" class="datepicker-button">
                        <i class="fas fa-calendar-alt"></i>
                        <?= $fecha ? formatear_fecha_esp($fecha) : 'Seleccionar fecha' ?>
                    </button>
                    
                    <!-- Botón para limpiar (solo visible cuando hay fecha seleccionada) -->
                    <?php if ($fecha): ?>
                        <a href="listar_novedades.php" class="btn btn-outline-secondary btn-limpiar">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <?php if (puede_crear_novedad()): ?>
        <div class="mb-4 text-end">
            <a href="registrar_novedad.php" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i> Nueva Novedad
            </a>
        </div>
        <?php endif; ?>

        <?php if (empty($novedades)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No hay novedades registradas <?= $fecha ? 'para el ' . formatear_fecha_esp($fecha) : '' ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>Fecha Registro</th>
                            <th>Área</th>
                            <th>Descripción</th>
                            <th>Fecha Guardia</th>
                            <th>Reportado por</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($novedades as $novedad): ?>
                        <tr>
                            <td class="text-center"><?= formatear_fecha($novedad['fecha_registro']) ?></td>
                            <td class="text-center"><?= formatear_area_novedad($novedad['area']) ?></td>
                            <td><?= htmlspecialchars(substr($novedad['descripcion'], 0, 50)) ?>...</td>
                            <td class="text-center"><?= formatear_fecha($novedad['fecha_guardia']) ?></td>
                            <td><?= htmlspecialchars($novedad['grado'] . ' ' . $novedad['nombre_personal'] . ' ' . $novedad['apellido']) ?></td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="detalle_novedad.php?id=<?= $novedad['id_novedad'] ?>" class="btn btn-sm btn-info" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (puede_editar_novedad($novedad['id_novedad'], $conn)): ?>
                                    <a href="editar_novedad.php?id=<?= $novedad['id_novedad'] ?>" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (puede_eliminar_novedad($novedad['id_novedad'], obtener_id_personal_usuario(), $conn)): ?>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#confirmarEliminarModal<?= $novedad['id_novedad'] ?>" title="Eliminar">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>

                                <!-- Modal para cada fila -->
                                <div class="modal fade" id="confirmarEliminarModal<?= $novedad['id_novedad'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-exclamation-triangle me-2"></i>Confirmar Eliminación
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>¿Está seguro que desea eliminar esta novedad?</p>
                                                <div class="alert alert-warning">
                                                    <i class="fas fa-exclamation-circle me-2"></i>
                                                    Esta acción no se puede deshacer
                                                </div>
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <p class="mb-1"><strong>ID:</strong> <?= $novedad['id_novedad'] ?></p>
                                                        <p class="mb-1"><strong>Fecha:</strong> <?= formatear_fecha($novedad['fecha_registro']) ?></p>
                                                        <p class="mb-0"><strong>Descripción:</strong> <?= htmlspecialchars(substr($novedad['descripcion'], 0, 50)) ?>...</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                    <i class="fas fa-times me-1"></i> Cancelar
                                                </button>
                                                <a href="eliminar_novedad.php?id=<?= $novedad['id_novedad'] ?>" class="btn btn-danger">
                                                    <i class="fas fa-trash-alt me-1"></i> Eliminar
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <?php if ($total_paginas > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if($pagina > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?pagina=<?= $pagina-1 ?><?= $fecha ? '&fecha='.urlencode($fecha) : '' ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for($i = 1; $i <= $total_paginas; $i++): ?>
                    <li class="page-item <?= ($i == $pagina) ? 'active' : '' ?>">
                        <a class="page-link" href="?pagina=<?= $i ?><?= $fecha ? '&fecha='.urlencode($fecha) : '' ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if($pagina < $total_paginas): ?>
                    <li class="page-item">
                        <a class="page-link" href="?pagina=<?= $pagina+1 ?><?= $fecha ? '&fecha='.urlencode($fecha) : '' ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <!-- Incluir footer -->
    <?php include __DIR__.'/../../includes/footer.php'; ?>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Datepicker JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.es.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Inicializar datepicker
        $('#datepickerButton').datepicker({
            format: 'yyyy-mm-dd',
            language: 'es',
            autoclose: true,
            todayHighlight: true,
            orientation: "bottom auto",
            todayBtn: "linked",
            clearBtn: true,
            templates: {
                leftArrow: '<i class="fas fa-chevron-left"></i>',
                rightArrow: '<i class="fas fa-chevron-right"></i>'
            }
        }).on('changeDate', function(e) {
            // Actualizar el valor del campo oculto
            $('#fecha').val(e.format('yyyy-mm-dd'));
            
            // Actualizar texto del botón con formato legible
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const fechaFormateada = new Date(e.date).toLocaleDateString('es-ES', options);
            $('#datepickerButton').html('<i class="fas fa-calendar-alt"></i> ' + fechaFormateada);
            
            // Enviar el formulario automáticamente
            $('#filtroForm').submit();
        });
        
        // Mostrar datepicker al hacer clic en el botón
        $('#datepickerButton').click(function(e) {
            e.preventDefault();
            $(this).datepicker('show');
        });
    });
    </script>
</body>
</html>
<?php
// Limpiar el buffer y mostrar el contenido
ob_end_flush();
?>