<?php
require_once "../../includes/conexion.php";
require_once "../../includes/auth.php";
require_once "../../includes/funciones.php";

// Solo admin puede eliminar guardias
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !es_admin()) {
    header("Location: listar_guardias.php?error=no_permiso");
    exit;
}

// Validar y sanitizar el ID
$id_guardia = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id_guardia) {
    header("Location: listar_guardias.php?error=id_invalido");
    exit;
}

// Verificar existencia antes de eliminar
$sql_verificar = "SELECT id_guardia FROM guardias WHERE id_guardia = ?";
$stmt = $conn->prepare($sql_verificar);
$stmt->bind_param("i", $id_guardia);
$stmt->execute();

if ($stmt->get_result()->num_rows === 0) {
    header("Location: listar_guardias.php?error=guardia_no_existe");
    exit;
}

// Eliminar la guardia
$sql_eliminar = "DELETE FROM guardias WHERE id_guardia = ?";
$stmt = $conn->prepare($sql_eliminar);
$stmt->bind_param("i", $id_guardia);

if ($stmt->execute()) {
    header("Location: listar_guardias.php?success=guardia_eliminada");
} else {
    header("Location: listar_guardias.php?error=eliminacion_fallida");
}
exit;
?>