<?php
require_once "../../includes/conexion.php";
require_once "../../includes/auth.php";
require_once "../../includes/funciones.php";

// Solo admin puede acceder
if (!es_admin()) {
    header("Location: listar_guardias.php?error=no_permiso");
    exit;
}

$error = "";

// Obtener lista de personal activo
$query_personal = "SELECT id_personal, nombre, grado FROM personal WHERE estado = 1 ORDER BY nombre";
$result_personal = $conn->query($query_personal);
$personal = [];
while ($row = $result_personal->fetch_assoc()) {
    $personal[] = $row;
}

// Obtener lista de roles
$query_roles = "SELECT id_rol, nombre_rol FROM roles_guardia ORDER BY nombre_rol";
$result_roles = $conn->query($query_roles);
$roles = [];
while ($row = $result_roles->fetch_assoc()) {
    $roles[] = $row;
}

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fecha = $_POST['fecha'];
    $tipo_guardia = $_POST['tipo_guardia'];
    $asignaciones = $_POST['asignaciones'] ?? [];

    // Verificar duplicados en asignaciones
    $ids_personal = array_column($asignaciones, 'id_personal');
    $repetidos = array_diff_assoc($ids_personal, array_unique($ids_personal));

    if (!empty($repetidos)) {
        $error = "No puedes asignar al mismo personal más de una vez en la misma guardia.";
    }

    if (empty($error)) {
        // Insertar la guardia
        $sql_guardia = "INSERT INTO guardias (fecha_inicio, fecha_fin, tipo_guardia) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql_guardia);
        $stmt->bind_param("sss", $fecha, $fecha, $tipo_guardia);

        if ($stmt->execute()) {
            $id_guardia = $conn->insert_id;

            // Insertar asignaciones
            $sql_asig = "INSERT INTO asignaciones_guardia (id_personal, id_guardia, id_rol, turno) VALUES (?, ?, ?, ?)";
            $stmt_asig = $conn->prepare($sql_asig);

            foreach ($asignaciones as $a) {
                $id_personal = (int)$a['id_personal'];
                $id_rol = (int)$a['id_rol'];
                $turno = !empty($a['turno']) ? $a['turno'] : NULL;

                $stmt_asig->bind_param("iiis", $id_personal, $id_guardia, $id_rol, $turno);
                $stmt_asig->execute();
            }

            header("Location: listar_guardias.php?success=guardia_creada");
            exit;
        } else {
            $error = "Error al guardar la guardia: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Guardia de 24 Horas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../assets/images/favicon.ico" type="image/x-icon">
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/styles_crear_guardias.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include "../../includes/navbar.php"; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4><i class="bi bi-calendar-plus"></i> Nueva Guardia de 24 Horas</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <!-- Fecha -->
                        <div class="mb-3">
                            <label for="fecha" class="form-label"><i class="bi bi-calendar"></i> Fecha de la Guardia</label>
                            <input type="date" class="form-control" id="fecha" name="fecha" required min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>">
                        </div>

                        <!-- Tipo de guardia -->
                        <div class="mb-3">
                            <label for="tipo_guardia" class="form-label"><i class="bi bi-list-check"></i> Tipo de Guardia</label>
                            <select class="form-select" id="tipo_guardia" name="tipo_guardia" required>
                                <option value="diurna">Diurna</option>
                                <option value="nocturna">Nocturna</option>
                            </select>
                        </div>

                        <!-- Asignaciones -->
                        <div id="contenedor-asignaciones">
                            <label class="form-label"><i class="bi bi-people"></i> Asignaciones de Personal</label>
                            <div class="asignacion-personal border p-3 mb-3">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label">Personal</label>
                                        <select name="asignaciones[0][id_personal]" class="form-select" required>
                                            <option value="">Seleccionar</option>
                                            <?php foreach ($personal as $p): ?>
                                                <option value="<?= $p['id_personal'] ?>"><?= $p['grado'] . ' ' . $p['nombre'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Rol</label>
                                        <select name="asignaciones[0][id_rol]" class="form-select" required>
                                            <option value="">Seleccionar</option>
                                            <?php foreach ($roles as $r): ?>
                                                <option value="<?= $r['id_rol'] ?>"><?= $r['nombre_rol'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Turno (opcional)</label>
                                        <select name="asignaciones[0][turno]" class="form-select">
                                            <option value="">Sin turno</option>
                                            <option value="mañana">Mañana</option>
                                            <option value="tarde">Tarde</option>
                                            <option value="noche">Noche</option>
                                        </select>
                                    </div>

                                    <div class="col-md-1 text-end">
                                        <button type="button" class="btn btn-danger btn-sm eliminar-asignacion"><i class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Agregar asignación -->
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
                                <i class="bi bi-save"></i> Guardar Guardia
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../../assets/js/bootstrap.bundle.min.js"></script>
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

    // Validación de duplicados antes de enviar
    document.querySelector('form').addEventListener('submit', function(e) {
        const selects = document.querySelectorAll('select[name*="[id_personal]"]');
        const seleccionados = Array.from(selects).map(s => s.value);
        const duplicados = seleccionados.filter((item, index) => seleccionados.indexOf(item) !== index);

        if (duplicados.length > 0) {
            e.preventDefault();
            alert("No puedes asignar al mismo personal más de una vez en la misma guardia.");
        }
    });
});
</script>
</body>
</html>