<?php
// sistema_guardias/includes/navbar.php
session_start();
require_once __DIR__ . '/config.php'; // Asegúrate de que este archivo existe y define BASE_URL
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">
            <img src="<?= BASE_URL ?>/assets/images/logo_hospital.png" alt="Logo" width="40" height="40" class="d-inline-block align-top">
            Guardias Hospitalarias
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="<?= BASE_URL ?>/index.php">Inicio</a>
                </li>

                <?php if (isset($_SESSION['usuario'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/modulos/guardias/listar_guardias.php">Guardias</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/modulos/novedades/listar_novedades.php">Novedades</a>
                    </li>
                    <?php if ($_SESSION['rol'] == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/admin/usuarios/">
                                <i class="bi bi-people"></i> Usuarios
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="<?= BASE_URL ?>/modulos/auth/logout.php">
                            <i class="bi bi-box-arrow-right"></i> Salir
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/modulos/auth/login.php">
                            <i class="bi bi-box-arrow-in-right"></i> Ingresar
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>