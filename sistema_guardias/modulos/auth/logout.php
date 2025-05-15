<?php
session_start();

// Destruye todas las variables de sesión
$_SESSION = array();

// Borra la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruye la sesión
session_destroy();

// Redirige al login con mensaje si fue por timeout
if (isset($_GET['timeout'])) {
    header("Location: /modulos/auth/login.php?timeout=1");
} else {
    header("Location: /modulos/auth/login.php");
}
exit;
?>