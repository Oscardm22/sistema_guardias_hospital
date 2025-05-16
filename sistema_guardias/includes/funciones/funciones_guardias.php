<?php
/**
 * Funciones específicas para gestión de guardias
 */

function puede_crear_guardia() {
    return es_admin(); // Solo admin puede crear
}

function puede_editar_guardia() {
    return es_admin(); // Solo admin puede editar
}

function puede_eliminar_guardia() {
    return es_admin(); // Solo admin puede eliminar
}

function formatear_tipo_guardia($tipo) {
    $tipos = [
        'Diurna' => '<span class="badge bg-success">Diurna</span>',
        'Nocturna' => '<span class="badge bg-dark">Nocturna</span>'
    ];
    return $tipos[$tipo] ?? $tipo;
}

/**
 * Verifica si el usuario actual puede ver una guardia específica
 * 
 * @return bool True si tiene permisos, False si no
 */
function puede_ver_guardia() {
    // Admin y personal pueden ver guardias (ajusta según tus necesidades)
    return es_admin() || es_personal();
    
    // Alternativa más granular:
    // return tiene_permiso('ver_guardias') || es_admin();
}