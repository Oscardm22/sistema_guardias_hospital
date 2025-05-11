<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: /modulos/auth/login.php"); // ← Usa ruta absoluta desde la raíz del servidor
    exit;
}

// Verificación opcional por rol
function verificarRol($rolRequerido) {
    if ($_SESSION['rol'] != $rolRequerido) {
        header("Location: /index.php?error=permisos");
        exit;
    }
}
?>