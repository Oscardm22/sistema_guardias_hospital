<?php
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/funciones/funciones_ordenes.php';
require_once __DIR__ . '/../../includes/funciones/funciones_autenticacion.php';

if (!es_admin()) {
    $_SESSION['error_ordenes'] = "No tienes permisos para esta acción";
    header('Location: listar_ordenes.php');
    exit;
}

// Verificar que se reciba el ID de la orden
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_ordenes'] = "ID de orden no válido";
    header('Location: listar_ordenes.php');
    exit;
}

$id_orden = (int)$_GET['id'];

// Verificar que la orden existe
$orden = obtenerOrdenPorId($id_orden);
if (!$orden) {
    $_SESSION['error_ordenes'] = "La orden no existe o ya fue eliminada";
    header('Location: listar_ordenes.php');
    exit;
}

// Eliminar la orden
if (eliminarOrden($id_orden)) {
    $_SESSION['exito_ordenes'] = "Orden eliminada correctamente";
} else {
    $_SESSION['error_ordenes'] = "Error al eliminar la orden";
}

header('Location: listar_ordenes.php');
exit;
?>