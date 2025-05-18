<?php
require_once __DIR__.'/../../includes/conexion.php';
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/funciones/funciones_autenticacion.php';
require_once __DIR__.'/../../includes/funciones/funciones_novedades.php';

// Validaciones iniciales y permisos
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No se especificó la novedad a editar";
    header('Location: listar_novedades.php');
    exit;
}

$id_novedad = (int)$_GET['id'];
$novedad = obtener_novedad($id_novedad, $conn);
if (!$novedad) {
    $_SESSION['error'] = "Novedad no encontrada";
    header('Location: listar_novedades.php');
    exit;
}

if (!puede_editar_novedad($id_novedad, $conn)) {
    $_SESSION['error'] = "No tienes permisos para editar esta novedad";
    header('Location: listar_novedades.php');
    exit;
}

$guardias = obtener_guardias_para_select($conn);
$personal = obtener_personal_activo($conn);

// Procesar POST para actualizar novedad
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'descripcion' => trim($_POST['descripcion']),
        'area' => $_POST['area'],
        'id_guardia' => (int)$_POST['id_guardia'],
        'id_personal_reporta' => (int)$_POST['id_personal_reporta']
    ];

    $errores = validar_datos_novedad($datos);

    if (empty($errores)) {
        if (actualizar_novedad($id_novedad, $datos, $conn)) {
            $_SESSION['exito'] = "Novedad actualizada correctamente";
            header('Location: detalle_novedad.php?id=' . $id_novedad);
            exit;
        } else {
            $_SESSION['error'] = "Error al actualizar la novedad";
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errores);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Editar Novedad</title>

<!-- Favicon -->
<link rel="icon" href="../../assets/images/favicon.ico" type="image/x-icon">
<link href="../../assets/css/bootstrap.min.css" rel="stylesheet" />
<link href="../../assets/css/styles_navbar.css" rel="stylesheet" />
<link href="../../assets/css/styles_listar_novedades.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />

<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />

<style>
    #calendar {
        width: 100%;
        height: 600px;
        margin: 20px 0;
        background-color: white;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        padding: 10px;
    }
    .fc-event {
        cursor: pointer;
        font-size: 0.85em;
        padding: 2px 5px;
        margin: 1px 0;
        border-radius: 3px;
    }
    #selected-guardia {
        transition: all 0.3s ease;
    }
</style>
</head>
<body class="bg-light">
<?php include __DIR__.'/../../includes/navbar.php'; ?>

<div class="container py-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0"><i class="fas fa-edit"></i> Editar Novedad</h2>
        </div>
        <div class="card-body">

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="row">
                    <div class="col-md-6">
                        <label for="id_guardia">Guardia Relacionada</label>
                        <input type="hidden" id="id_guardia" name="id_guardia" required value="<?= htmlspecialchars($novedad['id_guardia']) ?>">
                        <div id="calendar"></div>
                        <small class="text-muted">Haz clic en una guardia para seleccionarla</small>
                        <div id="selected-guardia" class="mt-2 p-2 bg-light rounded <?= $novedad['id_guardia'] ? '' : 'd-none' ?>">
                            <strong>Guardia seleccionada:</strong>
                            <span id="guardia-info"></span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="id_personal_reporta">Personal que Reporta</label>
                        <select class="form-control" id="id_personal_reporta" name="id_personal_reporta" required>
                            <option value="">Seleccione personal</option>
                            <?php foreach($personal as $persona): ?>
                            <option value="<?= htmlspecialchars($persona['id_personal']) ?>" <?= $persona['id_personal'] == $novedad['id_personal_reporta'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($persona['nombre'] . ' ' . $persona['apellido']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>

                        <label for="area" class="mt-3">Área</label>
                        <select class="form-control" id="area" name="area" required>
                            <option value="">Seleccione un área</option>
                            <option value="Personal" <?= $novedad['area'] == 'Personal' ? 'selected' : '' ?>>Personal</option>
                            <option value="Inteligencia" <?= $novedad['area'] == 'Inteligencia' ? 'selected' : '' ?>>Inteligencia</option>
                            <option value="Seguridad" <?= $novedad['area'] == 'Seguridad' ? 'selected' : '' ?>>Seguridad</option>
                            <option value="Operaciones" <?= $novedad['area'] == 'Operaciones' ? 'selected' : '' ?>>Operaciones</option>
                            <option value="Adiestramiento" <?= $novedad['area'] == 'Adiestramiento' ? 'selected' : '' ?>>Adiestramiento</option>
                            <option value="Logistica" <?= $novedad['area'] == 'Logistica' ? 'selected' : '' ?>>Logística</option>
                            <option value="Información general" <?= $novedad['area'] == 'Información general' ? 'selected' : '' ?>>Información general</option>
                        </select>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <label for="descripcion">Descripción de la Novedad</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="5" required><?= htmlspecialchars($novedad['descripcion']) ?></textarea>
                </div>

                <div class="form-group text-end mt-3">
                    <a href="detalle_novedad.php?id=<?= $id_novedad ?>" class="btn btn-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary" id="submit-btn">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__.'/../../includes/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.min.js'></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const inputGuardia = document.getElementById('id_guardia');
    const selectedGuardiaDiv = document.getElementById('selected-guardia');
    const guardiaInfoSpan = document.getElementById('guardia-info');

    // Función para mostrar la guardia seleccionada al cargar la página
    function mostrarGuardiaSeleccionada() {
        const idSeleccionado = inputGuardia.value;
        if (!idSeleccionado) {
            selectedGuardiaDiv.classList.add('d-none');
            guardiaInfoSpan.textContent = '';
            return;
        }
        const evento = eventos.find(e => e.id === idSeleccionado);
        if (evento) {
            const start = new Date(evento.start);
            const fechaStr = start.toLocaleDateString('es-ES', {
                weekday: 'short', year: 'numeric', month: 'short', day: 'numeric'
            });
            guardiaInfoSpan.textContent = `${evento.title} - ${fechaStr}`;
            selectedGuardiaDiv.classList.remove('d-none');
        }
    }

    const eventos = [
        <?php foreach($guardias as $guardia): ?>
        {   id: '<?= $guardia['id_guardia'] ?>',
            title: '<?= addslashes($guardia['tipo_guardia']) ?>',
            start: '<?= $guardia['fecha_inicio'] ?>',
            end: '<?= $guardia['fecha_fin'] ?>',
            color: '<?= $guardia['tipo_guardia'] === 'Diurna' ? '#28a745' : '#007bff' ?>',
            textColor: 'white',
        },
        <?php endforeach; ?>
    ];

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        navLinks: true,
        dayMaxEvents: true,
        eventDisplay: 'block',
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        },
        events: eventos,
        eventClick: function(info) {
            info.jsEvent.preventDefault();

            const startDate = info.event.start ? new Date(info.event.start) : null;
            const displayDate = startDate ? startDate.toLocaleDateString('es-ES', {
                weekday: 'short',
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            }) : 'Fecha no especificada';

            document.getElementById('id_guardia').value = info.event.id;
            mostrarGuardiaSeleccionada();
        }
    });

    calendar.render();

    mostrarGuardiaSeleccionada(); // Mostrar la guardia seleccionada al cargar
});
</script>
</body>
</html>
