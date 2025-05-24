<?php
require_once "../../includes/conexion.php";
require_once "../../includes/auth.php";
require_once "../../includes/funciones/funciones_autenticacion.php";
require_once "../../includes/funciones/funciones_servicios.php";

// Verificar autenticación y permisos
if (!es_admin()) {
    header("Location: listar_servicios.php?error=permisos");
    exit;
}

// Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: listar_servicios.php?error=metodo_invalido");
    exit;
}

// Obtener y validar ID del servicio
$id_servicio = filter_input(INPUT_POST, 'id_servicio', FILTER_VALIDATE_INT);
if ($id_servicio <= 0) {
    header("Location: listar_servicios.php?error=id_invalido");
    exit;
}

// Función para sanitizar strings de forma segura
function sanitize_string($input) {
    return htmlspecialchars(strip_tags(trim($input ?? '')), ENT_QUOTES, 'UTF-8');
}

// Obtener y sanitizar datos del formulario
$tipo = sanitize_string($_POST['tipo'] ?? '');
$medida = filter_input(INPUT_POST, 'medida', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$unidad = sanitize_string($_POST['unidad'] ?? '');
$observaciones = sanitize_string($_POST['observaciones'] ?? '');
$responsable = filter_input(INPUT_POST, 'responsable', FILTER_VALIDATE_INT) ?: null; // NULL si está vacío

// Validaciones
$errores = [];

// Validar tipo de servicio
if (!in_array($tipo, ['agua', 'combustible'])) {
    $errores[] = 'tipo_invalido';
}

// Validar medida
if (!is_numeric($medida)) {
    $errores[] = 'medida_no_numerica';
} elseif ($medida <= 0) {
    $errores[] = 'medida_invalida';
}

// Validar unidad
$unidades_permitidas = ['porcentaje', 'litros', 'galones'];
if (!in_array($unidad, $unidades_permitidas)) {
    $errores[] = 'unidad_invalida';
}

// Validación específica para porcentaje
if ($unidad === 'porcentaje' && ($medida < 0 || $medida > 100)) {
    $errores[] = 'porcentaje_invalido';
}

// Validar responsable (campo obligatorio)
if (!$responsable || $responsable <= 0) {
    $errores[] = 'responsable_requerido';
} else {
    $stmt = $conn->prepare("SELECT 1 FROM personal WHERE id_personal = ? AND estado = 1");
    $stmt->bind_param("i", $responsable);
    $stmt->execute();
    
    if (!$stmt->get_result()->num_rows) {
        $errores[] = 'responsable_invalido';
    }
    $stmt->close();
}

// Manejar errores
if (!empty($errores)) {
    $query = http_build_query(['id' => $id_servicio, 'errores' => $errores]);
    header("Location: editar_servicio.php?$query");
    exit;
}

// Actualizar el servicio en la base de datos
try {
    $sql = "UPDATE servicios SET 
            tipo = ?, 
            medida = ?, 
            unidad = ?, 
            observaciones = ?, 
            responsable = ? 
            WHERE id_servicio = ?";
    
    $stmt = $conn->prepare($sql);
    
    // Manejo de NULL para responsable
    if ($responsable === null) {
        $stmt->bind_param("sdssii", $tipo, $medida, $unidad, $observaciones, $responsable, $id_servicio);
    } else {
        $stmt->bind_param("sdssii", $tipo, $medida, $unidad, $observaciones, $responsable, $id_servicio);
    }
    
    if ($stmt->execute()) {
        header("Location: listar_servicios.php?success=actualizacion_exitosa");
    } else {
        throw new Exception("Error al ejecutar la consulta");
    }
} catch (Exception $e) {
    error_log("Error al actualizar servicio (ID: $id_servicio): " . $e->getMessage() . "\nConsulta: " . $sql);
    header("Location: editar_servicio.php?id=$id_servicio&error=error_bd");
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
}