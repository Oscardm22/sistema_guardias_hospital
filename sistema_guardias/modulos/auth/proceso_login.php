<?php
session_start();
require_once "../../includes/conexion.php";

// Debug
error_log("Iniciando proceso de login");

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $_SESSION['error'] = "Método no permitido";
    header("Location: login.php");
    exit;
}

$usuario = trim($_POST['usuario']);
$contrasena = $_POST['contrasena'];

if (empty($usuario) || empty($contrasena)) {
    $_SESSION['error'] = "Usuario y contraseña son obligatorios";
    header("Location: login.php");
    exit;
}

// Debug: Ver credenciales
error_log("Usuario: $usuario | Contraseña: $contrasena");

$sql = "SELECT id_usuario, usuario, contrasena, rol FROM usuarios WHERE usuario = ? LIMIT 1";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log("Error SQL: " . $conn->error);
    $_SESSION['error'] = "Error en el sistema";
    header("Location: login.php");
    exit;
}

$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    error_log("Usuario no encontrado: $usuario");
    $_SESSION['error'] = "Usuario no registrado";
    header("Location: login.php");
    exit;
}

$row = $result->fetch_assoc();

// Debug: Ver hash almacenado
error_log("Hash en BD: " . $row['contrasena']);

if (!password_verify($contrasena, $row['contrasena'])) {
    error_log("Contraseña incorrecta para: $usuario");
    $_SESSION['error'] = "Credenciales inválidas";
    header("Location: login.php");
    exit;
}

// Login exitoso
$_SESSION['usuario'] = $row['usuario'];
$_SESSION['rol'] = $row['rol'];
$_SESSION['id_usuario'] = $row['id_usuario'];

error_log("Login exitoso para: $usuario");
header("Location: ../../index.php");
exit;
?>