<?php
/**
 * Funciones relacionadas con autenticación y permisos
 */

function tiene_permiso($permiso) {
    if (!isset($_SESSION['usuario']['permisos'])) {
        return false;
    }
    
    // Admin tiene todos los permisos
    if ($_SESSION['usuario']['rol'] === 'admin') {
        return true;
    }
    
    return in_array($permiso, $_SESSION['usuario']['permisos']);
}

function es_admin() {
    return isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'admin';
}

function es_personal() {
    return isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'personal';
}

function obtener_id_personal_usuario() {
    return $_SESSION['usuario']['personal']['id'] ?? null;
}

function puede_gestionar_personal() {
    return es_admin() && tiene_permiso('gestion_personal');
}

function puede_gestionar_usuarios() {
    return es_admin() && tiene_permiso('gestion_usuarios');
}