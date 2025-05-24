<?php
/**
 * Obtiene todos los servicios registrados
 */
function obtenerServicios($conn) {
    $result = $conn->query("SELECT s.*, p.nombre as nombre_responsable, p.apellido 
                           FROM servicios s
                           LEFT JOIN personal p ON s.responsable = p.id_personal
                           ORDER BY s.fecha_registro DESC");
    
    $servicios = [];
    while ($row = $result->fetch_assoc()) {
        $servicios[] = $row;
    }
    return $servicios;
}

/**
 * Registra un nuevo servicio
 */
function registrarServicio($conn, $tipo, $medida, $unidad, $observaciones, $responsable) {
    $stmt = $conn->prepare("INSERT INTO servicios (tipo, medida, unidad, observaciones, responsable) 
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sdssi", $tipo, $medida, $unidad, $observaciones, $responsable);
    return $stmt->execute();
}

/**
 * Obtiene estadísticas de servicios
 */
function obtenerEstadisticasServicios($conn) {
    $stats = [];
    
    // Estadísticas para agua
    $result = $conn->query("SELECT 
                           'agua' as tipo,
                           AVG(medida) as promedio,
                           MAX(medida) as maximo,
                           MIN(medida) as minimo
                           FROM servicios WHERE tipo = 'agua'");
    $stats[] = $result->fetch_assoc();
    
    // Estadísticas para combustible
    $result = $conn->query("SELECT 
                           'combustible' as tipo,
                           AVG(medida) as promedio,
                           MAX(medida) as maximo,
                           MIN(medida) as minimo
                           FROM servicios WHERE tipo = 'combustible'");
    $stats[] = $result->fetch_assoc();
    
    return $stats;
}

function obtenerNivelesActuales($conn) {
    $niveles = [];
    
    // Obtener último nivel de agua
    $result = $conn->query("SELECT medida, unidad, fecha_registro 
                           FROM servicios 
                           WHERE tipo = 'agua' 
                           ORDER BY fecha_registro DESC 
                           LIMIT 1");
    $niveles['agua'] = $result->fetch_assoc();
    
    // Obtener último nivel de combustible
    $result = $conn->query("SELECT medida, unidad, fecha_registro 
                           FROM servicios 
                           WHERE tipo = 'combustible' 
                           ORDER BY fecha_registro DESC 
                           LIMIT 1");
    $niveles['combustible'] = $result->fetch_assoc();
    
    return $niveles;
}

function obtenerDatosGraficos($conn, $tipo) {
    $stmt = $conn->prepare("SELECT DATE(fecha_registro) as fecha, medida FROM servicios WHERE tipo = ? ORDER BY fecha_registro ASC");
    $stmt->bind_param("s", $tipo);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $datos = [];
    while ($fila = $resultado->fetch_assoc()) {
        $datos[] = $fila;
    }
    return $datos;
}

function obtenerServicioPorId($conn, $id_servicio) {
    $stmt = $conn->prepare("SELECT * FROM servicios WHERE id_servicio = ?");
    $stmt->bind_param("i", $id_servicio);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function actualizarServicio($conn, $id_servicio, $tipo, $medida, $unidad, $observaciones, $responsable) {
    $stmt = $conn->prepare("UPDATE servicios 
                          SET tipo = ?, medida = ?, unidad = ?, observaciones = ?, responsable = ?
                          WHERE id_servicio = ?");
    $stmt->bind_param("sdssii", $tipo, $medida, $unidad, $observaciones, $responsable, $id_servicio);
    return $stmt->execute();
}

function eliminarServicio($conn, $id_servicio) {
    $stmt = $conn->prepare("DELETE FROM servicios WHERE id_servicio = ?");
    $stmt->bind_param("i", $id_servicio);
    return $stmt->execute();
}