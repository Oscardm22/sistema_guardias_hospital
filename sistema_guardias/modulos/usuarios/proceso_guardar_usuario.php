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

$id = isset($_POST['id']) ? (int)$_POST['id'] : null;
$usuario = trim($_POST['usuario']);
$rol = $_POST['rol'];
$contrasena = $_POST['contrasena'] ?? '';
$confirmar = $_POST['confirmar_contrasena'] ?? '';

// Validaciones
if (empty($usuario)) {
    $_SESSION['error'] = 'El nombre de usuario es requerido';
    header('Location: ' . ($id ? "editar_usuario.php?id=$id" : "crear_usuario.php"));
    exit;
}

if (!in_array($rol, ['admin', 'personal'])) {
    $_SESSION['error'] = 'Rol no válido';
    header('Location: ' . ($id ? "editar_usuario.php?id=$id" : "crear_usuario.php"));
    exit;
}

if ($id) {
    // Edición de usuario existente
    if (!empty($contrasena)) {
        if ($contrasena !== $confirmar) {
            $_SESSION['error'] = 'Las contraseñas no coinciden';
            header("Location: editar_usuario.php?id=$id");
            exit;
        }
        
        if (strlen($contrasena) < 8) {
            $_SESSION['error'] = 'La contraseña debe tener al menos 8 caracteres';
            header("Location: editar_usuario.php?id=$id");
            exit;
        }
    }
    
    if (UsuarioFunciones::existeUsuario($conn, $usuario, $id)) {
        $_SESSION['error'] = 'El nombre de usuario ya está en uso';
        header("Location: editar_usuario.php?id=$id");
        exit;
    }
    
    $resultado = UsuarioFunciones::actualizarUsuario($conn, $id, $usuario, $rol, $contrasena ?: null);
} else {
    // Creación de nuevo usuario
    if (empty($contrasena)) {
        $_SESSION['error'] = 'La contraseña es requerida';
        header('Location: crear_usuario.php');
        exit;
    }
    
    if ($contrasena !== $confirmar) {
        $_SESSION['error'] = 'Las contraseñas no coinciden';
        header('Location: crear_usuario.php');
        exit;
    }
    
    if (strlen($contrasena) < 8) {
        $_SESSION['error'] = 'La contraseña debe tener al menos 8 caracteres';
        header('Location: crear_usuario.php');
        exit;
    }
    
    if (UsuarioFunciones::existeUsuario($conn, $usuario)) {
        $_SESSION['error'] = 'El nombre de usuario ya está en uso';
        header('Location: crear_usuario.php');
        exit;
    }
    
    $resultado = UsuarioFunciones::crearUsuario($conn, $usuario, $contrasena, $rol);
}

if ($resultado) {
    $_SESSION['exito'] = $id ? 'Usuario actualizado correctamente' : 'Usuario creado correctamente';
} else {
    $_SESSION['error'] = 'Ocurrió un error al guardar el usuario';
}

header('Location: listar_usuarios.php');
exit;