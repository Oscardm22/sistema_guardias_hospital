<?php
session_start();
require_once __DIR__ . '/../../includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['error'] = "Método no permitido";
    header("Location: recuperar_contrasena.php");
    exit;
}

$usuario = trim($_POST["usuario"]);

// Verificar primero si hay una sesión de admin activa
if (isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] === 'admin') {
    // Si es admin con sesión activa, verificar que el usuario exista
    $stmt = $conn->prepare("SELECT usuario FROM usuarios WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 1) {
        $_SESSION['usuario_reset'] = $usuario;
        $_SESSION['reset_token'] = bin2hex(random_bytes(32));
        $_SESSION['reset_time'] = time();
        header("Location: cambiar_contrasena.php");
        exit;
    } else {
        $_SESSION['error'] = "El usuario no existe";
        header("Location: recuperar_contrasena.php");
        exit;
    }
}

// Si no hay sesión de admin, pedir credenciales
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE usuario = ? AND rol = 'admin'");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {
    $_SESSION['usuario_reset'] = $usuario;
    $_SESSION['reset_token'] = bin2hex(random_bytes(32));
    $_SESSION['reset_time'] = time();
    header("Location: cambiar_contrasena.php");
    exit;
} else {
    $_SESSION['error'] = "Credenciales inválidas o no tiene permisos";
    header("Location: recuperar_contrasena.php");
    exit;
}
?>