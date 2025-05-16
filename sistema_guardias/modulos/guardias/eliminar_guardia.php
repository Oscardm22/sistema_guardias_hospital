<?php
require_once __DIR__.'/../../includes/conexion.php';
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/funciones/funciones_autenticacion.php';
require_once __DIR__.'/../../includes/funciones/funciones_guardias.php';

if (!puede_editar_guardia()) {
    $_SESSION['error'] = "No tienes permisos para esta acción";
    header('Location: listar_guardias.php');
    exit;
}

$id_guardia = $_GET['id'] ?? 0;

if ($id_guardia <= 0) {
    $_SESSION['error'] = "Guardia no especificada";
    header('Location: listar_guardias.php');
    exit;
}

// Iniciar transacción
$conn->begin_transaction();

try {
    // 1. Eliminar asignaciones
    $stmt = $conn->prepare("DELETE FROM asignaciones_guardia WHERE id_guardia = ?");
    $stmt->bind_param("i", $id_guardia);
    $stmt->execute();
    
    // 2. Eliminar la guardia
    $stmt = $conn->prepare("DELETE FROM guardias WHERE id_guardia = ?");
    $stmt->bind_param("i", $id_guardia);
    $stmt->execute();
    
    $conn->commit();
    $_SESSION['success'] = "Guardia eliminada correctamente";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Error al eliminar la guardia";
}

header('Location: listar_guardias.php');