<?php
require_once __DIR__.'/../../includes/conexion.php';
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/funciones/funciones_novedades.php';
require_once __DIR__.'/../../includes/funciones/funciones_autenticacion.php';

// Verificar si se ha pasado el ID de la novedad
if (!isset($_GET['id'])) {
    $_SESSION['error'] = [
        'titulo' => 'Error',
        'mensaje' => 'No se especificó la novedad a eliminar',
        'tipo' => 'danger'
    ];
    header('Location: listar_novedades.php');
    exit;
}

$id_novedad = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Obtener la novedad para verificar que existe
$novedad = obtener_novedad($conn, $id_novedad);

if (!$novedad) {
    $_SESSION['error'] = [
        'titulo' => 'No encontrado',
        'mensaje' => 'La novedad solicitada no existe',
        'tipo' => 'warning'
    ];
    header('Location: listar_novedades.php');
    exit;
}

// Verificar permisos
if (!puede_eliminar_novedad($novedad['id_novedad'], obtener_id_personal_usuario(), $conn)) {
    $_SESSION['error'] = [
        'titulo' => 'Acceso denegado',
        'mensaje' => 'No tienes permisos para esta acción',
        'tipo' => 'danger'
    ];
    header('Location: listar_novedades.php');
    exit;
}

// Eliminar la novedad
$resultado = eliminar_novedad_segura($id_novedad, obtener_id_personal_usuario(), $conn);

if ($resultado['success']) {
    $_SESSION['exito'] = [
        'titulo' => '¡Eliminación exitosa!',
        'mensaje' => 'La novedad ha sido eliminada correctamente',
        'tipo' => 'success'
    ];
} else {
    $_SESSION['error'] = [
        'titulo' => 'Error',
        'mensaje' => 'No se pudo eliminar la novedad: ' . $resultado['message'],
        'tipo' => 'danger'
    ];
}

// Redirigir siempre a listar_novedades.php después de eliminar
header('Location: listar_novedades.php');
exit;