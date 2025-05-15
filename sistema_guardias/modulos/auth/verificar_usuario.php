<?php
session_start();
require_once "../../includes/conexion.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST["usuario"]);

    $sql = "SELECT * FROM usuarios WHERE usuario = ?";
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $datos = $resultado->fetch_assoc();
        if ($datos['rol'] === 'admin') {
            $_SESSION['usuario_reset'] = $usuario;
            header("Location: cambiar_contrasena.php");
            exit;
        } else {
            $_SESSION['error'] = "Solo los usuarios con rol de administrador pueden restablecer la contraseña.";
            header("Location: recuperar_contrasena.php");
            exit;
        }
    } else {
        $_SESSION['error'] = "El usuario no existe.";
        header("Location: recuperar_contrasena.php");
        exit;
    }
} else {
    header("Location: recuperar_contrasena.php");
    exit;
}