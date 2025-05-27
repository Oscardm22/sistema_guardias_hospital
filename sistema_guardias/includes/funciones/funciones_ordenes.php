<?php
require_once __DIR__ . '/../../includes/conexion.php';

function crearOrdenSalida($datos) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO ordenes_salida 
        (destino, motivo, fecha_salida, fecha_retorno, id_vehiculo, id_personal) 
        VALUES (?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("ssssii", 
        $datos['destino'],
        $datos['motivo'],
        $datos['fecha_salida'],
        $datos['fecha_retorno'],
        $datos['id_vehiculo'],
        $datos['id_personal']
    );
    
    return $stmt->execute();
}

function obtenerOrdenes($filtros = []) {
    global $conn;
    
    $query = "SELECT o.*, v.placa, v.marca, v.tipo, p.nombre, p.apellido, p.grado
              FROM ordenes_salida o
              JOIN vehiculos v ON o.id_vehiculo = v.id_vehiculo
              JOIN personal p ON o.id_personal = p.id_personal
              WHERE 1=1";
    
    $params = [];
    $types = "";
    
    // Filtro por fechas
    if (!empty($filtros['desde'])) {
        $query .= " AND o.fecha_salida >= ?";
        $params[] = $filtros['desde'];
        $types .= "s";
    }
    
    if (!empty($filtros['hasta'])) {
        $query .= " AND o.fecha_salida <= ?";
        $params[] = $filtros['hasta'];
        $types .= "s";
    }
    
    // Ordenar por fecha de salida descendente
    $query .= " ORDER BY o.fecha_salida DESC";
    
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    return $stmt->get_result();
}

function obtenerOrdenPorId($id_orden) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT o.*, v.placa, v.marca, v.tipo, p.nombre, p.apellido, p.grado
                           FROM ordenes_salida o
                           JOIN vehiculos v ON o.id_vehiculo = v.id_vehiculo
                           JOIN personal p ON o.id_personal = p.id_personal
                           WHERE o.id_orden = ?");
    $stmt->bind_param("i", $id_orden);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function actualizarOrden($id_orden, $datos) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE ordenes_salida 
                           SET destino = ?, motivo = ?, fecha_salida = ?, 
                               fecha_retorno = ?, id_vehiculo = ?, id_personal = ?
                           WHERE id_orden = ?");
    
    $stmt->bind_param("ssssiii", 
        $datos['destino'],
        $datos['motivo'],
        $datos['fecha_salida'],
        $datos['fecha_retorno'],
        $datos['id_vehiculo'],
        $datos['id_personal'],
        $id_orden
    );
    
    return $stmt->execute();
}

function eliminarOrden($id_orden) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("DELETE FROM ordenes_salida WHERE id_orden = ?");
        $stmt->bind_param("i", $id_orden);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error al eliminar orden: " . $e->getMessage());
        return false;
    }
}

function determinarEstadoOrden($orden) {
    $ahora = new DateTime();
    $fechaSalida = new DateTime($orden['fecha_salida']);
    $fechaRetorno = $orden['fecha_retorno'] ? new DateTime($orden['fecha_retorno']) : null;
    
    if ($fechaRetorno) {
        return 'completada';
    } elseif ($ahora >= $fechaSalida) {
        return 'en_curso';
    } else {
        return 'pendiente';
    }
}
?>