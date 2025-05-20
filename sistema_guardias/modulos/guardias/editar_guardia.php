<?php
require_once "../../includes/conexion.php";
require_once "../../includes/auth.php";
require_once "../../includes/funciones.php";

// Solo admin puede acceder
if (!es_admin()) {
    header("Location: listar_guardias.php?error=no_permiso");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: listar_guardias.php");
    exit;
}

$id_guardia = (int) $_GET['id'];
$error = "";

// Obtener datos actuales de la guardia
$sql_guardia = "SELECT * FROM guardias WHERE id_guardia = ?";
$stmt = $conn->prepare($sql_guardia);
$stmt->bind_param("i", $id_guardia);
$stmt->execute();
$guardia = $stmt->get_result()->fetch_assoc();
if (!$guardia) {
    header("Location: listar_guardias.php");
    exit;
}

// Obtener lista de personal
$personal = [];
$res_personal = $conn->query("SELECT id_personal, grado, nombre FROM personal WHERE estado = 1 ORDER BY nombre");
while ($row = $res_personal->fetch_assoc()) {
    $personal[] = $row;
}

// Obtener lista de roles
$roles = [];
$res_roles = $conn->query("SELECT id_rol, nombre_rol FROM roles_guardia ORDER BY nombre_rol");
while ($row = $res_roles->fetch_assoc()) {
    $roles[] = $row;
}

