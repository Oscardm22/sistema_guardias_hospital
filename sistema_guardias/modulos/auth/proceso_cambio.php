<?php
session_start();
require_once __DIR__ . '/../../includes/conexion.php';

// Validaciones de seguridad
if (!isset($_SESSION['usuario_reset'], $_SESSION['reset_token']) || 
    $_SERVER["REQUEST_METHOD"] !== "POST") {
    
    unset($_SESSION['usuario_reset'], $_SESSION['reset_token'], $_SESSION['reset_time']);
    $_SESSION['error'] = "Solicitud inválida";
    header("Location: recuperar_contrasena.php");
    exit;
}

// Validar contraseña
if (!isset($_POST["nueva_contrasena"]) || strlen(trim($_POST["nueva_contrasena"])) < 8) {
    $_SESSION['error'] = "La contraseña debe tener al menos 8 caracteres";
    header("Location: cambiar_contrasena.php");
    exit;
}

$usuario = $_SESSION['usuario_reset'];
$nuevaContrasena = password_hash(trim($_POST["nueva_contrasena"]), PASSWORD_DEFAULT);

// Actualizar contraseña
$stmt = $conn->prepare("UPDATE usuarios SET contrasena = ? WHERE usuario = ?");
$stmt->bind_param("ss", $nuevaContrasena, $usuario);

if ($stmt->execute()) {
    // Limpiar variables de sesión
    unset($_SESSION['usuario_reset'], $_SESSION['reset_token'], $_SESSION['reset_time']);
    
    // Mensaje de éxito diferente según si estaba logueado o no
    if (isset($_SESSION['usuario'])) {
        $_SESSION['exito'] = "Contraseña actualizada correctamente";
        header("Location: ../index.php"); // Redirigir al panel admin
    } else {
        $_SESSION['exito'] = "Contraseña actualizada. Por favor inicie sesión";
        header("Location: login.php");
    }
} else {
    $_SESSION['error'] = "Error al actualizar la contraseña";
    header("Location: cambiar_contrasena.php");
}
exit;
?>