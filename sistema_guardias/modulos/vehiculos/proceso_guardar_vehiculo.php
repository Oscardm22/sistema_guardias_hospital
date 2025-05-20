<?php
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/funciones/funciones_autenticacion.php';

// Verificar sesión y permisos
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

// Validar y sanitizar datos
$placa = trim($_POST['placa'] ?? '');
$modelo = trim($_POST['modelo'] ?? '');

// Validaciones básicas
if (empty($placa) || empty($modelo)) {
    $_SESSION['error'] = "Todos los campos son obligatorios";
    header("Location: crear_vehiculo.php");
    exit;
}

// Validar formato de placa
if (!preg_match('/^[A-Z0-9-]+$/i', $placa)) {
    $_SESSION['error'] = "La placa solo puede contener letras, números y guiones";
    header("Location: crear_vehiculo.php");
    exit;
}

// Procesar el formulario
try {
    // Convertir placa a mayúsculas
    $placa = strtoupper($placa);
    
    $stmt = $conn->prepare("INSERT INTO vehiculos (placa, modelo) VALUES (?, ?)");
    $stmt->bind_param("ss", $placa, $modelo);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Vehículo registrado correctamente";
        header("Location: listar_vehiculos.php");
    } else {
        // Verificar si es error de duplicado
        if ($conexion->errno == 1062) {
            $_SESSION['error'] = "La placa ya está registrada";
        } else {
            $_SESSION['error'] = "Error al registrar el vehículo";
        }
        header("Location: crear_vehiculo.php");
    }
    exit;
} catch (Exception $e) {
    $_SESSION['error'] = "Error en la base de datos: " . $e->getMessage();
    header("Location: crear_vehiculo.php");
    exit;
}