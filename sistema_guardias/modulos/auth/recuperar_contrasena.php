<?php
session_start();
require_once __DIR__ . '/../../includes/conexion.php';

// Si ya hay sesión de admin, redirigir directamente
if (isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] === 'admin') {
    header("Location: cambiar_contrasena.php");
    exit;
}

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña</title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/styles_login.css" rel="stylesheet">
    <link href="../../assets/css/styles_recuperar_contrasena.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <div class="text-center mb-3">
                <img src="../../assets/images/logo_hospital.png" alt="Logo" width="60">
                <h5 class="mt-2">Recuperar Contraseña</h5>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="verificar_usuario.php">
                <div class="mb-3">
                    <label for="usuario" class="form-label">Nombre de Usuario</label>
                    <input type="text" name="usuario" id="usuario" class="form-control" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Verificar</button>
                </div>
            </form>

            <div class="text-center mt-3">
                <a href="login.php" class="text-decoration-none small">
                    <i class="bi bi-arrow-left"></i> Volver al login
                </a>
            </div>
        </div>
    </div>
</body>
</html>
