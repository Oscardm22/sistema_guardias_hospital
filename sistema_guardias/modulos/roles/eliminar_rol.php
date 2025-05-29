<?php
require_once "../../includes/conexion.php";
require_once "../../includes/auth.php";
require_once "../../includes/funciones.php";

if (!es_admin()) {
    header("Location: listar_roles.php?error=no_permiso");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: listar_roles.php?error=id_no_proporcionado");
    exit;
}

$id_rol = (int)$_GET['id'];

// Verificar si el rol está en uso
$sql = "SELECT 1 FROM asignaciones_guardia WHERE id_rol = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_rol);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    header("Location: listar_roles.php?error=rol_en_uso");
    exit;
}

// Eliminar el rol
$stmt = $conn->prepare("DELETE FROM roles_guardia WHERE id_rol = ?");
$stmt->bind_param("i", $id_rol);

if ($stmt->execute()) {
    header("Location: listar_roles.php?success=eliminado");
} else {
    header("Location: listar_roles.php?error=eliminacion_fallida");
}