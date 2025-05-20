<?php
require_once "../../includes/conexion.php";
require_once "../../includes/auth.php";
require_once "../../includes/funciones/funciones_autenticacion.php";
require_once "../../includes/funciones/funciones_servicios.php";

if (!es_admin()) {
    header("Location: listar_servicios.php?error=permisos");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: registrar_servicio.php?error=metodo_invalido");
    exit;
}

// Validar datos
$tipo = $_POST['tipo'] ?? null;
$medida = $_POST['medida'] ?? null;
$unidad = $_POST['unidad'] ?? null;

// Validaciones
if (!in_array($tipo, ['agua', 'electricidad'])) {
    header("Location: registrar_servicio.php?error=tipo_invalido");
    exit;
}

if (!is_numeric($medida)) {
    header("Location: registrar_servicio.php?error=medida_invalida");
    exit;
}

if (!in_array($unidad, ['porcentaje', 'litros', 'kWh'])) {
    header("Location: registrar_servicio.php?error=unidad_invalida");
    exit;
}

// Registrar servicio
try {
    if (registrarServicio($conn, $tipo, $medida, $unidad)) {
        header("Location: listar_servicios.php?success=registro_exitoso");
    } else {
        header("Location: registrar_servicio.php?error=error_registro");
    }
} catch (Exception $e) {
    header("Location: registrar_servicio.php?error=error_bd");
}