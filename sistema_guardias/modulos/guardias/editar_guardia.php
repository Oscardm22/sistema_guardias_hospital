<?php
require_once "../../includes/conexion.php";

if (!isset($_GET['id'])) {
    header("Location: listar_guardias.php");
    exit;
}

$id_guardia = $_GET['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fecha = $_POST['fecha'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin = $_POST['hora_fin'];
    $tipo_guardia = $_POST['tipo_guardia'];
    $id_responsable = $_POST['id_responsable'];

    $sql = "UPDATE guardias SET 
            fecha_guardia = ?, 
            hora_inicio = ?, 
            hora_fin = ?, 
            tipo_guardia = ?, 
            id_responsable = ? 
            WHERE id_guardia = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssii", $fecha, $hora_inicio, $hora_fin, $tipo_guardia, $id_responsable, $id_guardia);
    
    if ($stmt->execute()) {
        header("Location: listar_guardias.php?success=1");
    } else {
        echo "Error: " . $conn->error;
    }
}

// Obtener datos actuales de la guardia
$sql = "SELECT * FROM guardias WHERE id_guardia = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_guardia);
$stmt->execute();
$guardia = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Guardia</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Editar Guardia</h2>
    <form action="editar_guardia.php?id=<?= $id_guardia ?>" method="post">
        <div class="mb-3">
            <label for="fecha" class="form-label">Fecha</label>
            <input type="date" class="form-control" id="fecha" name="fecha" 
                   value="<?= htmlspecialchars($guardia['fecha_guardia']) ?>" required>
        </div>
        <div class="row mb-3">
            <div class="col">
                <label for="hora_inicio" class="form-label">Hora Inicio</label>
                <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" 
                       value="<?= htmlspecialchars($guardia['hora_inicio']) ?>" required>
            </div>
            <div class="col">
                <label for="hora_fin" class="form-label">Hora Fin</label>
                <input type="time" class="form-control" id="hora_fin" name="hora_fin" 
                       value="<?= htmlspecialchars($guardia['hora_fin']) ?>" required>
            </div>
        </div>
        <div class="mb-3">
            <label for="tipo_guardia" class="form-label">Tipo de Guardia</label>
            <select class="form-select" id="tipo_guardia" name="tipo_guardia" required>
                <option value="Diurna" <?= $guardia['tipo_guardia'] == 'Diurna' ? 'selected' : '' ?>>Diurna</option>
                <option value="Nocturna" <?= $guardia['tipo_guardia'] == 'Nocturna' ? 'selected' : '' ?>>Nocturna</option>
                <option value="Mixta" <?= $guardia['tipo_guardia'] == 'Mixta' ? 'selected' : '' ?>>Mixta</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="id_responsable" class="form-label">Responsable</label>
            <select class="form-select" id="id_responsable" name="id_responsable" required>
                <?php
                $sql_personal = "SELECT id_personal, nombre, grado FROM personal WHERE estado = 1";
                $result = $conn->query($sql_personal);
                while ($row = $result->fetch_assoc()) {
                    $selected = ($row['id_personal'] == $guardia['id_responsable']) ? 'selected' : '';
                    echo "<option value='{$row['id_personal']}' $selected>{$row['grado']} {$row['nombre']}</option>";
                }
                ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="listar_guardias.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
</body>
</html>