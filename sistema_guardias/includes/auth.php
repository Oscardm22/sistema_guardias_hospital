<?php
session_start();

// Configuración de headers anti-caché más robusta
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Fecha en el pasado

// Verificar sesión activa
if (!isset($_SESSION['usuario'])) {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Location: /modulos/auth/login.php");
    exit;
}

// Verificar inactividad (30 minutos)
$inactividad = 1800; // 30 minutos en segundos
if (isset($_SESSION['usuario']['ultimo_acceso']) && 
    (time() - $_SESSION['usuario']['ultimo_acceso']) > $inactividad) {
    session_unset();
    session_destroy();
    header("Location: /modulos/auth/login.php?timeout=1");
    exit;
}

// Actualizar tiempo de último acceso
$_SESSION['usuario']['ultimo_acceso'] = time();
?>