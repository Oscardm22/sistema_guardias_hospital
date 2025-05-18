<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/funciones/funciones_personal.php';
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/../../includes/funciones/funciones_autenticacion.php';

if (!es_admin()) {
    header('HTTP/1.0 403 Forbidden');
    exit('Acceso restringido a administradores');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar_personal.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : null;
$nombre = trim($_POST['nombre']);
$apellido = trim($_POST['apellido']);
$grado = trim($_POST['grado']);
$estado = isset($_POST['estado']) ? 1 : 0;

// Validaciones
if (empty($nombre) || empty($apellido) || empty($grado)) {
    $_SESSION['error'] = 'Todos los campos son requeridos';
    header('Location: ' . ($id ? "editar_personal.php?id=$id" : "crear_personal.php"));
    exit;
}

if ($id) {
    // Edición
    if (PersonalFunciones::existePersonal($conn, $nombre, $apellido, $id)) {
        $_SESSION['error'] = 'Ya existe un miembro del personal con ese nombre y apellido';
        header("Location: editar_personal.php?id=$id");
        exit;
    }
    
    $resultado = PersonalFunciones::actualizarPersonal($conn, $id, $nombre, $apellido, $grado, $estado);
} else {
    // Creación
    if (PersonalFunciones::existePersonal($conn, $nombre, $apellido)) {
        $_SESSION['error'] = 'Ya existe un miembro del personal con ese nombre y apellido';
        header('Location: crear_personal.php');
        exit;
    }
    
    $resultado = PersonalFunciones::crearPersonal($conn, $nombre, $apellido, $grado, $estado);
}

if ($resultado) {
    $_SESSION['exito'] = $id ? 'Personal actualizado correctamente' : 'Personal registrado correctamente';
} else {
    $_SESSION['error'] = 'Ocurrió un error al guardar los datos';
}

header('Location: listar_personal.php');
exit;