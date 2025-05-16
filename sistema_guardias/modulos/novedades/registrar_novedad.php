<?php
require_once __DIR__.'/../../includes/conexion.php';
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/funciones/funciones_autenticacion.php';
require_once __DIR__.'/../../includes/funciones/funciones_novedades.php';

if (!puede_crear_novedad()) {
    $_SESSION['error'] = "No tienes permisos para crear novedades";
    header('Location: listar_novedades.php');
    exit;
}

$guardias = obtener_guardias_para_select($conn);
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
    <link href="../../assets/css/styles_navbar.css" rel="stylesheet">
    <style>
        .container {
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        #calendar {
            width: 100%;
            height: 600px;
            margin: 20px auto;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            padding: 10px;
        }
        .fc-header-toolbar {
            margin-bottom: 1em;
        }
        .fc-event {
            cursor: pointer;
            font-size: 0.85em;
            padding: 2px 5px;
            margin: 1px 0;
            border-radius: 3px;
        }
        .fc-daygrid-event {
            white-space: normal;
            word-wrap: break-word;
        }
        #selected-guardia {
            transition: all 0.3s ease;
        }
        .card {
            margin-bottom: 20px;
        }
        .alert-empty-calendar {
            margin: 20px;
            padding: 20px;
            text-align: center;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
    </style>
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
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="id_guardia">Guardia Relacionada</label>
                                <input type="hidden" id="id_guardia" name="id_guardia" required>
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
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
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
                            
                            <div class="form-group">
                                <label for="area">Área</label>
                                <select class="form-control" id="area" name="area" required>
                                    <option value="">Seleccione un área</option>
                                    <option value="Personal">Personal</option>
                                    <option value="Seguridad">Seguridad</option>
                                    <option value="Operaciones">Operaciones</option>
                                    <option value="Logistica">Logística</option>
                                    <option value="Información general">Información general</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción de la Novedad</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="5" required placeholder="Describa la novedad con todos los detalles relevantes"></textarea>
                    </div>
                    
                    <div class="form-group text-end">
                        <a href="listar_novedades.php" class="btn btn-secondary me-2">Cancelar</a>
                        <button type="submit" class="btn btn-primary" id="submit-btn" <?= empty($guardias) ? 'disabled' : '' ?>>
                            <i class="fas fa-save"></i> Registrar Novedad
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
                {   id: '<?= $guardia['id_guardia'] ?>',
                    title: '<?= addslashes($guardia['tipo_guardia']) ?>',
                    start: '<?= $guardia['fecha_inicio'] ?>',
                    end: '<?= $guardia['fecha_fin'] ?>',
                    color: '<?= $guardia['tipo_guardia'] === 'Diurna' ? '#28a745' : '#007bff' ?>',
                    textColor: 'white',
                    extendedProps: {
                        detalles: '<?= isset($guardia['detalles']) ? addslashes($guardia['detalles']) : '' ?>',
                        fechaInicio: '<?= $guardia['fecha_inicio'] ?>',
                        fechaFin: '<?= $guardia['fecha_fin'] ?>'
                    }
                },
                <?php endforeach; ?>
            ],
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                
                // Crear objetos Date seguros
                const startDate = info.event.start ? new Date(info.event.start) : null;
                const endDate = info.event.end ? new Date(info.event.end) : null;
                
                // Formatear fechas con verificación
                const formatDate = (date) => {
                    return date ? date.toLocaleString('es-ES', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    }) : 'No especificada';
                };

                document.getElementById('modalGuardiaTitle').textContent = `Guardia ${info.event.title}`;
                
                document.getElementById('modalGuardiaBody').innerHTML = `
                    <p><strong>Tipo:</strong> ${info.event.title}</p>
                    <p><strong>Inicio:</strong> ${formatDate(startDate)}</p>
                    <p><strong>Fin:</strong> ${formatDate(endDate)}</p>
                    ${info.event.extendedProps.detalles ? `<p><strong>Detalles:</strong> ${info.event.extendedProps.detalles}</p>` : ''}
                `;
                
                // Configurar botón de confirmación
                const confirmBtn = document.getElementById('confirmarGuardiaBtn');
                confirmBtn.onclick = null; // Limpiar eventos previos
                
                confirmBtn.onclick = function() {
                    if (!info.event.id) {
                        alert('Error: No se pudo obtener el ID de la guardia');
                        return;
                    }
                    
                    document.getElementById('id_guardia').value = info.event.id;
                    document.getElementById('selected-guardia').classList.remove('d-none');
                    
                    // Mostrar información formateada
                    const displayDate = startDate ? startDate.toLocaleDateString('es-ES', {
                        weekday: 'short',
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    }) : 'Fecha no especificada';
                    
                    document.getElementById('guardia-info').textContent = 
                        `${info.event.title} - ${displayDate}`;
                    
                    // Habilitar el botón de enviar
                    document.getElementById('submit-btn').disabled = false;
                    
                    // Cerrar el modal
                    bootstrap.Modal.getInstance(document.getElementById('guardiaModal')).hide();
                };
                
                // Mostrar el modal
                new bootstrap.Modal(document.getElementById('guardiaModal')).show();
            },
            eventDidMount: function(info) {
                // Tooltip seguro
                if (info.event.start) {
                    const startDate = new Date(info.event.start);
                    const endDate = info.event.end ? new Date(info.event.end) : null;
                    
                    const startStr = startDate.toLocaleString('es-ES');
                    const endStr = endDate ? endDate.toLocaleString('es-ES') : 'Sin fecha de fin';
                    
                    info.el.setAttribute('title', `${info.event.title}\nInicio: ${startStr}\nFin: ${endStr}`);
                }
            }
        });
        
        calendar.render();
    <?php endif; ?>

    // Validación del formulario
    document.querySelector('form').addEventListener('submit', function(e) {
        if (!document.getElementById('id_guardia').value) {
            e.preventDefault();
            alert('Por favor seleccione una guardia antes de enviar el formulario');
        }
    });
});
</script>
</body>
</html>