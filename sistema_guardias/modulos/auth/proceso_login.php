<?php
session_start();
require_once "../../includes/conexion.php";
require_once "../../includes/funciones.php";

// Debug
error_log("Iniciando proceso de login");

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
$sql = "SELECT u.id_usuario, u.usuario, u.contrasena, u.rol, u.id_personal,
               p.nombre, p.apellido, p.grado, p.estado as estado_personal
        FROM usuarios u
        JOIN personal p ON u.id_personal = p.id_personal
        WHERE u.usuario = ? LIMIT 1";

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

// Verificar estado del personal asociado
if ($usuarioData['estado_personal'] != 1) {
    error_log("Usuario asociado a personal inactivo: $usuario");
    $_SESSION['error'] = "Cuenta desactivada";
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

// Datos completos de sesión
$_SESSION['usuario'] = [
    'id' => $usuarioData['id_usuario'],
    'usuario' => $usuarioData['usuario'],
    'rol' => $usuarioData['rol'],
    'permisos' => $permisos,
    'personal' => [
        'id' => $usuarioData['id_personal'],
        'nombre' => $usuarioData['nombre'],
        'apellido' => $usuarioData['apellido'],
        'grado' => $usuarioData['grado']
    ]
];

error_log("Login exitoso. Usuario: {$usuarioData['usuario']}, Rol: {$usuarioData['rol']}");
error_log("Datos personal: " . print_r($_SESSION['usuario']['personal'], true));

header("Location: ../../index.php");
exit;
?>