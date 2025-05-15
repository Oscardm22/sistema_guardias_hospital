<?php
session_start();
require_once "../../includes/conexion.php"; // Usa $conn, no $conexion

if (!isset($_SESSION['usuario_reset'])) {
    header("Location: recuperar_contrasena.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST["nueva_contrasena"])) {
        $_SESSION['error'] = "Debe ingresar una nueva contraseña.";
        header("Location: cambiar_contrasena.php");
        exit;
    }

    $usuario = $_SESSION['usuario_reset'];
    $nuevaContrasena = password_hash(trim($_POST["nueva_contrasena"]), PASSWORD_DEFAULT);

    $sql = "UPDATE usuarios SET contrasena = ? WHERE usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $nuevaContrasena, $usuario);

    if ($stmt->execute()) {
        unset($_SESSION['usuario_reset']);
        $_SESSION['exito'] = "Contraseña actualizada correctamente.";
        header("Location: login.php");
    } else {
        $_SESSION['error'] = "Error al actualizar la contraseña.";
        header("Location: cambiar_contrasena.php");
    }
    exit;
} else {
    header("Location: recuperar_contrasena.php");
    exit;
}
