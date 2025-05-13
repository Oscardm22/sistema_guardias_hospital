<?php
$host = "localhost";
$user = "root";
$password = "";  // Vacía por defecto en XAMPP
$database = "sistema_guardias";
$port = 3307;    // Nuevo puerto que configuraste

$conn = new mysqli($host, $user, $password, $database, $port);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>