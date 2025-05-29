<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si config.php existe antes de requerirlo
$configPath = __DIR__ . '/config.php';
if (!file_exists($configPath)) {
    die('Error: Archivo de configuración no encontrado');
}
require_once $configPath;

// Verificar que BASE_URL esté definido
if (!defined('BASE_URL')) {
    die('Error: BASE_URL no está definido en config.php');
}

// Obtener el nombre del archivo actual para resaltar el menú activo
$currentPage = basename($_SERVER['SCRIPT_NAME']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navbar - Sistema de Guardias</title>
    <!-- Favicon -->
    <link rel="icon" href="<?= htmlspecialchars(BASE_URL) ?>/assets/images/favicon.ico" type="image/x-icon">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= htmlspecialchars(BASE_URL) ?>/assets/css/styles_navbar.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?= htmlspecialchars(BASE_URL) ?>/index.php">
                <img src="<?= htmlspecialchars(BASE_URL) ?>/assets/images/logo_hospital.png" 
                     alt="Logo Hospital" width="40" height="40" class="d-inline-block align-top">
                Guardias Hospitalarias
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>" 
                           href="<?= htmlspecialchars(BASE_URL) ?>/index.php">
                           <i class="bi bi-house-door"></i> Inicio
                        </a>
                    </li>

                    <?php if (isset($_SESSION['usuario'])): ?>
                        <!-- Menú para usuarios logueados -->
                        <?php if (isset($_SESSION['usuario']['nombre'])): ?>
                            <li class="nav-item">
                                <span class="nav-link text-white">
                                    <i class="bi bi-person-circle"></i> 
                                    <?= htmlspecialchars($_SESSION['usuario']['rol'] === 'admin' ? 'Administrador' : 'Usuario') ?>
                                </span>
                            </li>
                        <?php endif; ?>
                        
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'guardias') !== false ? 'active' : '' ?>" 
                               href="<?= htmlspecialchars(BASE_URL) ?>/modulos/guardias/listar_guardias.php">
                               <i class="bi bi-calendar-week"></i> Guardias
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'novedades') !== false ? 'active' : '' ?>" 
                               href="<?= htmlspecialchars(BASE_URL) ?>/modulos/novedades/listar_novedades.php">
                               <i class="bi bi-exclamation-triangle"></i> Novedades
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'ordenes') !== false ? 'active' : '' ?>" 
                            href="<?= htmlspecialchars(BASE_URL) ?>/modulos/ordenes_salida/listar_ordenes.php">
                            <i class="bi bi-truck"></i> Órdenes de Salida
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'servicios') !== false ? 'active' : '' ?>" 
                               href="<?= htmlspecialchars(BASE_URL) ?>/modulos/servicios/listar_servicios.php">
                                <i class="bi bi-speedometer2 me-2"></i> Servicios
                            </a>
                        </li>

                        <?php if ($_SESSION['usuario']['rol'] === 'admin'): ?>                        
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle <?= strpos($_SERVER['REQUEST_URI'], 'admin') !== false ? 'active' : '' ?>" 
                                   href="#" id="adminDropdown" role="button" 
                                   data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-gear"></i> Administración
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown">
                                    <li>
                                        <a class="dropdown-item" href="<?= htmlspecialchars(BASE_URL) ?>/modulos/usuarios/listar_usuarios.php">
                                            <i class="bi bi-people"></i> Gestión de Usuarios
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= htmlspecialchars(BASE_URL) ?>/modulos/personal/listar_personal.php">
                                            <i class="bi bi-person-lines-fill"></i> Gestión de Personal
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= htmlspecialchars(BASE_URL) ?>/modulos/vehiculos/listar_vehiculos.php">
                                            <i class="bi bi-truck"></i> Gestión de Vehiculos
                                        </a>
                                    </li>
                                        <a class="dropdown-item" href="<?= htmlspecialchars(BASE_URL) ?>/modulos/roles/listar_roles.php">
                                            <i class="bi bi-person-badge"></i> Gestión de Roles
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php endif; ?>
                        
                        <li class="nav-item">
                            <a class="btn btn-danger btn-sm rounded-pill fw-bold px-3 ms-1 me-2 my-1" 
                                href="<?= htmlspecialchars(BASE_URL) ?>/modulos/auth/logout.php">
                                <i class="bi bi-power me-1"></i> Salir
                            </a>
                        </li>
                        
                    <?php else: ?>
                        <!-- Menú para invitados -->
                        <li class="nav-item">
                            <a class="nav-link <?= $currentPage === 'login.php' ? 'active' : '' ?>" 
                               href="<?= htmlspecialchars(BASE_URL) ?>/modulos/auth/login.php">
                               <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentPage === 'registro.php' ? 'active' : '' ?>" 
                               href="<?= htmlspecialchars(BASE_URL) ?>/modulos/auth/registro.php">
                               <i class="bi bi-person-plus"></i> Registrarse
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Bootstrap JS Bundle con Popper -->
    <script src="<?= htmlspecialchars(BASE_URL) ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>