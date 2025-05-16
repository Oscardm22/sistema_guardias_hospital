<?php
require_once __DIR__.'/../../includes/conexion.php';
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/funciones/funciones_novedades.php';
require_once __DIR__.'/../../includes/funciones/funciones_autenticacion.php';

// Verificar si se ha pasado el ID de la novedad
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No se especificó la novedad a eliminar";
    header('Location: listar_novedades.php');
    exit;
}

$id_novedad = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Obtener la novedad para verificar que existe
$novedad = obtener_novedad($id_novedad, $conn);

if (!$novedad) {
    $_SESSION['error'] = "Novedad no encontrada";
    header('Location: listar_novedades.php');
    exit;
}

// Verificar si el usuario tiene permisos para eliminar esta novedad
if (!puede_eliminar_novedad($novedad['id_novedad'], obtener_id_personal_usuario(), $conn)) {
    $_SESSION['error'] = "No tienes permisos para esta acción";
    header('Location: listar_novedades.php');
    exit;
}

// Eliminar la novedad
$resultado = eliminar_novedad_segura($id_novedad, obtener_id_personal_usuario(), $conn);

if ($resultado['success']) {
    $_SESSION['exito'] = "Novedad eliminada correctamente";
} else {
    $_SESSION['error'] = "Error al eliminar la novedad: " . $resultado['message'];
}

header('Location: listar_novedades.php');
exit;