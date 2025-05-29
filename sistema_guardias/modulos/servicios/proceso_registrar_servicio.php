<?php
require_once "../../includes/conexion.php";
require_once "../../includes/auth.php";
require_once "../../includes/funciones/funciones_autenticacion.php";
require_once "../../includes/funciones/funciones_servicios.php";

// Verificar permisos
if (!es_admin()) {
    header("Location: listar_servicios.php?error=permisos");
    exit;
}

// Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: registrar_servicio.php?error=metodo_invalido");
    exit;
}

// Función para sanitizar strings
function sanitize_string($input) {
    return htmlspecialchars(strip_tags(trim($input ?? '')), ENT_QUOTES, 'UTF-8');
}

// Obtener y sanitizar datos
$responsable = filter_input(INPUT_POST, 'responsable', FILTER_VALIDATE_INT);
$agua = $_POST['agua'] ?? [];
$combustible = $_POST['combustible'] ?? [];

// Validaciones
$errores = [];

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

// Validar datos de agua
if (!empty($agua)) {
    $agua_medida = filter_var($agua['medida'] ?? null, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $agua_unidad = sanitize_string($agua['unidad'] ?? '');
    $agua_observaciones = sanitize_string($agua['observaciones'] ?? '');

    // Validar medida de agua
    if (!is_numeric($agua_medida)) {
        $errores[] = 'agua_medida_no_numerica';
    } elseif ($agua_medida <= 0) {
        $errores[] = 'agua_medida_invalida';
    }

    // Validar unidad de agua
    $unidades_agua_permitidas = ['porcentaje', 'litros'];
    if (!in_array($agua_unidad, $unidades_agua_permitidas)) {
        $errores[] = 'agua_unidad_invalida';
    }

    // Validación específica para porcentaje de agua
    if ($agua_unidad === 'porcentaje' && ($agua_medida < 0 || $agua_medida > 100)) {
        $errores[] = 'agua_porcentaje_invalido';
    }
}

// Validar datos de combustible
if (!empty($combustible)) {
    $combustible_medida = filter_var($combustible['medida'] ?? null, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $combustible_unidad = sanitize_string($combustible['unidad'] ?? '');
    $combustible_observaciones = sanitize_string($combustible['observaciones'] ?? '');

    // Validar medida de combustible
    if (!is_numeric($combustible_medida)) {
        $errores[] = 'combustible_medida_no_numerica';
    } elseif ($combustible_medida <= 0) {
        $errores[] = 'combustible_medida_invalida';
    }

    // Validar unidad de combustible
    $unidades_combustible_permitidas = ['litros', 'galones'];
    if (!in_array($combustible_unidad, $unidades_combustible_permitidas)) {
        $errores[] = 'combustible_unidad_invalida';
    }
}

// Manejar errores
if (!empty($errores)) {
    $query = http_build_query(['errores' => $errores]);
    header("Location: registrar_servicio.php?$query");
    exit;
}

// Registrar servicios en una transacción
$conn->begin_transaction();

try {
    // Registrar servicio de agua
    if (!empty($agua)) {
        $sql = "INSERT INTO servicios (tipo, medida, unidad, observaciones, responsable) 
                VALUES ('agua', ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("dssi", $agua_medida, $agua_unidad, $agua_observaciones, $responsable);
        $stmt->execute();
        $stmt->close();
    }

    // Registrar servicio de combustible
    if (!empty($combustible)) {
        $sql = "INSERT INTO servicios (tipo, medida, unidad, observaciones, responsable) 
                VALUES ('combustible', ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("dssi", $combustible_medida, $combustible_unidad, $combustible_observaciones, $responsable);
        $stmt->execute();
        $stmt->close();
    }

    $conn->commit();
    header("Location: listar_servicios.php?success=registro_exitoso");
} catch (Exception $e) {
    $conn->rollback();
    error_log("Error al registrar servicios: " . $e->getMessage());
    header("Location: registrar_servicio.php?error=error_bd");
}