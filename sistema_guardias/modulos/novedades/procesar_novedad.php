<?php
require_once __DIR__.'/../../includes/conexion.php';
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/funciones/funciones_autenticacion.php';
require_once __DIR__.'/../../includes/funciones/funciones_novedades.php';

if (!puede_crear_novedad()) {
    $_SESSION['error'] = [
        'titulo' => 'Error',
        'mensaje' => 'No tienes permisos para crear novedades',
        'tipo' => 'danger'
    ];
    header('Location: listar_novedades.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = [
        'titulo' => 'Error',
        'mensaje' => 'Método no permitido',
        'tipo' => 'danger'
    ];
    header('Location: registrar_novedad.php');
    exit;
}

$id_guardia = $_POST['id_guardia'] ?? 0;
$fecha_guardia = $_POST['guardia_date'] ?? '';
$id_personal_reporta = $_POST['id_personal_reporta'] ?? 0;
$novedades = $_POST['novedades'] ?? [];

if (empty($id_guardia) || empty($fecha_guardia) || empty($id_personal_reporta) || empty($novedades)) {
    $_SESSION['error'] = [
        'titulo' => 'Error',
        'mensaje' => 'Datos incompletos',
        'tipo' => 'danger'
    ];
    header('Location: registrar_novedad.php');
    exit;
}

// Iniciar transacción
$conn->begin_transaction();

try {
    foreach ($novedades as $novedad) {
        // Validar cada novedad
        if (empty($novedad['area']) || empty($novedad['hora']) || empty($novedad['descripcion'])) {
            throw new Exception("Todos los campos son requeridos para cada novedad");
        }
        
        // Combinar fecha de guardia con hora de novedad
        $fecha_completa = $fecha_guardia . ' ' . $novedad['hora'] . ':00';
        
        // Validar fecha/hora
        if (!DateTime::createFromFormat('Y-m-d H:i:s', $fecha_completa)) {
            throw new Exception("Fecha/hora inválida");
        }
        
        // Insertar novedad
        $stmt = $conn->prepare("INSERT INTO novedades 
            (id_guardia, id_personal_reporta, area, descripcion, fecha_registro) 
            VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", 
            $id_guardia,
            $id_personal_reporta,
            $novedad['area'],
            $novedad['descripcion'],
            $fecha_completa
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Error al registrar novedad: " . $conn->error);
        }
    }
    
    $conn->commit();
    $_SESSION['exito'] = [
        'titulo' => 'Éxito',
        'mensaje' => count($novedades) . " novedad(es) registrada(s) correctamente",
        'tipo' => 'success'
    ];
    header('Location: listar_novedades.php');
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = [
        'titulo' => 'Error',
        'mensaje' => $e->getMessage(),
        'tipo' => 'danger'
    ];
    header('Location: registrar_novedad.php');
}
exit;