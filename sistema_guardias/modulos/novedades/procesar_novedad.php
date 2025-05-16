<?php
require_once __DIR__.'/../../includes/conexion.php';
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/funciones/funciones_novedades.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar_novedades.php');
    exit;
}

// Validar y sanitizar datos
$datos = [
    'descripcion' => filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
    'area' => filter_input(INPUT_POST, 'area', FILTER_SANITIZE_STRING),
    'id_guardia' => filter_input(INPUT_POST, 'id_guardia', FILTER_VALIDATE_INT),
    'id_personal_reporta' => filter_input(INPUT_POST, 'id_personal_reporta', FILTER_VALIDATE_INT)
];

// Validaciones básicas
if (empty($datos['descripcion']) || !$datos['id_guardia'] || !$datos['id_personal_reporta'] || empty($datos['area'])) {
    $_SESSION['error'] = "Todos los campos son obligatorios";
    header('Location: registrar_novedad.php');
    exit;
}

// Ejecutar el registro
$resultado = registrar_novedad_segura($datos, $conn);

if ($resultado['success']) {
    $_SESSION['exito'] = $resultado['message'];
    header('Location: listar_novedades.php');
    exit;
} else {
    $_SESSION['error'] = $resultado['message'];
    header('Location: registrar_novedad.php');
    exit;
}