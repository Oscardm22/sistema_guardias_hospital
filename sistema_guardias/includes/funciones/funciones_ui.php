<?php
/**
 * Funciones de ayuda para la interfaz de usuario
 */

function formatear_fecha($fecha, $formato = 'd/m/Y') {
    if (empty($fecha)) return '';
    $date = new DateTime($fecha);
    return $date->format($formato);
}