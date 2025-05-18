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

/**
 * Cuenta las guardias programadas para hoy
 */
function contar_guardias_hoy($conn) {
    $query = "SELECT COUNT(*) as total FROM guardias WHERE fecha_inicio = CURDATE()";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row['total'];
}

/**
 * Obtiene las próximas guardias programadas
 */
function obtener_proximas_guardias($conn, $limite = 5) {
    $query = "SELECT g.id_guardia, g.fecha_inicio as fecha, g.tipo_guardia, 
                     COUNT(a.id_asignacion) as total_asignaciones
              FROM guardias g
              LEFT JOIN asignaciones_guardia a ON g.id_guardia = a.id_guardia
              WHERE g.fecha_inicio >= CURDATE()
              GROUP BY g.id_guardia
              ORDER BY g.fecha_inicio ASC
              LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limite);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $guardias = [];
    while ($row = $result->fetch_assoc()) {
        $guardias[] = $row;
    }
    return $guardias;
}