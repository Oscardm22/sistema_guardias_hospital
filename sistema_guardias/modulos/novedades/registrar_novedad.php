<?php
require_once __DIR__.'/../../includes/conexion.php';
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/funciones/funciones_autenticacion.php';
require_once __DIR__.'/../../includes/funciones/funciones_novedades.php';

date_default_timezone_set('America/Caracas');

if (!puede_crear_novedad()) {
    $_SESSION['error'] = "No tienes permisos para crear novedades";
    header('Location: listar_novedades.php');
    exit;
}

$esAdmin = ($_SESSION['usuario']['rol'] == 'admin');
$guardias = obtener_guardias_para_select($conn, $esAdmin);
$personal = obtener_personal_activo($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Nueva Novedad</title>
    
    <!-- Favicon -->
    <link rel="icon" href="../../assets/images/favicon.ico" type="image/x-icon">
    
    <!-- Bootstrap CSS -->
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    
    <!-- Estilos personalizados -->
    <link href="../../assets/css/styles_novedades.css" rel="stylesheet">
    
</head>
<body class="bg-light">
    <?php include __DIR__.'/../../includes/navbar.php'; ?>

    <div class="container py-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0"><i class="fas fa-plus-circle"></i> Registrar Nueva Novedad</h2>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <?php if (empty($guardias)): ?>
                <div class="alert alert-warning">No hay guardias disponibles para registrar novedades</div>
            <?php endif; ?>
            
            <form action="procesar_novedad.php" method="post">
                <!-- Campos ocultos para guardar información de la guardia -->
                <input type="hidden" id="id_guardia" name="id_guardia" required>
                <input type="hidden" id="guardia_date" name="guardia_date">
                
                <!-- Sección del Calendario -->
                <div class="form-group">
                    <label for="id_guardia">Guardia Relacionada</label>
                    <div id="calendar">
                        <?php if (empty($guardias)): ?>
                            <div class="alert-empty-calendar">
                                <i class="fas fa-calendar-times fa-3x mb-3 text-muted"></i>
                                <p class="mb-0">No hay guardias programadas para mostrar</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <small class="text-muted">Haz clic en una guardia para seleccionarla</small>
                    <div id="selected-guardia" class="mt-2 p-2 bg-light rounded d-none">
                        <strong>Guardia seleccionada:</strong>
                        <span id="guardia-info"></span>
                    </div>
                </div>

                <!-- Selección de personal -->
                <div id="personal-container" class="form-group">
                    <label for="id_personal_reporta">Personal que Reporta</label>
                    <select class="form-control" id="id_personal_reporta" name="id_personal_reporta" required>
                        <option value="">Seleccione personal</option>
                        <?php foreach($personal as $persona): ?>
                        <option value="<?= htmlspecialchars($persona['id_personal']) ?>" <?= $persona['id_personal'] == obtener_id_personal_usuario() ? 'selected' : '' ?>>
                            <?= htmlspecialchars($persona['nombre'] . ' ' . $persona['apellido']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Contenedor de múltiples novedades -->
                <div id="novedades-container">
                    <!-- Primera novedad -->
                    <div class="novedad-item" data-index="0">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="area_0">Área</label>
                                    <select class="form-control" id="area_0" name="novedades[0][area]" required>
                                        <option value="">Seleccione un área</option>
                                        <option value="Personal">Personal</option>
                                        <option value="Inteligencia">Inteligencia</option>
                                        <option value="Seguridad">Seguridad</option>
                                        <option value="Operaciones">Operaciones</option>
                                        <option value="Adiestramiento">Adiestramiento</option>
                                        <option value="Logistica">Logística</option>
                                        <option value="Informacion general">Informacion general</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="hora_0">Hora de la Novedad</label>
                                    <input type="time" class="form-control hora-input" id="hora_0" name="novedades[0][hora]" required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Descripción -->
                        <div class="form-group mt-3">
                            <label for="descripcion_0">Descripción de la Novedad</label>
                            <textarea class="form-control" id="descripcion_0" name="novedades[0][descripcion]" rows="3" required placeholder="Describa la novedad con todos los detalles relevantes"></textarea>
                        </div>
                        
                        <!-- Botón para eliminar esta novedad -->
                        <button type="button" class="btn btn-sm btn-outline-danger remove-novedad d-none">
                            <i class="fas fa-trash"></i> Eliminar esta novedad
                        </button>
                    </div>
                </div>
                
                <!-- Botón para agregar más novedades -->
                <button type="button" id="add-novedad" class="btn btn-outline-primary btn-add-novedad">
                    <i class="fas fa-plus"></i> Agregar otra novedad
                </button>
                
                <!-- Botones de acción -->
                <div class="form-group text-end mt-4">
                    <a href="listar_novedades.php" class="btn btn-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary" id="submit-btn" <?= empty($guardias) ? 'disabled' : '' ?>>
                        <i class="fas fa-save"></i> Registrar Todas las Novedades
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para detalles de guardia -->
<div class="modal fade" id="guardiaModal" tabindex="-1" aria-labelledby="guardiaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalGuardiaTitle">Detalles de Guardia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalGuardiaBody">
                <!-- Contenido dinámico -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="confirmarGuardiaBtn">Seleccionar esta guardia</button>
            </div>
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

<!-- Script para el calendario -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    
    <?php if (!empty($guardias)): ?>
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
            events: [
    <?php foreach($guardias as $guardia): ?>
    // Solo se incluirán las guardias permitidas (hoy si no es admin)
    {   id: '<?= $guardia['id_guardia'] ?>',
        title: 'Guardia',
        start: '<?= $guardia['fecha'] ?>',
        end: '<?= $guardia['fecha'] ?>',
        color: '#28a745',
        textColor: 'white'
    },
    <?php endforeach; ?>
],
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                
                const startDate = info.event.start ? new Date(info.event.start) : null;
                
                const formatDate = (date) => {
                    return date ? date.toLocaleString('es-ES', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                    }) : 'No especificada';
                };

                document.getElementById('modalGuardiaTitle').textContent = 'Detalles de Guardia';
                
                document.getElementById('modalGuardiaBody').innerHTML = `
                    <p><strong>Fecha:</strong> ${formatDate(startDate)}</p>
                    ${info.event.extendedProps.detalles ? `<p><strong>Detalles:</strong> ${info.event.extendedProps.detalles}</p>` : ''}
                `;
                
                const confirmBtn = document.getElementById('confirmarGuardiaBtn');
                confirmBtn.onclick = null;
                
                confirmBtn.onclick = function() {
                    if (!info.event.id) {
                        alert('Error: No se pudo obtener el ID de la guardia');
                        return;
                    }
                    
                    document.getElementById('id_guardia').value = info.event.id;
                    document.getElementById('selected-guardia').classList.remove('d-none');
                    
                    // Guardar la fecha de la guardia
                    const guardiaDate = info.event.start ? new Date(info.event.start) : null;
                    document.getElementById('guardia_date').value = guardiaDate ? guardiaDate.toISOString().split('T')[0] : '';
                    
                    // Mostrar información formateada
                    const displayDate = guardiaDate ? guardiaDate.toLocaleDateString('es-ES', {
                        weekday: 'short',
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    }) : 'Fecha no especificada';
                    
                    document.getElementById('guardia-info').textContent = 
                        `${info.event.title} - ${displayDate}`;
                    
                    document.getElementById('submit-btn').disabled = false;
                    
                    bootstrap.Modal.getInstance(document.getElementById('guardiaModal')).hide();
                };
                
                new bootstrap.Modal(document.getElementById('guardiaModal')).show();
            }
        });
        
        calendar.render();
    <?php endif; ?>

    // Contador para nuevas novedades
    let novedadCounter = 1;
    
    // Función para agregar nueva novedad
    document.getElementById('add-novedad').addEventListener('click', function() {
        const container = document.getElementById('novedades-container');
        const newIndex = novedadCounter++;
        
        const newNovedad = document.querySelector('.novedad-item').cloneNode(true);
        newNovedad.setAttribute('data-index', newIndex);
        
        const inputs = newNovedad.querySelectorAll('select, textarea, input');
        inputs.forEach(input => {
            const oldId = input.id;
            const newId = oldId.replace(/_0$/, `_${newIndex}`);
            input.id = newId;
            input.name = input.name.replace('[0]', `[${newIndex}]`);
            input.value = '';
        });
        
        newNovedad.querySelector('.remove-novedad').classList.remove('d-none');
        
        newNovedad.querySelector('.remove-novedad').addEventListener('click', function() {
            newNovedad.remove();
        });
        
        container.appendChild(newNovedad);
        newNovedad.scrollIntoView({ behavior: 'smooth' });
    });
    
    // Validación del formulario
    document.querySelector('form').addEventListener('submit', function(e) {
    const guardiaSeleccionada = document.getElementById('id_guardia').value;
    const personalSeleccionado = document.getElementById('id_personal_reporta').value;
    
    if (!guardiaSeleccionada) {
        e.preventDefault();
        // Usar un modal de Bootstrap en lugar de alert()
        const modalBody = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Debe seleccionar una guardia del calendario antes de enviar el formulario.
            </div>
        `;
        
        document.getElementById('modalGuardiaBody').innerHTML = modalBody;
        document.getElementById('modalGuardiaTitle').textContent = 'Error en el formulario';
        
        // Ocultar botón de confirmación ya que solo es un mensaje
        document.getElementById('confirmarGuardiaBtn').classList.add('d-none');
        
        const modal = new bootstrap.Modal(document.getElementById('guardiaModal'));
        modal.show();
        
        // Restaurar el botón cuando se cierre el modal
        document.getElementById('guardiaModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('confirmarGuardiaBtn').classList.remove('d-none');
        });
        
        return;
    }
    
    if (!personalSeleccionado) {
        e.preventDefault();
        const modalBody = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Debe seleccionar el personal que reporta la novedad.
            </div>
        `;
        
        document.getElementById('modalGuardiaBody').innerHTML = modalBody;
        document.getElementById('modalGuardiaTitle').textContent = 'Error en el formulario';
        document.getElementById('confirmarGuardiaBtn').classList.add('d-none');
        
        const modal = new bootstrap.Modal(document.getElementById('guardiaModal'));
        modal.show();
        
        document.getElementById('guardiaModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('confirmarGuardiaBtn').classList.remove('d-none');
        });
        
        return;
    }
});
});
</script>
</body>
</html>