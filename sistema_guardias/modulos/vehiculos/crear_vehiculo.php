<?php
require_once "../../includes/conexion.php";
require_once "../../includes/auth.php"; // Este ya incluye session_start()
require_once "../../includes/funciones/funciones_autenticacion.php";

if (!es_admin()) {
    $_SESSION['error'] = "No tienes permisos para acceder a esta función";
    header("Location: listar_vehiculos.php");
    exit;
}

$titulo_pagina = "Registrar Nuevo Vehículo";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titulo_pagina); ?> - Sistema de Guardias</title>
    <!-- Favicon -->
    <link rel="icon" href="../../assets/images/favicon.ico" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Estilos personalizados -->
    <link href="../../assets/css/styles_listar.css" rel="stylesheet">
    <style>
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-header {
            color: #fff;
            padding: 15px;
            border-radius: 5px 5px 0 0;
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: 600;
        }
        #placa {
            text-transform: uppercase;
        }
    </style>
</head>
<body class="bg-light">
    <?php include "../../includes/navbar.php"; ?>

    <div class="container py-4">
        <div class="form-container">
            <div class="bg-primary text-white form-header">
                <h3 class="mb-0"><i class="bi bi-car-front-fill me-2"></i> Registrar Nuevo Vehículo</h3>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="proceso_guardar_vehiculo.php" method="post">
                <div class="mb-3">
                    <label for="placa" class="form-label">Placa del Vehículo</label>
                    <input type="text" class="form-control" id="placa" name="placa" 
                           required maxlength="20" pattern="[A-Za-z0-9-]+"
                           placeholder="Ej: ABC-123">
                    <div class="invalid-feedback">
                        Por favor ingrese una placa válida (solo letras, números y guiones)
                    </div>
                </div>

                <div class="mb-3">
                    <label for="modelo" class="form-label">Modelo del Vehículo</label>
                    <input type="text" class="form-control" id="modelo" name="modelo" 
                           required maxlength="50"
                           placeholder="Ej: Toyota Hilux 2023">
                    <div class="invalid-feedback">
                        Por favor ingrese el modelo del vehículo
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <button type="submit" class="btn btn-primary me-md-2">
                        <i class="bi bi-save me-2"></i> Guardar
                    </button>
                    <a href="listar_vehiculos.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-2"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php include "../../includes/footer.php"; ?>

    <!-- Bootstrap Bundle with Popper -->
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script para convertir placa a mayúsculas -->
    <script>
        document.getElementById('placa').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
        
        // Validación básica del formulario
        (function() {
            'use strict';
            var forms = document.querySelectorAll('form');
            Array.prototype.slice.call(forms)
                .forEach(function(form) {
                    form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
        })();
    </script>
</body>
</html>