<?php
require_once "../../includes/conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_guardia'])) {
    $id = $_POST['id_guardia'];
    $sql = "DELETE FROM guardias WHERE id_guardia = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => $conn->error]);
    }
    exit;
}
?>