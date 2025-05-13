// Variable para almacenar instancias de tooltips
let guardiasTooltips = [];
let activeTooltip = null;
let hoverTimeout = null;

/**
 * Oculta todos los tooltips de manera inmediata
 */
function hideAllTooltips() {
    if (hoverTimeout) {
        clearTimeout(hoverTimeout);
        hoverTimeout = null;
    }
    guardiasTooltips.forEach(tooltip => {
        if (tooltip && typeof tooltip.hide === 'function') {
            tooltip.hide();
        }
    });
    activeTooltip = null;
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
 * Inicializa los tooltips de manera optimizada
 */
function initGuardiasTooltips() {
    // Limpiar tooltips existentes
    guardiasTooltips.forEach(tooltip => {
        if (tooltip && typeof tooltip.dispose === 'function') {
            tooltip.dispose();
        }
    });
    
    guardiasTooltips = [];
    
    // Configuración común para todos los tooltips
    const tooltipOptions = {
        boundary: 'viewport',
        trigger: 'manual', // Control manual para mejor rendimiento
        animation: true,
        delay: 0 // Sin retraso
    };
    
    // Inicializar tooltips para las guardias
    const guardiaElements = document.querySelectorAll('.guardia-24h[data-bs-toggle="tooltip"]');
    const deleteButtons = document.querySelectorAll('.btn-eliminar[data-bs-toggle="tooltip"]');
    
    // Función para manejar hover de manera óptima
    const setupHover = (element, tooltip) => {
        element.addEventListener('mouseenter', () => {
            if (hoverTimeout) clearTimeout(hoverTimeout);
            hoverTimeout = setTimeout(() => {
                hideAllTooltips();
                tooltip.show();
                activeTooltip = tooltip;
                hoverTimeout = null;
            }, 50); // Pequeño retraso para evitar flickering
        });
        
        element.addEventListener('mouseleave', () => {
            if (hoverTimeout) clearTimeout(hoverTimeout);
            setTimeout(() => {
                if (activeTooltip === tooltip) {
                    tooltip.hide();
                    activeTooltip = null;
                }
            }, 100);
        });
    };
    
    // Tooltips para las guardias
    guardiaElements.forEach(el => {
        const tooltip = new bootstrap.Tooltip(el, tooltipOptions);
        setupHover(el, tooltip);
        guardiasTooltips.push(tooltip);
    });
    
    // Tooltips para los botones de eliminar
    deleteButtons.forEach(el => {
        const tooltip = new bootstrap.Tooltip(el, {
            ...tooltipOptions,
            placement: 'top'
        });
        
        el.addEventListener('mouseenter', (e) => {
            if (hoverTimeout) clearTimeout(hoverTimeout);
            hoverTimeout = setTimeout(() => {
                hideAllTooltips();
                tooltip.show();
                activeTooltip = tooltip;
                hoverTimeout = null;
                e.stopPropagation();
            }, 50);
        });
        
        el.addEventListener('mouseleave', () => {
            if (hoverTimeout) clearTimeout(hoverTimeout);
            setTimeout(() => {
                if (activeTooltip === tooltip) {
                    tooltip.hide();
                    activeTooltip = null;
                }
            }, 100);
        });
        
        guardiasTooltips.push(tooltip);
    });

    // Manejar formularios de eliminación
    document.querySelectorAll('.form-eliminar').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('¿Estás seguro de eliminar esta guardia?')) {
                e.preventDefault();
                hideAllTooltips();
            }
        });
    });
}

// Inicialización al cargar la página
document.addEventListener('DOMContentLoaded', initGuardiasTooltips);

// Manejar clicks globales
document.addEventListener('click', function(e) {
    if (e.target.closest('.form-eliminar')) {
        hideAllTooltips();
    }
});