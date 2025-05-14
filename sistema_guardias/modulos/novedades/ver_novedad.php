<?php
require_once '../conexion.php';
require_once '../auth.php';
require_once '../navbar.php';

if (!isset($_GET['id'])) {
    header("Location: listar_novedades.php");
    exit();
}

$id_novedad = intval($_GET['id']);

$query = "SELECT n.*, p.nombre, p.apellido, g.fecha_inicio, g.tipo_guardia
          FROM novedades n
          JOIN personal p ON n.id_personal_reporta = p.id_personal
          LEFT JOIN guardias g ON n.id_guardia = g.id_guardia
          WHERE n.id_novedad = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_novedad);
$stmt->execute();
$novedad = $stmt->get_result()->fetch_assoc();

if (!$novedad) {
    $_SESSION['error'] = "Novedad no encontrada";
    header("Location: listar_novedades.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Novedad</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../novedades/css/styles_novedades.css">
</head>
<body>
    
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Detalle de Novedad</h2>
            <a href="listar_novedades.php" class="btn btn-secondary">Volver</a>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Información General</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Fecha de Registro:</strong>
                        <p><?= date('d/m/Y H:i', strtotime($novedad['fecha_registro'])) ?></p>
                    </div>
                    <div class="col-md-4">
                        <strong>Área:</strong>
                        <p><?= $novedad['area'] ?></p>
                    </div>
                    <div class="col-md-4">
                        <strong>Reportado por:</strong>
                        <p><?= $novedad['nombre'] . ' ' . $novedad['apellido'] ?></p>
                    </div>
                </div>
                
                <?php if ($novedad['fecha_inicio']): ?>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Guardia Relacionada:</strong>
                        <p><?= date('d/m/Y', strtotime($novedad['fecha_inicio'])) ?> - <?= $novedad['tipo_guardia'] ?></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <strong>Descripción:</strong>
                    <div class="border p-3 bg-light"><?= nl2br($novedad['descripcion']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>