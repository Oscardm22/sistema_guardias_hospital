<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/funciones/funciones_ordenes.php';
require_once __DIR__ . '/../../includes/funciones/funciones_autenticacion.php';

if (!es_admin() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /index.php');
    exit;
}

// Validar y sanitizar datos
$datos = [
    'destino' => trim($_POST['destino']),
    'motivo' => trim($_POST['motivo']),
    'fecha_salida' => $_POST['fecha_salida'],
    'fecha_retorno' => !empty($_POST['fecha_retorno']) ? $_POST['fecha_retorno'] : null,
    'id_vehiculo' => (int)$_POST['id_vehiculo'],
    'id_personal' => (int)$_POST['id_personal']
];

// Validaciones básicas
if (empty($datos['destino']) || empty($datos['motivo']) || empty($datos['fecha_salida'])) {
    $_SESSION['error'] = "Todos los campos obligatorios deben ser completados";
    header('Location: crear_orden.php');
    exit;
}

// Validación de fecha de retorno
if ($datos['fecha_retorno'] && $datos['fecha_retorno'] < $datos['fecha_salida']) {
    $_SESSION['error'] = "La fecha de retorno no puede ser anterior a la de salida";
    header('Location: crear_orden.php');
    exit;
}

if (crearOrdenSalida($datos)) {
    $_SESSION['exito_ordenes'] = "Orden de salida creada correctamente";
    header('Location: listar_ordenes.php?success=orden_creada');
    exit;
} else {
    $_SESSION['error_ordenes'] = "Error al crear la orden de salida";
    header('Location: crear_orden.php');
    exit;
}
exit;
?>