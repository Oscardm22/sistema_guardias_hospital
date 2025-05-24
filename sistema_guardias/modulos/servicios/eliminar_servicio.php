<?php
require_once "../../includes/conexion.php";
require_once "../../includes/auth.php";
require_once "../../includes/funciones/funciones_autenticacion.php";
require_once "../../includes/funciones/funciones_servicios.php";

if (!es_admin()) {
    header("Location: listar_servicios.php?error=permisos");
    exit;
}

$id_servicio = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_servicio <= 0) {
    header("Location: listar_servicios.php?error=id_invalido");
    exit;
}

try {
    if (eliminarServicio($conn, $id_servicio)) {
        header("Location: listar_servicios.php?success=eliminacion_exitosa");
    } else {
        header("Location: listar_servicios.php?error=error_eliminacion");
    }
} catch (Exception $e) {
    error_log("Error al eliminar servicio: " . $e->getMessage());
    header("Location: listar_servicios.php?error=error_bd");
}