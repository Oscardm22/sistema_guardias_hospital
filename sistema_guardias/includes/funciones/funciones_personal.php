<?php
require_once __DIR__ . '/../conexion.php';

class PersonalFunciones {
    public static function listarPersonal($conn) {
        $query = "SELECT id_personal, nombre, apellido, grado, estado FROM personal";
        $result = $conn->query($query);
        
        $personal = [];
        while ($row = $result->fetch_assoc()) {
            $personal[] = $row;
        }
        return $personal;
    }

    public static function obtenerPersonal($conn, $id) {
        $query = "SELECT id_personal, nombre, apellido, grado, estado FROM personal WHERE id_personal = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public static function crearPersonal($conn, $nombre, $apellido, $grado, $estado = true) {
        $query = "INSERT INTO personal (nombre, apellido, grado, estado) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssi", $nombre, $apellido, $grado, $estado);
        return $stmt->execute();
    }

    public static function actualizarPersonal($conn, $id, $nombre, $apellido, $grado, $estado) {
        $query = "UPDATE personal SET nombre = ?, apellido = ?, grado = ?, estado = ? WHERE id_personal = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssii", $nombre, $apellido, $grado, $estado, $id);
        return $stmt->execute();
    }

    public static function eliminarPersonal($conn, $id) {
        $query = "DELETE FROM personal WHERE id_personal = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public static function existePersonal($conn, $nombre, $apellido, $excluirId = null) {
    $count = 0; // Inicializamos la variable
    
    if ($excluirId) {
        $query = "SELECT COUNT(*) FROM personal WHERE nombre = ? AND apellido = ? AND id_personal != ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $nombre, $apellido, $excluirId);
    } else {
        $query = "SELECT COUNT(*) FROM personal WHERE nombre = ? AND apellido = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $nombre, $apellido);
    }
    
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close(); // Buenas prácticas: cerrar el statement
    
    return $count > 0;
}

public static function contarPersonalActivo($conn) {
    $query = "SELECT COUNT(*) as total FROM personal WHERE estado = 1";
    $result = $conn->query($query);
    
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0; // Retorna 0 si hay error
}

/**
 * Obtiene los últimos miembros del personal registrados
 */
public static function obtenerPersonalReciente($conn, $limite = 5) {
    // Cambiamos 'creado_en' por 'fecha_registro' o eliminamos si no existe
    $query = "SELECT id_personal, nombre, apellido, grado, estado 
              FROM personal ORDER BY id_personal DESC LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limite);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $personal = [];
    while ($row = $result->fetch_assoc()) {
        $personal[] = $row;
    }
    return $personal;
}
}