<?php
session_start();
require_once __DIR__ . '/../../includes/conexion.php';

// Validaciones de seguridad
if (!isset($_SESSION['usuario_reset'], $_SESSION['reset_token'], $_SESSION['reset_time']) || 
    (time() - $_SESSION['reset_time']) > 600) { // 10 minutos de validez
    
    unset($_SESSION['usuario_reset'], $_SESSION['reset_token'], $_SESSION['reset_time']);
    $_SESSION['error'] = "Solicitud inválida o expirada";
    header("Location: recuperar_contrasena.php");
    exit;
}

// Si hay sesión de admin activa, verificar que sea el mismo usuario
if (isset($_SESSION['usuario'])) {
    if ($_SESSION['usuario']['rol'] !== 'admin' || $_SESSION['usuario']['usuario'] !== $_SESSION['usuario_reset']) {
        unset($_SESSION['usuario_reset'], $_SESSION['reset_token'], $_SESSION['reset_time']);
        $_SESSION['error'] = "No tiene permisos para esta acción";
        header("Location: login.php");
        exit;
    }
}

$usuario = $_SESSION['usuario_reset'];
$error = $_SESSION['error'] ?? '';
$exito = $_SESSION['exito'] ?? '';
unset($_SESSION['error'], $_SESSION['exito']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cambiar Contraseña</title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/styles_login.css" rel="stylesheet">
    <link href="../../assets/css/styles_cambiar_contrasena.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h5 class="text-center mb-3">Cambiar Contraseña de <strong><?= htmlspecialchars($usuario) ?></strong></h5>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($exito): ?>
                <div class="alert alert-success"><?= htmlspecialchars($exito) ?></div>
            <?php endif; ?>

            <form action="proceso_cambio.php" method="POST">
                <div class="mb-3">
                    <label for="nueva_contrasena" class="form-label">Nueva Contraseña</label>
                    <input type="password" name="nueva_contrasena" id="nueva_contrasena" class="form-control" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-success">Actualizar Contraseña</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