// Obtener asignaciones actuales
$asignaciones = [];
$res_asignaciones = $conn->query("
    SELECT id_asignacion, id_personal, id_rol, turno 
    FROM asignaciones_guardia 
    WHERE id_guardia = $id_guardia
");
while ($a = $res_asignaciones->fetch_assoc()) {
    $asignaciones[] = $a;
}

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fecha = $_POST['fecha'];
    $tipo_guardia = $_POST['tipo_guardia'];
    $nuevas_asignaciones = $_POST['asignaciones'] ?? [];

    // Validar duplicados
    $ids_personal = array_column($nuevas_asignaciones, 'id_personal');
    $repetidos = array_diff_assoc($ids_personal, array_unique($ids_personal));
    if (!empty($repetidos)) {
        $error = "No puedes asignar al mismo personal más de una vez en esta guardia.";
    }

    if (empty($error)) {
        // Actualizar la guardia
        $stmt = $conn->prepare("UPDATE guardias SET fecha_inicio = ?, fecha_fin = ?, tipo_guardia = ? WHERE id_guardia = ?");
        $stmt->bind_param("sssi", $fecha, $fecha, $tipo_guardia, $id_guardia);
        $stmt->execute();

        // Eliminar asignaciones actuales
        $conn->query("DELETE FROM asignaciones_guardia WHERE id_guardia = $id_guardia");

        // Insertar nuevas asignaciones
        $stmt_asig = $conn->prepare("INSERT INTO asignaciones_guardia (id_personal, id_guardia, id_rol, turno) VALUES (?, ?, ?, ?)");
        foreach ($nuevas_asignaciones as $a) {
            $id_personal = (int)$a['id_personal'];
            $id_rol = (int)$a['id_rol'];
            $turno = !empty($a['turno']) ? $a['turno'] : NULL;
            $stmt_asig->bind_param("iiis", $id_personal, $id_guardia, $id_rol, $turno);
            $stmt_asig->execute();
        }

        header("Location: listar_guardias.php?success=guardia_actualizada");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Guardia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../assets/images/favicon.ico" type="image/x-icon">
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include "../../includes/navbar.php"; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4><i class="bi bi-pencil-square"></i> Editar Guardia</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <!-- Fecha -->
                        <div class="mb-3">
                            <label class="form-label"><i class="bi bi-calendar"></i> Fecha</label>
                            <input type="date" name="fecha" class="form-control" value="<?= htmlspecialchars($guardia['fecha_inicio']) ?>" required>
                        </div>

                        <!-- Tipo de guardia -->
                        <div class="mb-3">
                            <label class="form-label"><i class="bi bi-list-check"></i> Tipo de Guardia</label>
                            <select name="tipo_guardia" class="form-select" required>
                                <option value="diurna" <?= $guardia['tipo_guardia'] === 'diurna' ? 'selected' : '' ?>>Diurna</option>
                                <option value="nocturna" <?= $guardia['tipo_guardia'] === 'nocturna' ? 'selected' : '' ?>>Nocturna</option>
                            </select>
                        </div>

                        <!-- Asignaciones -->
                        <div id="contenedor-asignaciones">
                            <label class="form-label"><i class="bi bi-people"></i> Asignaciones</label>

                            <?php foreach ($asignaciones as $index => $a): ?>
                                <div class="asignacion-personal border p-3 mb-3">
                                    <div class="row g-2 align-items-end">
                                        <!-- Personal -->
                                        <div class="col-md-4">
                                            <label class="form-label">Personal</label>
                                            <select name="asignaciones[<?= $index ?>][id_personal]" class="form-select" required>
                                                <option value="">Seleccionar</option>
                                                <?php foreach ($personal as $p): ?>
                                                    <option value="<?= $p['id_personal'] ?>" <?= $p['id_personal'] == $a['id_personal'] ? 'selected' : '' ?>>
                                                        <?= $p['grado'] . ' ' . $p['nombre'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <!-- Rol -->
                                        <div class="col-md-4">
                                            <label class="form-label">Rol</label>
                                            <select name="asignaciones[<?= $index ?>][id_rol]" class="form-select" required>
                                                <option value="">Seleccionar</option>
                                                <?php foreach ($roles as $r): ?>
                                                    <option value="<?= $r['id_rol'] ?>" <?= $r['id_rol'] == $a['id_rol'] ? 'selected' : '' ?>>
                                                        <?= $r['nombre_rol'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <!-- Turno -->
                                        <div class="col-md-3">
                                            <label class="form-label">Turno (opcional)</label>
                                            <select name="asignaciones[<?= $index ?>][turno]" class="form-select">
                                                <option value="">Sin turno</option>
                                                <option value="mañana" <?= $a['turno'] === 'mañana' ? 'selected' : '' ?>>Mañana</option>
                                                <option value="tarde" <?= $a['turno'] === 'tarde' ? 'selected' : '' ?>>Tarde</option>
                                                <option value="noche" <?= $a['turno'] === 'noche' ? 'selected' : '' ?>>Noche</option>
                                            </select>
                                        </div>

                                        <!-- Eliminar -->
                                        <div class="col-md-1 text-end">
                                            <button type="button" class="btn btn-danger btn-sm eliminar-asignacion"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Botón agregar -->
                        <div class="mb-3">
                            <button type="button" class="btn btn-secondary btn-sm" id="agregar-asignacion">
                                <i class="bi bi-plus-circle"></i> Agregar asignación
                            </button>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-end">
                            <a href="listar_guardias.php" class="btn btn-secondary me-2">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Actualizar Guardia
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const contenedor = document.getElementById('contenedor-asignaciones');
    const btnAgregar = document.getElementById('agregar-asignacion');

    btnAgregar.addEventListener('click', () => {
        const index = contenedor.querySelectorAll('.asignacion-personal').length;
        const original = contenedor.querySelector('.asignacion-personal');
        const clon = original.cloneNode(true);

        clon.querySelectorAll('select').forEach(select => {
            const name = select.name.replace(/\[\d+\]/, `[${index}]`);
            select.name = name;
            select.value = '';
        });

        contenedor.appendChild(clon);
    });

    contenedor.addEventListener('click', e => {
        if (e.target.closest('.eliminar-asignacion')) {
            const items = contenedor.querySelectorAll('.asignacion-personal');
            if (items.length > 1) {
                e.target.closest('.asignacion-personal').remove();
            }
        }
    });

    // Validación de duplicados
    document.querySelector('form').addEventListener('submit', function(e) {
        const selects = document.querySelectorAll('select[name*="[id_personal]"]');
        const seleccionados = Array.from(selects).map(s => s.value);
        const duplicados = seleccionados.filter((item, index) => seleccionados.indexOf(item) !== index);

        if (duplicados.length > 0) {
            e.preventDefault();
            alert("No puedes asignar al mismo personal más de una vez en esta guardia.");
        }
    });
});
</script>
</body>
</html>