<?php
// includes/funciones/funciones_servicios.php

/**
 * Obtiene todos los servicios registrados
 */
function obtenerServicios($conexion) {
    $servicios = [];
    $query = "SELECT id_servicio, tipo, medida, unidad, fecha_registro 
              FROM servicios 
              ORDER BY fecha_registro DESC";
    
    if ($resultado = $conexion->query($query)) {
        while ($fila = $resultado->fetch_assoc()) {
            $servicios[] = $fila;
        }
        $resultado->free();
    }
    
    return $servicios;
}

/**
 * Registra un nuevo servicio
 */
function registrarServicio($conexion, $tipo, $medida, $unidad) {
    $query = "INSERT INTO servicios (tipo, medida, unidad) VALUES (?, ?, ?)";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("sds", $tipo, $medida, $unidad);
    return $stmt->execute();
}

/**
 * Obtiene estadísticas de servicios
 */
function obtenerEstadisticasServicios($conexion) {
    $query = "SELECT 
                tipo, 
                AVG(medida) as promedio, 
                MAX(medida) as maximo, 
                MIN(medida) as minimo,
                COUNT(*) as registros
              FROM servicios
              GROUP BY tipo";
    
    $estadisticas = [];
    if ($resultado = $conexion->query($query)) {
        while ($fila = $resultado->fetch_assoc()) {
            $estadisticas[] = $fila;
        }
        $resultado->free();
    }
    
    return $estadisticas;
}