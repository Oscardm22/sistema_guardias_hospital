<?php
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/funciones/funciones_autenticacion.php';
require_once __DIR__ . '/../../includes/funciones/funciones_vehiculos.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar permisos
if (!isset($_SESSION['usuario'])) {
    header("Location: /modulos/auth/login.php");
    exit;
}

if (!es_admin()) {
    $_SESSION['error'] = "No tienes permisos para esta acción";
    header("Location: listar_vehiculos.php");
    exit;
}

// Validar método de envío
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Método no permitido";
    header("Location: listar_vehiculos.php");
    exit;
}

// Determinar si es creación o edición
$es_edicion = isset($_POST['id_vehiculo']) && is_numeric($_POST['id_vehiculo']);

// Validar y sanitizar datos
$id_vehiculo = $es_edicion ? intval($_POST['id_vehiculo']) : null;
$placa = trim($_POST['placa'] ?? '');
$marca = trim($_POST['marca'] ?? '');
$tipo = trim($_POST['tipo'] ?? '');
$combustible = trim($_POST['combustible'] ?? '');
$operativo = isset($_POST['operativo']) ? 1 : 0;

// Convertir placa a mayúsculas
$placa = strtoupper($placa);

// Validaciones básicas
if (empty($placa) || empty($marca) || empty($tipo) || empty($combustible)) {
    $_SESSION['error'] = "Todos los campos marcados como obligatorios son requeridos";
    header("Location: " . ($es_edicion ? "editar_vehiculo.php?id=$id_vehiculo" : "crear_vehiculo.php"));
    exit;
}

// Validar formato de placa
if (!preg_match('/^[A-Z0-9-]+$/', $placa)) {
    $_SESSION['error'] = "La placa solo puede contener letras, números y guiones";
    header("Location: " . ($es_edicion ? "editar_vehiculo.php?id=$id_vehiculo" : "crear_vehiculo.php"));
    exit;
}

// Validar valores de enumeración
$tipos_permitidos = ['ambulancia', 'administrativo'];
if (!in_array($tipo, $tipos_permitidos)) {
    $_SESSION['error'] = "Tipo de vehículo no válido";
    header("Location: " . ($es_edicion ? "editar_vehiculo.php?id=$id_vehiculo" : "crear_vehiculo.php"));
    exit;
}

$combustibles_permitidos = ['lleno', '3/4', 'medio', '1/4', 'reserva', 'vacio'];
if (!in_array($combustible, $combustibles_permitidos)) {
    $_SESSION['error'] = "Nivel de combustible no válido";
    header("Location: " . ($es_edicion ? "editar_vehiculo.php?id=$id_vehiculo" : "crear_vehiculo.php"));
    exit;
}

try {
    // Verificar si la placa ya existe (excepto para el vehículo actual en edición)
    if ($es_edicion) {
        $stmt = $conn->prepare("SELECT id_vehiculo FROM vehiculos WHERE placa = ? AND id_vehiculo != ?");
        $stmt->bind_param("si", $placa, $id_vehiculo);
    } else {
        $stmt = $conn->prepare("SELECT id_vehiculo FROM vehiculos WHERE placa = ?");
        $stmt->bind_param("s", $placa);
    }
    
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = "La placa ya está registrada en otro vehículo";
        header("Location: " . ($es_edicion ? "editar_vehiculo.php?id=$id_vehiculo" : "crear_vehiculo.php"));
        exit;
    }

    if ($es_edicion) {
        // Actualizar vehículo existente
        $stmt = $conn->prepare("UPDATE vehiculos SET placa = ?, marca = ?, tipo = ?, combustible = ?, operativo = ? WHERE id_vehiculo = ?");
        $stmt->bind_param("ssssii", $placa, $marca, $tipo, $combustible, $operativo, $id_vehiculo);
        
        if ($stmt->execute()) {
            $_SESSION['exito_vehiculos'] = "Vehículo actualizado correctamente";
            header("Location: listar_vehiculos.php");
        } else {
            $_SESSION['error_vehiculos'] = "Error al actualizar el vehículo: " . $conn->error;
            header("Location: editar_vehiculo.php?id=$id_vehiculo");
        }
    } else {
        // Crear nuevo vehículo
        $stmt = $conn->prepare("INSERT INTO vehiculos (placa, marca, tipo, combustible, operativo) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $placa, $marca, $tipo, $combustible, $operativo);
        
        if ($stmt->execute()) {
            $_SESSION['exito_vehiculos'] = "Vehículo registrado correctamente";
            header("Location: listar_vehiculos.php");
        } else {
            if ($conn->errno == 1062) {
                $_SESSION['error_vehiculos'] = "La placa ya está registrada en otro vehículo";
            } else {
                $_SESSION['error_vehiculos'] = "Error al registrar el vehículo: " . $conn->error;
            }
            header("Location: crear_vehiculo.php");
        }
    }
    exit;
} catch (Exception $e) {
    error_log("Error en proceso_guardar_vehiculo: " . $e->getMessage());
    $_SESSION['error_vehiculos'] = "Error en el sistema. Por favor intente nuevamente.";
    header("Location: " . ($es_edicion ? "editar_vehiculo.php?id=$id_vehiculo" : "crear_vehiculo.php"));
    exit;
}