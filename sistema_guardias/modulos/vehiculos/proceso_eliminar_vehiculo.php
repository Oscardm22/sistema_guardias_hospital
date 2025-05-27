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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: listar_vehiculos.php");
    exit;
}

if (!isset($_POST['id_vehiculo']) || !is_numeric($_POST['id_vehiculo'])) {
    header("Location: listar_vehiculos.php");
    exit;
}

$id_vehiculo = intval($_POST['id_vehiculo']);

try {
    // Verificar si el vehículo tiene órdenes de salida asociadas
    $stmt = $conexion->prepare("SELECT COUNT(*) FROM ordenes_salida WHERE id_vehiculo = ?");
    $stmt->bind_param("i", $id_vehiculo);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    
    if ($count > 0) {
        $_SESSION['error'] = "No se puede eliminar: el vehículo tiene órdenes de salida asociadas";
        header("Location: listar_vehiculos.php");
        exit;
    }
    
    // Eliminar el vehículo
    $stmt = $conexion->prepare("DELETE FROM vehiculos WHERE id_vehiculo = ?");
    $stmt->bind_param("i", $id_vehiculo);
    
    if ($stmt->execute()) {
        $_SESSION['exito_vehiculos'] = "Vehículo eliminado correctamente";
    } else {
        $_SESSION['error_vehiculos'] = "Error al eliminar el vehículo";
    }
    
    $stmt->close();
} catch (Exception $e) {
    $_SESSION['error_vehiculos'] = "Error en la base de datos: " . $e->getMessage();
}

header("Location: listar_vehiculos.php");
exit;