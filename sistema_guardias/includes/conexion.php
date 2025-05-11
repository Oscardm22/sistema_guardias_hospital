<?php
$host = "localhost";
$user = "root";
$password = "";  // Vacía por defecto en XAMPP
$database = "sistema_guardias";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>