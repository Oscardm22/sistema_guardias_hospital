<?php
/**
 * Funciones relacionadas con información de usuarios
 */

function nombre_completo_usuario() {
    if (!isset($_SESSION['usuario'])) {
        return 'Invitado';
    }
    return $_SESSION['usuario']['rol'] === 'admin' 
        ? 'Administrador del Sistema' 
        : 'Usuario General';
}

function rol_usuario() {
    if (!isset($_SESSION['usuario']['rol'])) {
        return 'Invitado';
    }
    
    return $_SESSION['usuario']['rol'] === 'admin' ? 'Administrador' : 'Personal Médico';
}