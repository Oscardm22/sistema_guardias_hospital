<?php
require_once "../../includes/conexion.php";
require_once "../../includes/auth.php";
require_once "../../includes/funciones.php";

// Solo admin puede acceder
if (!es_admin()) {
    header("Location: listar_guardias.php?error=no_permiso");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: listar_guardias.php");
    exit;
}

$id_guardia = $_GET['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar y limpiar datos
    $fecha = $_POST['fecha'];
    $tipo_guardia = $_POST['tipo_guardia'];
    $id_personal_entrega = (int)$_POST['id_personal_entrega'];
    $id_personal_recibe = (int)$_POST['id_personal_recibe'];
    
    // Validar que no sea la misma persona
    if ($id_personal_entrega === $id_personal_recibe) {
        $error = "El personal que entrega no puede ser el mismo que recibe la guardia";
    } else {
        $sql = "UPDATE guardias SET 
                fecha_inicio = ?, 
                fecha_fin = ?, 
                tipo_guardia = ?, 
                id_personal_entrega = ?, 
                id_personal_recibe = ? 
                WHERE id_guardia = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssii", $fecha, $fecha, $tipo_guardia, $id_personal_entrega, $id_personal_recibe, $id_guardia);
        
        if ($stmt->execute()) {
            header("Location: listar_guardias.php?success=1");
            exit();
        } else {
            $error = "Error al actualizar la guardia: " . $conn->error;
        }
    }
}

// Obtener datos actuales de la guardia
$sql = "SELECT g.*, 
        p_entrega.nombre as nombre_entrega, p_entrega.grado as grado_entrega,
        p_recibe.nombre as nombre_recibe, p_recibe.grado as grado_recibe
        FROM guardias g
        LEFT JOIN personal p_entrega ON g.id_personal_entrega = p_entrega.id_personal
        LEFT JOIN personal p_recibe ON g.id_personal_recibe = p_recibe.id_personal
        WHERE g.id_guardia = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_guardia);
$stmt->execute();
$guardia = $stmt->get_result()->fetch_assoc();

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
    <title>Editar Guardia de 24 Horas</title>
    <!-- Favicon -->
    <link rel="icon" href="../../assets/images/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include __DIR__ . "/../../includes/navbar.php"; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-calendar-plus"></i> Editar Guardia de 24 Horas
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
                                       value="<?= htmlspecialchars($guardia['fecha_inicio']) ?>">
                            </div>

                            <!-- Tipo de guardia -->
                            <div class="mb-3">
                                <label for="tipo_guardia" class="form-label">
                                    <i class="bi bi-list-check"></i> Tipo de Guardia
                                </label>
                                <select class="form-select" id="tipo_guardia" name="tipo_guardia" required>
                                    <option value="diurna" <?= $guardia['tipo_guardia'] == 'diurna' ? 'selected' : '' ?>>Diurna</option>
                                    <option value="nocturna" <?= $guardia['tipo_guardia'] == 'nocturna' ? 'selected' : '' ?>>Nocturna</option>
                                </select>
                            </div>

                            <!-- Personal que entrega -->
                            <div class="mb-3">
                                <label for="id_personal_entrega" class="form-label">
                                    <i class="bi bi-person-up"></i> Personal que Entrega
                                </label>
                                <select class="form-select" id="id_personal_entrega" name="id_personal_entrega" required>
                                    <?php foreach ($personal as $p): ?>
                                        <option value="<?= $p['id_personal'] ?>" 
                                            <?= $p['id_personal'] == $guardia['id_personal_entrega'] ? 'selected' : '' ?>>
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
                                        <option value="<?= $p['id_personal'] ?>"
                                            <?= $p['id_personal'] == $guardia['id_personal_recibe'] ? 'selected' : '' ?>
                                            <?= $p['id_personal'] == $guardia['id_personal_entrega'] ? 'disabled' : '' ?>>
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
                                    <i class="bi bi-save"></i> Actualizar Guardia
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
            
            // Función para actualizar las opciones disponibles en "Recibe"
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