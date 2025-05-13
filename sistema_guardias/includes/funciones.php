<?php
/**
 * Verifica si el usuario actual tiene un permiso específico
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

/**
 * Verifica si el usuario es administrador
 */
function es_admin() {
    return isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'admin';
}

/**
 * Verifica si el usuario es personal médico
 */
function es_personal() {
    return isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'personal';
}

/**
 * Obtiene el ID del personal asociado al usuario logueado
 */
function obtener_id_personal_usuario() {
    return $_SESSION['usuario']['personal']['id'] ?? null;
}

/**
 * Verifica si el usuario puede crear guardias
 */
function puede_crear_guardia() {
    return es_admin(); // Solo admin puede crear
}

/**
 * Verifica si el usuario puede editar guardias
 */
function puede_editar_guardia() {
    return es_admin(); // Solo admin puede editar
}

/**
 * Verifica si el usuario puede eliminar guardias
 */
function puede_eliminar_guardia() {
    return es_admin(); // Solo admin puede eliminar
}

/**
 * Devuelve el nombre completo del usuario con su grado
 */
function nombre_completo_usuario() {
    if (!isset($_SESSION['usuario']['personal'])) {
        return $_SESSION['usuario']['usuario'] ?? 'Usuario';
    }
    
    return $_SESSION['usuario']['personal']['grado'] . ' ' . 
           $_SESSION['usuario']['personal']['nombre'] . ' ' . 
           $_SESSION['usuario']['personal']['apellido'];
}

/**
 * Devuelve el rol del usuario formateado para mostrar
 */
function rol_usuario() {
    if (!isset($_SESSION['usuario']['rol'])) {
        return 'Invitado';
    }
    
    return $_SESSION['usuario']['rol'] === 'admin' ? 'Administrador' : 'Personal Médico';
}

/**
 * Verifica si el usuario puede gestionar (CRUD) personal
 */
function puede_gestionar_personal() {
    return es_admin() && tiene_permiso('gestion_personal');
}

/**
 * Verifica si el usuario puede gestionar usuarios
 */
function puede_gestionar_usuarios() {
    return es_admin() && tiene_permiso('gestion_usuarios');
}

/**
 * Formatea una fecha para mostrarla en la interfaz
 */
function formatear_fecha($fecha, $formato = 'd/m/Y') {
    if (empty($fecha)) return '';
    $date = new DateTime($fecha);
    return $date->format($formato);
}

/**
 * Formatea el tipo de guardia para mostrarlo en la interfaz
 */
function formatear_tipo_guardia($tipo) {
    $tipos = [
        'Diurna' => '<span class="badge bg-success">Diurna</span>',
        'Nocturna' => '<span class="badge bg-dark">Nocturna</span>'
    ];
    return $tipos[$tipo] ?? $tipo;
}

/**
 * Obtiene el nombre completo de un miembro del personal por su ID
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
?>