<?php
require_once "../../includes/conexion.php";
require_once "../../includes/auth.php";
require_once "../../includes/funciones/funciones_autenticacion.php";
require_once "../../includes/funciones/funciones_vehiculos.php";

if (!es_admin()) {
    $_SESSION['error'] = "No tienes permisos para esta acción";
    header("Location: listar_vehiculos.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: listar_vehiculos.php");
    exit;
}

$id_vehiculo = intval($_GET['id']);
$vehiculo = obtenerVehiculoPorId($conn, $id_vehiculo);

if (!$vehiculo) {
    header("Location: listar_vehiculos.php");
    exit;
}

$titulo_pagina = "Editar Vehículo";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titulo_pagina); ?> - Sistema de Guardias</title>
    <link rel="icon" href="../../assets/images/favicon.ico" type="image/x-icon">
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .card-shadow {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border: none;
            border-radius: 0.5rem;
        }
        .form-section {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .form-title {
            color: #2c3e50;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .btn-submit {
            padding: 0.5rem 1.5rem;
        }
        .input-group-text {
            min-width: 45px;
            justify-content: center;
        }
        .is-invalid {
            border-color: #dc3545;
        }
        .is-valid {
            border-color: #28a745;
        }
        .invalid-feedback {
            color: #dc3545;
            font-size: 0.875em;
        }
    </style>
</head>
<body class="bg-light">
    <?php include "../../includes/navbar.php"; ?>

    <div class="container py-4">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0 text-primary">
                <i class="fas fa-car me-2"></i><?php echo htmlspecialchars($titulo_pagina); ?>
            </h2>
            <a href="listar_vehiculos.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Volver al listado
            </a>
        </div>

        <!-- Mensajes de error -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Tarjeta contenedora del formulario -->
        <div class="card card-shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-id-card me-2"></i> Información del Vehículo</h5>
            </div>
            
            <div class="card-body">
                <form action="proceso_guardar_vehiculo.php" method="post" id="formVehiculo">
                    <input type="hidden" name="id_vehiculo" value="<?= $vehiculo['id_vehiculo'] ?>">
                    
                    <!-- Sección de información básica -->
                    <div class="form-section">
                        <h5 class="form-title"><i class="fas fa-car me-2"></i>Datos Principales</h5>
                        
                        <div class="mb-3">
                            <label for="placa" class="form-label">Placa <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-car"></i></span>
                                <input type="text" class="form-control" id="placa" name="placa" 
                                    maxlength="20" pattern="[A-Z0-9-]+"
                                    value="<?= htmlspecialchars($vehiculo['placa']) ?>"
                                    placeholder="Ej: ABC-123" required>
                            </div>
                            <div id="placa-error" class="invalid-feedback d-none">
                                Por favor ingrese una placa válida (solo letras, números y guiones)
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="marca" class="form-label">Marca <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                <input type="text" class="form-control" id="marca" name="marca" 
                                       maxlength="50" value="<?= htmlspecialchars($vehiculo['marca']) ?>"
                                       placeholder="Ej: Toyota, Ford, Chevrolet" required>
                            </div>
                            <div id="marca-error" class="invalid-feedback d-none">
                                Por favor ingrese la marca del vehículo
                            </div>
                        </div>
                    </div>

                    <!-- Sección de información adicional -->
                    <div class="form-section">
                        <h5 class="form-title"><i class="fas fa-info-circle me-2"></i>Información Adicional</h5>
                        
                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo de Vehículo <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-car-side"></i></span>
                                <select class="form-select" id="tipo" name="tipo" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="ambulancia" <?= ($vehiculo['tipo'] ?? '') === 'ambulancia' ? 'selected' : '' ?>>Ambulancia</option>
                                    <option value="administrativo" <?= ($vehiculo['tipo'] ?? '') === 'administrativo' ? 'selected' : '' ?>>Administrativo</option>
                                </select>
                            </div>
                            <div id="tipo-error" class="invalid-feedback d-none">
                                Por favor seleccione el tipo de vehículo
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="combustible" class="form-label">Nivel de Combustible <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-gas-pump"></i></span>
                                <select class="form-select" id="combustible" name="combustible" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="lleno" <?= ($vehiculo['combustible'] ?? '') === 'lleno' ? 'selected' : '' ?>>Lleno</option>
                                    <option value="3/4" <?= ($vehiculo['combustible'] ?? '') === '3/4' ? 'selected' : '' ?>>3/4 de tanque</option>
                                    <option value="medio" <?= ($vehiculo['combustible'] ?? '') === 'medio' ? 'selected' : '' ?>>Medio tanque</option>
                                    <option value="1/4" <?= ($vehiculo['combustible'] ?? '') === '1/4' ? 'selected' : '' ?>>1/4 de tanque</option>
                                    <option value="reserva" <?= ($vehiculo['combustible'] ?? '') === 'reserva' ? 'selected' : '' ?>>Reserva</option>
                                    <option value="vacio" <?= ($vehiculo['combustible'] ?? '') === 'vacio' ? 'selected' : '' ?>>Vacío</option>
                                </select>
                            </div>
                            <div id="combustible-error" class="invalid-feedback d-none">
                                Por favor seleccione el nivel de combustible
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="operativo" name="operativo" 
                                   <?= ($vehiculo['operativo'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="operativo">Vehículo operativo</label>
                        </div>
                        <small class="text-muted">Desactive si el vehículo no está disponible para uso</small>
                    </div>

                    <!-- Botones de acción -->
                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary btn-submit me-3">
                            <i class="fas fa-save me-2"></i> Guardar Cambios
                        </button>
                        <a href="listar_vehiculos.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include "../../includes/footer.php"; ?>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validación mejorada
        document.getElementById('formVehiculo').addEventListener('submit', function(e) {
            e.preventDefault();
            
            let isValid = true;
            const campos = [
                {id: 'placa', errorId: 'placa-error', validacion: (valor) => valor.trim() !== '' && /^[A-Z0-9-]+$/i.test(valor)},
                {id: 'marca', errorId: 'marca-error', validacion: (valor) => valor.trim() !== ''},
                {id: 'tipo', errorId: 'tipo-error', validacion: (valor) => valor !== ''},
                {id: 'combustible', errorId: 'combustible-error', validacion: (valor) => valor !== ''}
            ];

            // Validar cada campo
            campos.forEach(campo => {
                const elemento = document.getElementById(campo.id);
                const errorElement = document.getElementById(campo.errorId);
                const valor = elemento.value;
                
                if (!campo.validacion(valor)) {
                    isValid = false;
                    elemento.classList.add('is-invalid');
                    elemento.classList.remove('is-valid');
                    errorElement.classList.remove('d-none');
                } else {
                    elemento.classList.remove('is-invalid');
                    elemento.classList.add('is-valid');
                    errorElement.classList.add('d-none');
                }
            });

            // Si todo es válido, enviar formulario
            if(isValid) {
                this.submit();
            }
        });

        // Validación en tiempo real
        document.querySelectorAll('#marca, #tipo, #combustible').forEach(element => {
            element.addEventListener('change', function() {
                const value = this.value;
                let isValid = true;
                
                if (this.type === 'select-one') {
                    isValid = value !== '';
                } else {
                    isValid = value.trim() !== '';
                }
                
                if (isValid) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                    document.getElementById(`${this.id}-error`).classList.add('d-none');
                }
            });
        });
    </script>
</body>
</html>