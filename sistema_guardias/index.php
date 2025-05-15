<?php
// Protección de ruta
require_once "includes/conexion.php";
require_once "includes/auth.php"; // Verifica si el usuario está logueado
require_once "includes/funciones.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Guardias - Hospital Naval</title>
    <!-- Favicon -->
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <!-- Bootstrap CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Estilos personalizados -->
    <link href="assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Inclusión del navbar -->
    <?php include "includes/navbar.php"; ?>

    <!-- Contenido principal -->
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow">
                    <div class="card-header bg-white">
                        <h3 class="text-center">
                            <?php echo isset($_SESSION['usuario']) ? "Bienvenido, " . htmlspecialchars(nombre_completo_usuario()) : "Personal Registrado"; ?>
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['usuario'])): ?>
                            <!-- Tabla de personal -->
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Grado</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT id_personal, nombre, grado, estado FROM personal LIMIT 5";
                                    $result = $conn->query($query);

                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $estado = $row["estado"] ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>';
                                            echo "<tr>
                                                    <td>".htmlspecialchars($row['id_personal'])."</td>
                                                    <td>".htmlspecialchars($row['nombre'])."</td>
                                                    <td>".htmlspecialchars($row['grado'])."</td>
                                                    <td>{$estado}</td>
                                                </tr>";
                                        }
                                    } else {
                                        echo '<tr><td colspan="4" class="text-center">No hay personal registrado.</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                            <?php if (puede_crear_guardia()): ?>
                                <a href="modulos/guardias/crear_guardia.php" class="btn btn-primary mt-3">
                                    <i class="bi bi-plus-circle"></i> Registrar Nueva Guardia
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- Mensaje para invitados -->
                            <div class="alert alert-info text-center">
                                <h4><i class="bi bi-shield-lock"></i> Acceso Restringido</h4>
                                <p>Por favor inicie sesión para acceder al sistema.</p>
                                <a href="modulos/auth/login.php" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>