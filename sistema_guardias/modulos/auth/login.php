<?php
session_start();
if (isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit;
}

$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Guardias Hospitalarias</title>
    <!-- Favicon -->
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <!-- Bootstrap 5 CSS -->
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Estilos personalizados -->
    <link href="../../assets/css/styles_login.css" rel="stylesheet">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card card">
            <div class="login-header card-header">
                <img src="../../assets/images/logo_hospital.png" alt="Logo Hospital" class="login-logo">
                <h3 class="mb-2"><i class="bi bi-shield-lock"></i> Acceso al Sistema</h3>
                <p class="mb-0">Hospital Naval "TN. Pedro Manuel Chirinos"</p>
            </div>
            <div class="login-form-container">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> 
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="proceso_login.php" method="POST" class="login-form">
                    <div class="form-group">
                        <label for="usuario" class="form-label">
                            <i class="bi bi-person-fill me-2"></i>Usuario
                        </label>
                        <input type="text" class="form-control" id="usuario" name="usuario" 
                               placeholder="Ingrese su usuario" required autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label for="contrasena" class="form-label">
                            <i class="bi bi-key-fill me-2"></i>Contraseña
                        </label>
                        <input type="password" class="form-control" id="contrasena" 
                               name="contrasena" placeholder="••••••••" required>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-hospital btn-lg">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar
                        </button>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="#" class="forgot-password text-decoration-none small text-muted">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center bg-light py-3">
                <small class="text-muted">Sistema de Gestión de Guardias - Versión 1.0</small>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle con Popper -->
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>