<?php
require_once __DIR__.'/../../includes/conexion.php';
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/funciones/funciones_autenticacion.php';
require_once __DIR__.'/../../includes/funciones/funciones_novedades.php';
require_once __DIR__.'/../../includes/funciones/funciones_guardias.php';


// Validaciones iniciales y permisos
if (!isset($_GET['id'])) {
    $_SESSION['error'] = [
        'titulo' => 'Error',
        'mensaje' => 'No se especificó la novedad a editar',
        'tipo' => 'danger'
    ];
    header('Location: listar_novedades.php');
    exit;
}

$id_novedad = (int)$_GET['id'];
$novedad = obtener_novedad($conn, $id_novedad);
if (!$novedad) {
    $_SESSION['error'] = [
        'titulo' => 'Error',
        'mensaje' => 'Novedad no encontrada',
        'tipo' => 'danger'
    ];
    header('Location: listar_novedades.php');
    exit;
}

if (!puede_editar_novedad($id_novedad, $conn)) {
    $_SESSION['error'] = [
        'titulo' => 'Error',
        'mensaje' => 'No tienes permisos para editar esta novedad',
        'tipo' => 'danger'
    ];
    header('Location: listar_novedades.php');
    exit;
}

$guardias = obtener_guardias_para_select($conn);
$personal = obtener_personal_activo($conn);

// Extraer hora de la fecha de registro
$hora_novedad = date('H:i', strtotime($novedad['fecha_registro']));

