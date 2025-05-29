<?php
require_once "../../includes/conexion.php";
require_once "../../includes/auth.php";
require_once "../../includes/funciones.php";

if (!es_admin()) {
    header("Location: listar_roles.php?error=no_permiso");
    exit;
}

$id_rol = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$rol = ['nombre_rol' => ''];
$error = '';

// Obtener datos del rol si estamos editando
if ($id_rol > 0) {
    $stmt = $conn->prepare("SELECT id_rol, nombre_rol FROM roles_guardia WHERE id_rol = ?");
    $stmt->bind_param("i", $id_rol);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header("Location: listar_roles.php?error=rol_no_encontrado");
        exit;
    }
    
    $rol = $result->fetch_assoc();
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    
    // Validaciones
    if (empty($nombre)) {
        $error = "El nombre del rol es obligatorio";
    } else {
        // Verificar si el nombre ya existe (excepto para el rol actual)
        $sql = "SELECT id_rol FROM roles_guardia WHERE nombre_rol = ? AND id_rol != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $nombre, $id_rol);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $error = "Ya existe un rol con ese nombre";
        }
    }
    
    if (empty($error)) {
        if ($id_rol > 0) {
            // Actualizar rol existente
            $stmt = $conn->prepare("UPDATE roles_guardia SET nombre_rol = ? WHERE id_rol = ?");
            $stmt->bind_param("si", $nombre, $id_rol);
        } else {
            // Crear nuevo rol
            $stmt = $conn->prepare("INSERT INTO roles_guardia (nombre_rol) VALUES (?)");
            $stmt->bind_param("s", $nombre);
        }
        
        if ($stmt->execute()) {
            $accion = $id_rol > 0 ? 'actualizado' : 'creado';
            header("Location: listar_roles.php?success=$accion");
            exit;
        } else {
            $error = "Error al guardar el rol: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $id_rol > 0 ? 'Editar' : 'Crear' ?> Rol de Guardia</title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include "../../includes/navbar.php"; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4><i class="bi bi-person-badge me-2"></i> <?= $id_rol > 0 ? 'Editar' : 'Crear' ?> Rol</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre del Rol *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   value="<?= htmlspecialchars($rol['nombre_rol']) ?>" required>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <a href="listar_roles.php" class="btn btn-secondary me-2">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>