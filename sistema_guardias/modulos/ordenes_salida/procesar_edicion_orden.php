<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/funciones/funciones_ordenes.php';
require_once __DIR__ . '/../../includes/funciones/funciones_autenticacion.php';

if (!es_admin() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /index.php');
    exit;
}

// Validar que se reciba el ID de la orden
if (!isset($_POST['id_orden']) || !is_numeric($_POST['id_orden'])) {
    header('Location: listar_ordenes.php');
    exit;
}

$id_orden = (int)$_POST['id_orden'];

// Validar y sanitizar datos
$datos = [
    'id_orden' => $id_orden,
    'destino' => trim($_POST['destino']),
    'motivo' => trim($_POST['motivo']),
    'fecha_salida' => $_POST['fecha_salida'],
    'fecha_retorno' => !empty($_POST['fecha_retorno']) ? $_POST['fecha_retorno'] : null,
    'id_vehiculo' => (int)$_POST['id_vehiculo'],
    'id_personal' => (int)$_POST['id_personal']
];

// Validaciones básicas
if (empty($datos['destino']) || empty($datos['motivo']) || empty($datos['fecha_salida'])) {
    header('Location: editar_orden.php?id=' . $id_orden . '&error=campos_requeridos');
    exit;
}

// Validación de fecha de retorno
if ($datos['fecha_retorno'] && $datos['fecha_retorno'] < $datos['fecha_salida']) {
    header('Location: editar_orden.php?id=' . $id_orden . '&error=fechas_invalidas');
    exit;
}

// Actualizar en base de datos
// En procesar_edicion_orden.php
if (actualizarOrden($id_orden, $datos)) {
    $_SESSION['exito_ordenes'] = "Orden de salida actualizada correctamente";
    header('Location: listar_ordenes.php?success=orden_actualizada&timestamp='.time());
    exit;
} else {
    $_SESSION['error_ordenes'] = "Error al actualizar la orden de salida";
    header('Location: editar_orden.php?id='.$id_orden);
    exit;
}
exit;
?>