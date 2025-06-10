<?php
require_once "../../includes/conexion.php";
require_once "../../includes/auth.php";
require_once "../../includes/funciones.php";

date_default_timezone_set('America/Caracas');

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
    $asignaciones = $_POST['asignaciones'] ?? [];

    // Verificar si ya existe una guardia para esta fecha
    $sql_check = "SELECT id_guardia FROM guardias WHERE fecha = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $fecha);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        $error = "Ya existe una guardia registrada para la fecha seleccionada.";
    }
    
    // Verificar duplicados en asignaciones
    $ids_personal = array_column($asignaciones, 'id_personal');
    $repetidos = array_diff_assoc($ids_personal, array_unique($ids_personal));

    if (!empty($repetidos)) {
        $error = "No puedes asignar al mismo personal más de una vez en la misma guardia.";
    }

    if (empty($error)) {
        // Insertar la guardia
        $sql_guardia = "INSERT INTO guardias (fecha) VALUES (?)";
        $stmt = $conn->prepare($sql_guardia);
        $stmt->bind_param("s", $fecha);

        if ($stmt->execute()) {
            $id_guardia = $conn->insert_id;

            // Insertar asignaciones
            $sql_asig = "INSERT INTO asignaciones_guardia (id_personal, id_guardia, id_rol, turno) VALUES (?, ?, ?, ?)";
            $stmt_asig = $conn->prepare($sql_asig);

            foreach ($asignaciones as $a) {
                $id_personal = (int)$a['id_personal'];
                $id_rol = (int)$a['id_rol'];
                $turno = $a['turno'];

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
    <style>
        .alert-danger {
            animation: fadeIn 0.3s;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .bg-danger-light {
            background-color: rgba(220, 53, 69, 0.1);
        }
    </style>
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

                    <form method="POST" id="guardiaForm">
                        <!-- Fecha -->
                        <div class="mb-3">
                            <label for="fecha" class="form-label"><i class="bi bi-calendar"></i> Fecha de la Guardia</label>
                            <input type="date" class="form-control" id="fecha" name="fecha" required min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>">
                            <div id="fecha-error" class="invalid-feedback"></div>
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
                                        <label class="form-label">Turno</label>
                                        <select name="asignaciones[0][turno]" class="form-select" required>
                                            <option value="">Seleccionar</option>
                                            <option value="12h">12 horas</option>
                                            <option value="24h">24 horas</option>
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
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-save"></i> Guardar Guardia
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para errores -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>Error en asignación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="errorModalBody">
                <!-- Mensaje se insertará aquí -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>

<script src="../../assets/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const contenedor = document.getElementById('contenedor-asignaciones');
    const btnAgregar = document.getElementById('agregar-asignacion');
    const fechaInput = document.getElementById('fecha');
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('guardiaForm');

    // Función para clonar asignaciones
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

    // Eliminar asignaciones
    contenedor.addEventListener('click', e => {
        if (e.target.closest('.eliminar-asignacion')) {
            const items = contenedor.querySelectorAll('.asignacion-personal');
            if (items.length > 1) {
                e.target.closest('.asignacion-personal').remove();
            }
        }
    });

    // Verificar fecha única al cambiar
    fechaInput.addEventListener('change', function() {
        const fecha = this.value;
        
        if (!fecha) return;
        
        fetch('verificar_fecha_guardia.php?fecha=' + encodeURIComponent(fecha))
            .then(response => response.json())
            .then(data => {
                const errorDiv = document.getElementById('fecha-error');
                
                if (data.existe) {
                    // Mostrar error
                    errorDiv.textContent = 'Ya existe una guardia para esta fecha';
                    fechaInput.classList.add('is-invalid');
                    submitBtn.disabled = true;
                    
                    // Mostrar alerta debajo del campo
                    const existingAlert = fechaInput.nextElementSibling;
                    if (!existingAlert || !existingAlert.classList.contains('alert')) {
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-danger mt-2';
                        alertDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>Ya existe una guardia registrada para esta fecha';
                        fechaInput.insertAdjacentElement('afterend', alertDiv);
                    }
                } else {
                    // Limpiar errores
                    errorDiv.textContent = '';
                    fechaInput.classList.remove('is-invalid');
                    submitBtn.disabled = false;
                    
                    // Eliminar alerta si existe
                    const existingAlert = fechaInput.nextElementSibling;
                    if (existingAlert && existingAlert.classList.contains('alert')) {
                        existingAlert.remove();
                    }
                }
            })
            .catch(error => {
                console.error('Error al verificar fecha:', error);
            });
    });

    // Validar al enviar el formulario
    form.addEventListener('submit', function(e) {
        const fecha = fechaInput.value;
        
        // Primero verificar fecha
        fetch('verificar_fecha_guardia.php?fecha=' + encodeURIComponent(fecha))
            .then(response => response.json())
            .then(data => {
                if (data.existe) {
                    e.preventDefault();
                    
                    // Configurar contenido del modal
                    document.getElementById('errorModalBody').innerHTML = `
                        <p>Ya existe una guardia registrada para la fecha seleccionada (${fecha}).</p>
                        <p>Por favor, seleccione una fecha diferente.</p>
                    `;
                    
                    // Mostrar modal
                    const modal = new bootstrap.Modal(document.getElementById('errorModal'));
                    modal.show();
                } else {
                    // Continuar con otras validaciones (duplicados de personal)
                    const selects = document.querySelectorAll('select[name*="[id_personal]"]');
                    const seleccionados = Array.from(selects).map(s => s.value);
                    const duplicados = seleccionados.filter((item, index) => seleccionados.indexOf(item) !== index);
                    
                    if (duplicados.length > 0) {
                        e.preventDefault();
                        
                        // Configurar contenido del modal
                        document.getElementById('errorModalBody').innerHTML = `
                            <p>No puedes asignar al mismo personal más de una vez en la misma guardia.</p>
                            <p class="fw-bold">Por favor, corrige las siguientes asignaciones:</p>
                            <ul>
                                ${duplicados.map(id => {
                                    const select = Array.from(selects).find(s => s.value === id);
                                    const nombre = select.options[select.selectedIndex].text;
                                    return `<li>${nombre}</li>`;
                                }).join('')}
                            </ul>
                        `;
                        
                        // Resaltar asignaciones duplicadas
                        selects.forEach(select => {
                            if (duplicados.includes(select.value)) {
                                const asignacion = select.closest('.asignacion-personal');
                                asignacion.classList.add('border-danger', 'bg-danger-light');
                                
                                // Scroll a la primera asignación duplicada
                                if (select.value === duplicados[0]) {
                                    setTimeout(() => {
                                        asignacion.scrollIntoView({ 
                                            behavior: 'smooth', 
                                            block: 'center'
                                        });
                                    }, 300);
                                }
                            }
                        });
                        
                        // Mostrar modal
                        const modal = new bootstrap.Modal(document.getElementById('errorModal'));
                        modal.show();
                        
                        // Limpiar estilos al cerrar el modal
                        document.getElementById('errorModal').addEventListener('hidden.bs.modal', function() {
                            document.querySelectorAll('.asignacion-personal').forEach(el => {
                                el.classList.remove('border-danger', 'bg-danger-light');
                            });
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error al verificar fecha:', error);
            });
    });
});
</script>
</body>
</html>