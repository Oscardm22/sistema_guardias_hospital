<?php
require_once __DIR__.'/../../includes/conexion.php';
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/funciones/funciones_autenticacion.php';
require_once __DIR__.'/../../includes/funciones/funciones_guardias.php';

// Establecer cabeceras para respuesta JSON
header('Content-Type: application/json');

// Verificar si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Método no permitido
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Verificar permisos
if (!puede_editar_guardia()) {
    http_response_code(403); // Prohibido
    echo json_encode(['success' => false, 'message' => 'No tienes permisos para esta acción']);
    exit;
}

// Obtener y validar ID de la asignación
$id_asignacion = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id_asignacion || $id_asignacion <= 0) {
    http_response_code(400); // Solicitud incorrecta
    echo json_encode(['success' => false, 'message' => 'ID de asignación no válido']);
    exit;
}

// Iniciar transacción para mayor seguridad
$conn->begin_transaction();

try {
    // 1. Verificar que la asignación existe
    $stmt_check = $conn->prepare("SELECT id_asignacion FROM asignaciones_guardia WHERE id_asignacion = ?");
    $stmt_check->bind_param("i", $id_asignacion);
    $stmt_check->execute();
    $stmt_check->store_result();
    
    if ($stmt_check->num_rows === 0) {
        throw new Exception("La asignación no existe");
    }
    
    // 2. Eliminar la asignación
    $stmt_delete = $conn->prepare("DELETE FROM asignaciones_guardia WHERE id_asignacion = ?");
    $stmt_delete->bind_param("i", $id_asignacion);
    
    if (!$stmt_delete->execute()) {
        throw new Exception("Error al ejecutar la eliminación");
    }
    
    // Confirmar cambios
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Asignación eliminada correctamente'
    ]);
    
} catch (Exception $e) {
    // Revertir cambios en caso de error
    $conn->rollback();
    
    // Registrar error para depuración
    error_log("Error al eliminar asignación ID {$id_asignacion}: " . $e->getMessage());
    
    http_response_code(500); // Error interno del servidor
    echo json_encode([
        'success' => false,
        'message' => 'Error al eliminar la asignación: ' . $e->getMessage()
    ]);
}