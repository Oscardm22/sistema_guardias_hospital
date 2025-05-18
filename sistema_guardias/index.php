<?php
// Protección de ruta
require_once "includes/conexion.php";
require_once "includes/auth.php";
require_once "includes/funciones/funciones_personal.php";
require_once "includes/funciones/funciones_guardias.php";
require_once "includes/funciones/funciones_autenticacion.php";

// Obtener datos para el dashboard
$total_personal = PersonalFunciones::contarPersonalActivo($conn);
$guardias_hoy = contar_guardias_hoy($conn);
$proximas_guardias = obtener_proximas_guardias($conn, 5);
$miembros_recientes = PersonalFunciones::obtenerPersonalReciente($conn, 5);

// Obtener nombre de usuario para mostrar (adaptado a tu estructura de sesión)
$nombre_usuario = isset($_SESSION['usuario']['usuario']) ? $_SESSION['usuario']['usuario'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Sistema de Guardias Hospitalarias</title>
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/styles_index.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include "includes/navbar.php"; ?>

    <div class="container mt-4">
        <!-- Encabezado de bienvenida - Versión mejorada -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm bg-gradient-primary text-white">
            <div class="card-body text-center py-4">
                <h2 class="mb-3 fw-bold">
                    <?php echo !empty($nombre_usuario) ? "Bienvenido, " . htmlspecialchars($nombre_usuario) : "Sistema de Gestión de Guardias"; ?>
                </h2>
                <p class="lead mb-0 opacity-75">
                    <i class="bi bi-hospital me-2"></i> Sistema integral para la administración de guardias hospitalarias
                </p>
            </div>
        </div>
    </div>
</div>

        <?php if (isset($_SESSION['usuario'])): ?>
            <!-- Estadísticas rápidas - Versión mejorada -->
<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm h-100 border-start border-4 border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title text-muted text-uppercase small">Personal Activo</h5>
                        <h2 class="mb-0 text-primary"><?php echo $total_personal; ?></h2>
                        <p class="mb-0 small text-muted">Total registrado</p>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                        <i class="bi bi-people-fill fs-2 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm h-100 border-start border-4 border-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title text-muted text-uppercase small">Guardias Hoy</h5>
                        <h2 class="mb-0 text-success"><?php echo $guardias_hoy; ?></h2>
                        <p class="mb-0 small text-muted">Programadas</p>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                        <i class="bi bi-calendar-check fs-2 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

            <!-- Próximas guardias - Versión mejorada -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Próximas Guardias Programadas</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($proximas_guardias)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="bg-light">Fecha</th>
                                    <th class="bg-light">Tipo</th>
                                    <th class="bg-light">Personal Asignado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($proximas_guardias as $guardia): ?>
                                <tr class="hover-shadow">
                                    <td class="fw-bold"><?php echo htmlspecialchars($guardia['fecha']); ?></td>
                                    <td>
                                        <span class="badge bg-info text-dark">
                                            <?php echo htmlspecialchars($guardia['tipo_guardia']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill bg-primary">
                                            <?php echo htmlspecialchars($guardia['total_asignaciones']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="modulos/guardias/listar_guardias.php" class="btn btn-primary mt-2">
                        <i class="bi bi-list-ul me-1"></i> Ver todas las guardias
                    </a>
                <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i> No hay guardias programadas próximamente.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

            <!-- Personal reciente - Versión mejorada -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Último Personal Registrado</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($miembros_recientes)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="bg-light">Nombre</th>
                                    <th class="bg-light">Apellido</th>
                                    <th class="bg-light">Grado</th>
                                    <th class="bg-light">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($miembros_recientes as $miembro): ?>
                                <tr class="hover-shadow">
                                    <td class="fw-bold"><?php echo htmlspecialchars($miembro['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($miembro['apellido']); ?></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo htmlspecialchars($miembro['grado']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($miembro['estado']): ?>
                                            <span class="badge bg-success rounded-pill">
                                                <i class="bi bi-check-circle me-1"></i> Activo
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger rounded-pill">
                                                <i class="bi bi-x-circle me-1"></i> Inactivo
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="modulos/personal/listar_personal.php" class="btn btn-success mt-2">
                        <i class="bi bi-people-fill me-1"></i> Ver todo el personal
                    </a>
                <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i> No se encontró personal registrado.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

            <!-- Acciones rápidas -->
            <?php if (in_array('crear_guardias', $_SESSION['usuario']['permisos'] ?? []) || 
                  in_array('gestion_personal', $_SESSION['usuario']['permisos'] ?? [])): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body text-center">
                            <div class="btn-group" role="group">
                                <?php if (in_array('crear_guardias', $_SESSION['usuario']['permisos'] ?? [])): ?>
                                <a href="modulos/guardias/crear_guardia.php" class="btn btn-primary mx-2">
                                    <i class="bi bi-plus-circle me-2"></i>Nueva Guardia
                                </a>
                                <?php endif; ?>
                                <?php if (in_array('gestion_personal', $_SESSION['usuario']['permisos'] ?? [])): ?>
                                <a href="modulos/personal/crear_personal.php" class="btn btn-success mx-2">
                                    <i class="bi bi-person-plus me-2"></i>Registrar Personal
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Mensaje para invitados -->
            <div class="row">
                <div class="col-md-8 mx-auto">
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-5">
                            <h3 class="mb-4"><i class="bi bi-shield-lock text-primary"></i> Acceso al Sistema</h3>
                            <p class="lead text-muted mb-4">Por favor inicie sesión para acceder a todas las funcionalidades del sistema de gestión de guardias.</p>
                            <a href="modulos/auth/login.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include "includes/footer.php"; ?>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>