// Procesar POST para actualizar novedad
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y limpiar datos del formulario
    $datos = [
        'descripcion' => trim($_POST['descripcion'] ?? ''),
        'area' => trim($_POST['area'] ?? ''),
        'id_guardia' => (int)($_POST['id_guardia'] ?? 0),
        'id_personal_reporta' => (int)($_POST['id_personal_reporta'] ?? 0),
        'hora' => trim($_POST['hora'] ?? '')
    ];
    
    // Validar datos
    $errores = [];
    if (empty($datos['descripcion'])) $errores[] = "La descripción es requerida";
    if (empty($datos['area'])) $errores[] = "El área es requerida";
    if (empty($datos['id_guardia'])) $errores[] = "Debe seleccionar una guardia";
    if (empty($datos['id_personal_reporta'])) $errores[] = "Debe seleccionar el personal que reporta";
    if (empty($datos['hora']) || !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $datos['hora'])) {
        $errores[] = "La hora es inválida o no tiene el formato correcto (HH:MM)";
    }
    
    if (empty($errores)) {
        // Combinar fecha de guardia con hora de novedad
        $guardia = obtener_guardia($conn, $datos['id_guardia']);
        $fecha_completa = $guardia['fecha'] . ' ' . $datos['hora'] . ':00';
        
        // Actualizar novedad
        $stmt = $conn->prepare("UPDATE novedades SET 
            id_guardia = ?, 
            id_personal_reporta = ?, 
            area = ?, 
            descripcion = ?, 
            fecha_registro = ?
            WHERE id_novedad = ?");
        
        $stmt->bind_param("iisssi", 
            $datos['id_guardia'],
            $datos['id_personal_reporta'],
            $datos['area'],
            $datos['descripcion'],
            $fecha_completa,
            $id_novedad
        );
        
        if ($stmt->execute()) {
            $_SESSION['exito'] = [
                'titulo' => 'Éxito',
                'mensaje' => 'Novedad actualizada correctamente',
                'tipo' => 'success'
            ];
            header('Location: detalle_novedad.php?id=' . $id_novedad);
            exit;
        } else {
            $_SESSION['error'] = [
                'titulo' => 'Error',
                'mensaje' => 'Error al actualizar la novedad: ' . $conn->error,
                'tipo' => 'danger'
            ];
        }
    } else {
        $_SESSION['error'] = [
            'titulo' => 'Error',
            'mensaje' => implode("<br>", $errores),
            'tipo' => 'danger'
        ];
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

<!-- Estilos personalizados -->
<link href="../../assets/css/styles_novedades.css" rel="stylesheet">

</head>
<body class="bg-light">
<?php include __DIR__.'/../../includes/navbar.php'; ?>

<!-- Contenedor para mensajes -->
<div class="container alert-messages">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-<?= $_SESSION['error']['tipo'] ?> alert-dismissible fade show">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle fs-3 me-3"></i>
                <div>
                    <h5 class="alert-heading mb-1"><?= htmlspecialchars($_SESSION['error']['titulo']) ?></h5>
                    <p class="mb-0"><?= htmlspecialchars($_SESSION['error']['mensaje']) ?></p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
</div>

<div class="container py-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0"><i class="fas fa-edit"></i> Editar Novedad</h2>
        </div>
        <div class="card-body">
            <?php if (empty($guardias)): ?>
                <div class="alert alert-warning">No hay guardias disponibles para editar novedades</div>
            <?php endif; ?>
            
            <form method="post">
                <!-- Campos ocultos para guardar información de la guardia -->
                <input type="hidden" id="id_guardia" name="id_guardia" required value="<?= htmlspecialchars($novedad['id_guardia']) ?>">
                <input type="hidden" id="guardia_date" name="guardia_date" value="<?= date('Y-m-d', strtotime($novedad['fecha_guardia'])) ?>">
                
                <!-- Sección del Calendario -->
                <div class="form-group">
                    <label for="id_guardia">Guardia Relacionada</label>
                    <div id="calendar" class="calendar-container">
                        <?php if (empty($guardias)): ?>
                            <div class="alert-empty-calendar">
                                <i class="fas fa-calendar-times fa-3x mb-3 text-muted"></i>
                                <p class="mb-0">No hay guardias programadas para mostrar</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <small class="text-muted">Haz clic en una guardia para seleccionarla</small>
                    <div id="selected-guardia" class="mt-2 p-2 bg-light rounded <?= $novedad['id_guardia'] ? '' : 'd-none' ?>">
                        <strong>Guardia seleccionada:</strong>
                        <span id="guardia-info"></span>
                    </div>
                </div>

                <!-- Sección de campos personales -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="id_personal_reporta">Personal que Reporta</label>
                            <select class="form-control" id="id_personal_reporta" name="id_personal_reporta" required>
                                <option value="">Seleccione personal</option>
                                <?php foreach($personal as $persona): ?>
                                <option value="<?= htmlspecialchars($persona['id_personal']) ?>" <?= $persona['id_personal'] == $novedad['id_personal_reporta'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($persona['nombre'] . ' ' . $persona['apellido']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="area">Área</label>
                            <select class="form-control" id="area" name="area" required>
                                <option value="">Seleccione un área</option>
                                <option value="Personal" <?= $novedad['area'] == 'Personal' ? 'selected' : '' ?>>Personal</option>
                                <option value="Inteligencia" <?= $novedad['area'] == 'Inteligencia' ? 'selected' : '' ?>>Inteligencia</option>
                                <option value="Seguridad" <?= $novedad['area'] == 'Seguridad' ? 'selected' : '' ?>>Seguridad</option>
                                <option value="Operaciones" <?= $novedad['area'] == 'Operaciones' ? 'selected' : '' ?>>Operaciones</option>
                                <option value="Adiestramiento" <?= $novedad['area'] == 'Adiestramiento' ? 'selected' : '' ?>>Adiestramiento</option>
                                <option value="Logistica" <?= $novedad['area'] == 'Logistica' ? 'selected' : '' ?>>Logística</option>
                                <option value="Informacion general" <?= $novedad['area'] == 'Informacion general' ? 'selected' : '' ?>>Informacion general</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="hora">Hora de la Novedad</label>
                            <input type="time" class="form-control" id="hora" name="hora" required value="<?= htmlspecialchars($hora_novedad) ?>">
                        </div>
                    </div>
                </div>
                
                <!-- Descripción -->
                <div class="form-group mt-3">
                    <label for="descripcion">Descripción de la Novedad</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="5" required><?= htmlspecialchars($novedad['descripcion']) ?></textarea>
                </div>
                
                <!-- Botones -->
                <div class="form-group text-end mt-4">
                    <a href="detalle_novedad.php?id=<?= $id_novedad ?>" class="btn btn-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary" id="submit-btn" <?= empty($guardias) ? 'disabled' : '' ?>>
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__.'/../../includes/footer.php'; ?>

<!-- jQuery (necesario para Bootstrap) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS -->
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
<!-- FullCalendar JS -->
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
        
        // Buscar la guardia seleccionada
        const evento = eventos.find(e => e.id === idSeleccionado);
        if (evento) {
            const startDate = new Date(evento.start + 'T00:00:00');
            const fechaStr = startDate.toLocaleDateString('es-ES', {
                weekday: 'long', 
                day: 'numeric', 
                month: 'long', 
                year: 'numeric',
                timeZone: 'UTC'
            });
            guardiaInfoSpan.textContent = `${evento.title} - ${fechaStr}`;
            selectedGuardiaDiv.classList.remove('d-none');
        }
    }

    const eventos = [
        <?php foreach($guardias as $guardia): ?>
        {   id: '<?= $guardia['id_guardia'] ?>',
            title: 'Guardia',
            start: '<?= $guardia['fecha'] ?>',
            end: '<?= $guardia['fecha'] ?>',
            color: '#28a745',
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
            document.getElementById('guardia_date').value = info.event.start ? info.event.start.toISOString().split('T')[0] : '';
            mostrarGuardiaSeleccionada();
        }
    });

    calendar.render();
    mostrarGuardiaSeleccionada(); // Mostrar la guardia seleccionada al cargar
});
</script>
</body>
</html>