<?php
require_once "../../includes/conexion.php";
require_once "../../includes/auth.php";
require_once "../../includes/funciones.php";

// Solo admin puede acceder
if (!es_admin()) {
    header("Location: listar_guardias.php?error=no_permiso");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar y limpiar datos
    $fecha = $_POST['fecha'];
    $tipo_guardia = $_POST['tipo_guardia'];
    $id_personal_entrega = (int)$_POST['id_personal_entrega'];
    $id_personal_recibe = (int)$_POST['id_personal_recibe'];

    // Validar que no sea la misma persona
    if ($id_personal_entrega === $id_personal_recibe) {
        $error = "El personal que entrega no puede ser el mismo que recibe la guardia";
    }
    
    // Validar que no exista ya una guardia para este personal en la misma fecha
    $sql_verificar = "SELECT id_guardia FROM guardias 
                     WHERE id_personal_entrega = ? AND fecha_inicio = ?";
    $stmt = $conn->prepare($sql_verificar);
    $stmt->bind_param("is", $id_personal_entrega, $fecha);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        $error = "Este personal ya tiene una guardia asignada para esta fecha";
    } else {
        // Insertar la nueva guardia (24 horas)
        $sql = "INSERT INTO guardias 
                (fecha_inicio, fecha_fin, tipo_guardia, id_personal_entrega, id_personal_recibe) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $fecha, $fecha, $tipo_guardia, $id_personal_entrega, $id_personal_recibe);
        
        if ($stmt->execute()) {
            header("Location: listar_guardias.php?success=1");
            exit();
        } else {
            $error = "Error al guardar la guardia: " . $conn->error;
        }
    }
}

// Obtener lista de personal activo
$query_personal = "SELECT id_personal, nombre, grado FROM personal WHERE estado = 1 ORDER BY nombre";
$result_personal = $conn->query($query_personal);
$personal = [];
while ($row = $result_personal->fetch_assoc()) {
    $personal[] = $row;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Guardia de 24 Horas</title>
    <!-- Favicon -->
    <link rel="icon" href="../../assets/images/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/styles_crear_guardias.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include __DIR__ . "/../../includes/navbar.php"; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-calendar-plus"></i> Nueva Guardia de 24 Horas
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <!-- Fecha de la guardia -->
                            <div class="mb-3">
                                <label for="fecha" class="form-label">
                                    <i class="bi bi-calendar"></i> Fecha de la Guardia
                                </label>
                                <input type="date" class="form-control" id="fecha" name="fecha" required
                                       min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>">
                            </div>

                            <!-- Tipo de guardia -->
                            <div class="mb-3">
                                <label for="tipo_guardia" class="form-label">
                                    <i class="bi bi-list-check"></i> Tipo de Guardia
                                </label>
                                <select class="form-select" id="tipo_guardia" name="tipo_guardia" required>
                                    <option value="diurna">Diurna</option>
                                    <option value="nocturna">Nocturna</option>
                                </select>
                            </div>

                            <!-- Personal que entrega -->
                            <div class="mb-3">
                                <label for="id_personal_entrega" class="form-label">
                                    <i class="bi bi-person-up"></i> Personal que Entrega
                                </label>
                                <select class="form-select" id="id_personal_entrega" name="id_personal_entrega" required>
                                    <?php foreach ($personal as $p): ?>
                                        <option value="<?= $p['id_personal'] ?>">
                                            <?= htmlspecialchars($p['grado'] . ' ' . $p['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Personal que recibe -->
                            <div class="mb-3">
                                <label for="id_personal_recibe" class="form-label">
                                    <i class="bi bi-person-down"></i> Personal que Recibe
                                </label>
                                <select class="form-select" id="id_personal_recibe" name="id_personal_recibe" required>
                                    <?php foreach ($personal as $p): ?>
                                        <option value="<?= $p['id_personal'] ?>">
                                            <?= htmlspecialchars($p['grado'] . ' ' . $p['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Botones -->
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="listar_guardias.php" class="btn btn-secondary me-md-2">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Guardar Guardia
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const selectEntrega = document.getElementById('id_personal_entrega');
                const selectRecibe = document.getElementById('id_personal_recibe');
                const form = document.querySelector('form');
                
                // Función para deshabilitar la opción seleccionada en entrega
                function actualizarOpcionesRecibe() {
                    const idEntrega = selectEntrega.value;
                    
                    // Habilitar todas las opciones primero
                    Array.from(selectRecibe.options).forEach(option => {
                        option.disabled = false;
                    });
                    
                    // Deshabilitar la opción seleccionada en entrega
                    if (idEntrega) {
                        const optionToDisable = selectRecibe.querySelector(`option[value="${idEntrega}"]`);
                        if (optionToDisable) {
                            optionToDisable.disabled = true;
                            
                            // Si el valor actual es el deshabilitado, cambiarlo
                            if (selectRecibe.value === idEntrega) {
                                // Buscar primera opción habilitada
                                for (let i = 0; i < selectRecibe.options.length; i++) {
                                    if (!selectRecibe.options[i].disabled) {
                                        selectRecibe.value = selectRecibe.options[i].value;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
                
                // Validación al enviar el formulario
                form.addEventListener('submit', function(e) {
                    if (selectEntrega.value === selectRecibe.value) {
                        e.preventDefault();
                        alert('Error: El personal que entrega no puede ser el mismo que recibe la guardia');
                        
                        // Mostrar feedback visual
                        selectRecibe.classList.add('is-invalid');
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'invalid-feedback';
                        errorDiv.textContent = 'Debe seleccionar un personal diferente al que entrega';
                        
                        // Limpiar feedback anterior
                        const existingFeedback = selectRecibe.nextElementSibling;
                        if (existingFeedback && existingFeedback.classList.contains('invalid-feedback')) {
                            existingFeedback.remove();
                        }
                        
                        selectRecibe.after(errorDiv);
                    }
                });
                
                // Inicializar y escuchar cambios
                actualizarOpcionesRecibe();
                selectEntrega.addEventListener('change', actualizarOpcionesRecibe);
            });
        </script>
</body>
</html>