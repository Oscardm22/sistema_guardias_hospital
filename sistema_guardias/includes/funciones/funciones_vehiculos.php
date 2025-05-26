<?php
/**
 * Obtiene la lista completa de vehículos
 * @param mysqli $conn Conexión a la base de datos
 * @return array Lista de vehículos
 */
function obtener_vehiculos($conn) {
    $sql = "SELECT * FROM vehiculos ORDER BY placa";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Obtiene vehículos activos (disponibles o en mantenimiento)
 * @param mysqli $conn Conexión a la base de datos
 * @return array Lista de vehículos activos
 */
function obtener_vehiculos_activos($conn) {
    $sql = "SELECT * FROM vehiculos 
            WHERE estado IN ('Disponible', 'En mantenimiento') 
            ORDER BY placa";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Obtiene un vehículo específico por su ID
 */
function obtenerVehiculoPorId($conn, $id_vehiculo) {
    $stmt = $conn->prepare("SELECT * FROM vehiculos WHERE id_vehiculo = ?");
    $stmt->bind_param("i", $id_vehiculo);
    $stmt->execute();
    $resultado = $stmt->get_result();
    return $resultado->fetch_assoc();
}

/**
 * Guarda un nuevo vehículo en la base de datos
 * @param array $datos Datos del vehículo
 * @param mysqli $conn Conexión a la base de datos
 * @return bool True si se guardó correctamente
 */
function guardar_vehiculo($datos, $conn) {
    $sql = "INSERT INTO vehiculos (placa, modelo, tipo_vehiculo, estado, kilometraje, observaciones) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssds", 
        $datos['placa'],
        $datos['modelo'],
        $datos['tipo_vehiculo'],
        $datos['estado'],
        $datos['kilometraje'],
        $datos['observaciones']
    );
    return $stmt->execute();
}

function eliminarVehiculo($conexion, $id) {
    // Inicializar variable count
    $count = 0;
    
    // Verificar órdenes de salida primero
    $query = "SELECT COUNT(*) FROM ordenes_salida WHERE id_vehiculo = ?";
    $stmt = $conexion->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . $conexion->error);
    }
    
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }
    
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    
    if ($count > 0) {
        return false;
    }
    
    // Eliminar el vehículo
    $query = "DELETE FROM vehiculos WHERE id_vehiculo = ?";
    $stmt = $conexion->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta de eliminación: " . $conexion->error);
    }
    
    $stmt->bind_param("i", $id);
    $resultado = $stmt->execute();
    $stmt->close();
    
    return $resultado;
}

function actualizarVehiculo($conn, $id_vehiculo, $placa, $marca, $tipo, $combustible, $operativo) {
    try {
        // Verificar duplicados de placa
        $stmt = $conn->prepare("SELECT id_vehiculo FROM vehiculos WHERE placa = ? AND id_vehiculo != ?");
        $stmt->bind_param("si", $placa, $id_vehiculo);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            return false; // Placa duplicada
        }

        // Actualizar
        $stmt = $conn->prepare("UPDATE vehiculos SET placa = ?, marca = ?, tipo = ?, combustible = ?, operativo = ? WHERE id_vehiculo = ?");
        $stmt->bind_param("ssssii", $placa, $marca, $tipo, $combustible, $operativo, $id_vehiculo);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error al actualizar vehículo: " . $e->getMessage());
        return false;
    }
}