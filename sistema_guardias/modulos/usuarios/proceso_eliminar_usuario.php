<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/funciones/funciones_usuarios.php';
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/../../includes/funciones/funciones_autenticacion.php';

if (!es_admin()) {
    header('HTTP/1.0 403 Forbidden');
    exit('Acceso restringido a administradores');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar_usuarios.php');
    exit;
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    header('Location: listar_usuarios.php');
    exit;
}

if ($_POST['id'] == $_SESSION['usuario_id']) {
    $_SESSION['error'] = 'No puedes eliminar tu propio usuario';
    header('Location: listar_usuarios.php');
    exit;
}

$resultado = UsuarioFunciones::eliminarUsuario($conn, $_POST['id']);

if ($resultado) {
    $_SESSION['exito'] = 'Usuario eliminado correctamente';
} else {
    $_SESSION['error'] = 'Ocurrió un error al eliminar el usuario';
}

header('Location: listar_usuarios.php');
exit;