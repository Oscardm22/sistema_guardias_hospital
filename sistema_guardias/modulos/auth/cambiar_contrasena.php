<?php
session_start();
require_once "../../includes/conexion.php";

if (!isset($_SESSION['usuario_reset'])) {
    unset($_SESSION['usuario_reset']);
    header("Location: recuperar_contrasena.php");
    exit;
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
    <style>
        .login-box {
            max-width: 420px;
            margin: 4rem auto;
            padding: 2rem;
            border-radius: 10px;
            background-color: #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
    </style>
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
