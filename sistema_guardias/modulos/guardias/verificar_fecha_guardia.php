<?php
require_once "../../includes/conexion.php";

header('Content-Type: application/json');

if (!isset($_GET['fecha'])) {
    echo json_encode(['existe' => false]);
    exit;
}

$fecha = $_GET['fecha'];

$sql = "SELECT id_guardia FROM guardias WHERE fecha = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $fecha);
$stmt->execute();
$result = $stmt->get_result();

echo json_encode(['existe' => $result->num_rows > 0]);
?>