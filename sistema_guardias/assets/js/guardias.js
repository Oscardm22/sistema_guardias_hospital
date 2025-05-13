// Variable para almacenar instancias de tooltips
let guardiasTooltips = [];

/**
 * Oculta todos los tooltips de manera controlada
 */
function hideAllTooltips() {
    guardiasTooltips.forEach(tooltip => {
        if (tooltip && typeof tooltip.hide === 'function') {
            tooltip.hide();
        }
    });
}

/**
 * Maneja el clic en las celdas de guardia
 */
function handleCellClick(event, idGuardia) {
    const deleteButton = event.target.closest('.btn-eliminar, .position-absolute');
    if (!deleteButton) {
        window.location.href = 'editar_guardia.php?id=' + idGuardia;
    }
}

/**
 * Inicializa los tooltips correctamente
 */
function initGuardiasTooltips() {
    // Limpiar tooltips existentes
    guardiasTooltips.forEach(tooltip => {
        if (tooltip && typeof tooltip.dispose === 'function') {
            tooltip.dispose();
        }
    });
    
    guardiasTooltips = [];
    
    // Inicializar tooltips
    const elements = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    
    guardiasTooltips = elements.map(el => {
        const tooltip = new bootstrap.Tooltip(el, {
            boundary: 'viewport',
            trigger: 'hover',
            animation: true,
            delay: { show: 300, hide: 100 }
        });
        
        // Manejar el cierre del tooltip cuando se hace clic en el botón de eliminar
        el.addEventListener('mouseleave', () => {
            // Pequeño retraso para evitar cerrar el tooltip durante la confirmación
            setTimeout(() => {
                if (!document.querySelector('.tooltip.show')) {
                    tooltip.hide();
                }
            }, 100);
        });
        
        return tooltip;
    });

    // Manejar formularios de eliminación
    document.querySelectorAll('.form-eliminar').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('¿Estás seguro de eliminar esta guardia?')) {
                e.preventDefault();
                // Solo ocultar el tooltip actual sin reinicializar todos
                hideAllTooltips();
            }
        });
    });
}

// Inicialización al cargar la página
document.addEventListener('DOMContentLoaded', initGuardiasTooltips);

// Manejar el evento de confirmación para evitar problemas con los tooltips
document.addEventListener('click', function(e) {
    if (e.target.closest('.form-eliminar')) {
        hideAllTooltips();
    }
});