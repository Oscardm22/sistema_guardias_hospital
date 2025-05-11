<?php
require_once "../../includes/conexion.php";
require_once "../../includes/auth.php"; // Verifica sesión

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Datos del formulario (nombres de columnas correctos)
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $tipo_guardia = $_POST['tipo_guardia'];
    $id_personal_entrega = $_POST['id_personal_entrega'];
    $id_personal_recibe = $_POST['id_personal_recibe'];

    // Consulta SQL actualizada
    $sql = "INSERT INTO guardias (fecha_inicio, fecha_fin, tipo_guardia, id_personal_entrega, id_personal_recibe) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssii", $fecha_inicio, $fecha_fin, $tipo_guardia, $id_personal_entrega, $id_personal_recibe);
    
    if ($stmt->execute()) {
        header("Location: listar_guardias.php?success=1");
    } else {
        echo '<div class="alert alert-danger">Error: ' . $conn->error . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Guardia</title>
    <!-- Bootstrap CSS -->
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Estilos personalizados -->
    <link href="../../assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Barra de navegación (como en tu index.php) -->
    <?php include __DIR__ . "/../../includes/navbar.php"; ?>

    <!-- Contenedor principal -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-calendar-plus"></i> Nueva Guardia
                        </h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <!-- Fecha y hora de inicio -->
                            <div class="mb-3">
                                <label for="fecha_inicio" class="form-label">
                                    <i class="bi bi-clock"></i> Fecha/Hora Inicio
                                </label>
                                <input type="datetime-local" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                            </div>

                            <!-- Fecha y hora de fin -->
                            <div class="mb-3">
                                <label for="fecha_fin" class="form-label">
                                    <i class="bi bi-clock-fill"></i> Fecha/Hora Fin
                                </label>
                                <input type="datetime-local" class="form-control" id="fecha_fin" name="fecha_fin" required>
                            </div>

                            <!-- Tipo de guardia -->
                            <div class="mb-3">
                                <label for="tipo_guardia" class="form-label">
                                    <i class="bi bi-list-check"></i> Tipo de Guardia
                                </label>
                                <select class="form-select" id="tipo_guardia" name="tipo_guardia" required>
                                    <option value="Diurna">Diurna</option>
                                    <option value="Nocturna">Nocturna</option>
                                </select>
                            </div>

                            <!-- Personal que entrega -->
                            <div class="mb-3">
                                <label for="id_personal_entrega" class="form-label">
                                    <i class="bi bi-person-up"></i> Personal que Entrega
                                </label>
                                <select class="form-select" id="id_personal_entrega" name="id_personal_entrega" required>
                                    <?php
                                    $query = "SELECT id_personal, nombre, grado FROM personal WHERE estado = 1";
                                    $result = $conn->query($query);
                                    while ($row = $result->fetch_assoc()) {
                                        echo '<option value="' . $row['id_personal'] . '">' . $row['grado'] . ' ' . $row['nombre'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Personal que recibe -->
                            <div class="mb-3">
                                <label for="id_personal_recibe" class="form-label">
                                    <i class="bi bi-person-down"></i> Personal que Recibe
                                </label>
                                <select class="form-select" id="id_personal_recibe" name="id_personal_recibe" required>
                                    <?php
                                    $query = "SELECT id_personal, nombre, grado FROM personal WHERE estado = 1";
                                    $result = $conn->query($query);
                                    while ($row = $result->fetch_assoc()) {
                                        echo '<option value="' . $row['id_personal'] . '">' . $row['grado'] . ' ' . $row['nombre'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Botones -->
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="listar_guardias.php" class="btn btn-secondary me-md-2">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Guardar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>