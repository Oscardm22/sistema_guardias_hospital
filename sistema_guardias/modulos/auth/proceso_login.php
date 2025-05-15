<?php
require_once __DIR__ . '/../../includes/conexion.php';
session_start();

// Headers para prevenir caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Fecha en el pasado

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $_SESSION['error'] = "Método no permitido";
    header("Location: login.php");
    exit;
}

$usuario = trim($_POST['usuario'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';


if (empty($usuario) || empty($contrasena)) {
    $_SESSION['error'] = "Usuario y contraseña son obligatorios";
    header("Location: login.php");
    exit;
}

// Consulta que obtiene datos de usuario y personal relacionado
$sql = "SELECT id_usuario, usuario, contrasena, rol 
        FROM usuarios 
        WHERE usuario = ? LIMIT 1";

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
    $_SESSION['error'] = "Credenciales inválidas";
    header("Location: login.php");
    exit;
}

$usuarioData = $result->fetch_assoc();

if (!$usuarioData) {
    error_log("Intento de login fallido para: $usuario");
    $_SESSION['error'] = "Credenciales inválidas";
    header("Location: login.php");
    exit;
}

if (!password_verify($contrasena, $usuarioData['contrasena'])) {
    error_log("Contraseña incorrecta para: $usuario");
    $_SESSION['error'] = "Credenciales inválidas";
    header("Location: login.php");
    exit;
}

/**
 * Asignación de permisos según rol
 */
$permisos = [];
switch ($usuarioData['rol']) {
    case 'admin':
        $permisos = [
            'ver_guardias',
            'crear_guardias',
            'editar_guardias',
            'eliminar_guardias',
            'gestion_usuarios',
            'gestion_personal',
            'reportes'
        ];
        break;
    case 'personal':
        $permisos = [
            'ver_guardias',
        ];
        break;
}

// Después de validar credenciales correctamente:
$_SESSION['usuario'] = [
    'id' => $usuarioData['id_usuario'],
    'usuario' => $usuarioData['usuario'],
    'rol' => $usuarioData['rol'],
    'permisos' => $permisos,
    'ultimo_acceso' => time()
];

// Redirección definitiva
header("Location: /index.php");
exit;
?>