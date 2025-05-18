<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/funciones/funciones_personal.php';
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/../../includes/funciones/funciones_autenticacion.php';

if (!es_admin()) {
    header('HTTP/1.0 403 Forbidden');
    exit('Acceso restringido a administradores');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar_personal.php');
    exit;
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    header('Location: listar_personal.php');
    exit;
}

try {
    // Primero verificamos si el personal está asignado a alguna guardia
    $query = "SELECT COUNT(*) FROM asignaciones_guardia WHERE id_personal = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $_POST['id']);
    $stmt->execute();
    $stmt->bind_result($asignaciones);
    $stmt->fetch();
    $stmt->close();
    
    if ($asignaciones > 0) {
        $_SESSION['error'] = 'No se puede eliminar, el personal tiene guardias asignadas';
        header('Location: listar_personal.php');
        exit;
    }
    
    // Si no tiene asignaciones, procedemos a eliminar
    $resultado = PersonalFunciones::eliminarPersonal($conn, $_POST['id']);
    
    if ($resultado) {
        $_SESSION['exito'] = 'Personal eliminado correctamente';
    } else {
        $_SESSION['error'] = 'Ocurrió un error al eliminar el personal';
    }
} catch (Exception $e) {
    error_log("Error en proceso_eliminar_personal.php: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la eliminación';
}

header('Location: listar_personal.php');
exit;