<?php
/**
 * Funciones para manejo de datos del personal
 */

function obtener_nombre_personal($id_personal, $conn) {
    static $cache = [];
    
    if (isset($cache[$id_personal])) {
        return $cache[$id_personal];
    }
    
    $sql = "SELECT CONCAT(grado, ' ', nombre, ' ', apellido) as nombre_completo 
            FROM personal 
            WHERE id_personal = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_personal);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $cache[$id_personal] = $row['nombre_completo'];
        return $row['nombre_completo'];
    }
    
    return 'Desconocido';
}