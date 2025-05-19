<?php
/**
 * Funciones específicas para el módulo de novedades
 */

/**
 * Verifica si el usuario puede crear novedades
 * @return bool
 */
function puede_crear_novedad() {
    return es_admin() || es_personal();
}

/**
 * Verifica si el usuario puede editar una novedad específica
 * @param int $id_novedad
 * @param mysqli $conn
 * @return bool
 */
function puede_editar_novedad($id_novedad, $conn) {
    if (es_admin()) {
        return true;
    }
    
    if (!es_personal()) {
        return false;
    }
    
    $id_personal = obtener_id_personal_usuario();
    $stmt = $conn->prepare("SELECT 1 FROM novedades WHERE id_novedad = ? AND id_personal_reporta = ?");
    $stmt->bind_param("ii", $id_novedad, $id_personal);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows === 1;
}

/**
 * Verifica si el usuario puede eliminar una novedad
 * @return bool
 */
function puede_eliminar_novedad($id_novedad, $id_usuario, $conn) {
    if (es_admin()) return true;
    
    // Verificar si el usuario es quien reportó la novedad
    $stmt = $conn->prepare("SELECT 1 FROM novedades 
                           WHERE id_novedad = ? AND id_personal_reporta = ?");
    $stmt->bind_param("ii", $id_novedad, $id_usuario);
    $stmt->execute();
    return $stmt->get_result()->num_rows === 1;
}

/**
 * Obtiene los datos completos de una novedad
 * @param int $id_novedad
 * @param mysqli $conn
 * @return array|null
 */
function obtener_novedad($id_novedad, $conn) {
    $sql = "SELECT n.*, 
                   p.nombre AS nombre_personal,
                   p.apellido,
                   p.grado,
                   g.fecha_inicio AS fecha_guardia
            FROM novedades n
            JOIN personal p ON n.id_personal_reporta = p.id_personal
            JOIN guardias g ON n.id_guardia = g.id_guardia
            WHERE n.id_novedad = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_novedad);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc();
}

/**
 * Formatea el área de la novedad para mostrar en la interfaz
 * @param string $area
 * @return string
 */
function formatear_area_novedad($area) {
    $areas = [
        'Personal' => '<span class="badge bg-primary">Personal</span>',
        'Inteligencia' => '<span class="badge bg-success">Inteligencia</span>',
        'Seguridad' => '<span class="badge bg-warning text-dark">Seguridad</span>',
        'Operaciones' => '<span class="badge bg-info">Operaciones</span>',
        'Adiestramiento' => '<span class="badge bg-dark">Adiestramiento</span>',
        'Logistica' => '<span class="badge bg-secondary">Logística</span>',
        'Información general' => '<span class="badge bg-danger">Información general</span>'
    ];
    
    return $areas[$area] ?? $area;
}

/**
 * Registra una nueva novedad con validación y transacción
 * @param array $datos
 * @param mysqli $conn
 * @return array ['success' => bool, 'message' => string, 'id_novedad' => int]
 */
function registrar_novedad_segura($datos, $conn) {
    $errores = validar_datos_novedad($datos);
    if (!empty($errores)) {
        return [
            'success' => false,
            'message' => implode("\n", $errores)
        ];
    }
    
    $conn->begin_transaction();
    
    try {
        $sql = "INSERT INTO novedades 
                (descripcion, area, id_guardia, id_personal_reporta) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssii", 
            $datos['descripcion'],
            $datos['area'], 
            $datos['id_guardia'], 
            $datos['id_personal_reporta']
        );
        
        if ($stmt->execute()) {
            $id_novedad = $conn->insert_id;
            $conn->commit();
            
            return [
                'success' => true,
                'message' => "Novedad registrada correctamente",
                'id_novedad' => $id_novedad
            ];
        } else {
            throw new Exception("Error al ejecutar la consulta");
        }
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error registrando novedad: " . $e->getMessage());
        
        return [
            'success' => false,
            'message' => "Error al registrar la novedad: " . $e->getMessage()
        ];
    }
}

/**
 * Actualiza una novedad existente
 * @param int $id_novedad
 * @param array $datos
 * @param mysqli $conn
 * @return bool
 */
function actualizar_novedad($id_novedad, $datos, $conn) {
    $sql = "UPDATE novedades SET
            descripcion = ?,
            area = ?,
            id_guardia = ?,
            id_personal_reporta = ?
            WHERE id_novedad = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssiii", 
        $datos['descripcion'],
        $datos['area'], 
        $datos['id_guardia'], 
        $datos['id_personal_reporta'],
        $id_novedad
    );
    
    return $stmt->execute();
}

/**
 * Elimina una novedad con verificación de permisos y transacción
 * @param int $id_novedad
 * @param int $id_usuario
 * @param mysqli $conn
 * @return array ['success' => bool, 'message' => string]
 */
function eliminar_novedad_segura($id_novedad, $id_usuario, $conn) {
    if (!puede_eliminar_novedad($id_novedad, $id_usuario, $conn)) {
        return [
            'success' => false,
            'message' => "No tienes permisos para eliminar esta novedad"
        ];
    }
    
    $conn->begin_transaction();
    
    try {
        // Verificar que existe antes de eliminar
        $sql_check = "SELECT 1 FROM novedades WHERE id_novedad = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("i", $id_novedad);
        $stmt_check->execute();
        
        if ($stmt_check->get_result()->num_rows === 0) {
            throw new Exception("La novedad no existe");
        }
        
        // Eliminar
        $sql_delete = "DELETE FROM novedades WHERE id_novedad = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $id_novedad);
        $stmt_delete->execute();
        
        $conn->commit();
        
        return [
            'success' => true,
            'message' => "Novedad eliminada correctamente"
        ];
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error eliminando novedad ID $id_novedad: " . $e->getMessage());
        
        return [
            'success' => false,
            'message' => "Error al eliminar la novedad: " . $e->getMessage()
        ];
    }
}

/**
 * Obtiene el listado de novedades con paginación
 * @param int $pagina
 * @param int $por_pagina
 * @param mysqli $conn
 * @return array
 */
function listar_novedades($pagina, $por_pagina, $conn) {
    $inicio = ($pagina - 1) * $por_pagina;
    
    $sql = "SELECT n.*, 
                   p.nombre AS nombre_personal,
                   p.apellido,
                   p.grado,
                   g.fecha_inicio AS fecha_guardia
            FROM novedades n
            JOIN personal p ON n.id_personal_reporta = p.id_personal
            JOIN guardias g ON n.id_guardia = g.id_guardia
            ORDER BY n.fecha_registro DESC
            LIMIT ?, ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $inicio, $por_pagina);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Obtiene el total de novedades registradas
 * @param mysqli $conn
 * @return int
 */
function contar_novedades($conn) {
    $sql = "SELECT COUNT(*) AS total FROM novedades";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return (int)$row['total'];
}

/**
 * Busca novedades según criterios
 * @param array $filtros
 * @param mysqli $conn
 * @return array
 */
function buscar_novedades($filtros, $conn) {
    // Implementación de búsqueda con filtros
}

/**
 * Obtiene las novedades recientes (últimas 24 horas)
 * @param mysqli $conn
 * @return array
 */
function obtener_novedades_recientes($conn) {
    // Implementación para novedades recientes
}

/**
 * Obtiene todas las guardias para mostrarlas en un select
 * @param mysqli $conn
 * @return array
 */
function obtener_guardias_para_select($conn) {
    $guardias = [];
    $sql = "SELECT id_guardia, fecha_inicio, fecha_fin, tipo_guardia 
            FROM guardias 
            ORDER BY fecha_inicio DESC";
    
    if ($result = $conn->query($sql)) {
        $guardias = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
    }
    
    return $guardias;
}

/**
 * Obtiene todo el personal activo para mostrarlo en un select
 * @param mysqli $conn
 * @return array
 */
function obtener_personal_activo($conn) {
    $personal = [];
    $sql = "SELECT id_personal, nombre, apellido FROM personal WHERE estado = 1 ORDER BY nombre";
    
    if ($result = $conn->query($sql)) {
        $personal = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
    }
    
    return $personal;
}

/**
 * Valida los datos de una novedad antes de insertar/actualizar
 * @param array $datos
 * @return array Array de errores (vacío si no hay errores)
 */
function validar_datos_novedad($datos) {
    $errores = [];
    
    if (empty($datos['descripcion'])) {
        $errores[] = "La descripción es obligatoria";
    } elseif (strlen($datos['descripcion']) > 2000) {
        $errores[] = "La descripción no puede exceder los 2000 caracteres";
    }
    
    $areasPermitidas = ['Personal', 'Inteligencia', 'Seguridad', 'Operaciones', 'Adiestramiento', 'Logistica', 'Información general'];
    if (empty($datos['area'])) {
        $errores[] = "El área es obligatoria";
    } elseif (!in_array($datos['area'], $areasPermitidas)) {
        $errores[] = "Área no válida";
    }
    
    if (empty($datos['id_guardia'])) {
        $errores[] = "Debe seleccionar una guardia";
    }
    
    if (empty($datos['id_personal_reporta'])) {
        $errores[] = "Debe seleccionar personal que reporta";
    }
    
    return $errores;
}

/**
 * Obtiene novedades con filtros avanzados
 * @param array $filtros [pagina, por_pagina, area, fecha_desde, fecha_hasta, id_guardia]
 * @param mysqli $conn
 * @return array
 */
function listar_novedades_filtradas($filtros, $conn) {
    $pagina = max(1, (int)($filtros['pagina'] ?? 1));
    $por_pagina = max(1, min(100, (int)($filtros['por_pagina'] ?? 10)));
    $inicio = ($pagina - 1) * $por_pagina;
    
    // Construir consulta con filtros
    $sql = "SELECT SQL_CALC_FOUND_ROWS n.*, 
                   p.nombre AS nombre_personal,
                   p.apellido,
                   p.grado,
                   g.fecha_inicio AS fecha_guardia
            FROM novedades n
            JOIN personal p ON n.id_personal_reporta = p.id_personal
            JOIN guardias g ON n.id_guardia = g.id_guardia
            WHERE 1=1";
    
    $params = [];
    $types = "";
    
    // Filtro por área
    if (!empty($filtros['area'])) {
        $sql .= " AND n.area = ?";
        $params[] = $filtros['area'];
        $types .= "s";
    }
    
    // Filtro por fecha desde
    if (!empty($filtros['fecha_desde'])) {
        $sql .= " AND n.fecha_registro >= ?";
        $params[] = $filtros['fecha_desde'];
        $types .= "s";
    }
    
    // Filtro por fecha hasta
    if (!empty($filtros['fecha_hasta'])) {
        $sql .= " AND n.fecha_registro <= ?";
        $params[] = $filtros['fecha_hasta'];
        $types .= "s";
    }
    
    // Filtro por guardia
    if (!empty($filtros['id_guardia'])) {
        $sql .= " AND n.id_guardia = ?";
        $params[] = $filtros['id_guardia'];
        $types .= "i";
    }
    
    $sql .= " ORDER BY n.fecha_registro DESC LIMIT ?, ?";
    $params[] = $inicio;
    $params[] = $por_pagina;
    $types .= "ii";
    
    // Preparar y ejecutar consulta
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $novedades = $result->fetch_all(MYSQLI_ASSOC);
    
    // Obtener total de registros (sin límite)
    $total = $conn->query("SELECT FOUND_ROWS() AS total")->fetch_assoc()['total'];
    
    return [
        'novedades' => $novedades,
        'total' => $total,
        'paginas' => ceil($total / $por_pagina)
    ];
}

/**
 * Obtiene estadísticas de novedades por área y período
 * @param string $periodo 'dia', 'semana', 'mes', 'anio'
 * @param mysqli $conn
 * @return array
 */
function obtener_estadisticas_novedades($periodo, $conn) {
    $groupBy = "";
    $dateFormat = "";
    
    switch ($periodo) {
        case 'dia':
            $groupBy = "DATE(fecha_registro)";
            $dateFormat = "%Y-%m-%d";
            break;
        case 'semana':
            $groupBy = "YEARWEEK(fecha_registro)";
            $dateFormat = "%Y-%u";
            break;
        case 'mes':
            $groupBy = "DATE_FORMAT(fecha_registro, '%Y-%m')";
            $dateFormat = "%Y-%m";
            break;
        case 'anio':
            $groupBy = "YEAR(fecha_registro)";
            $dateFormat = "%Y";
            break;
        default:
            $groupBy = "DATE(fecha_registro)";
            $dateFormat = "%Y-%m-%d";
    }
    
    $sql = "SELECT 
                $groupBy AS periodo,
                DATE_FORMAT(fecha_registro, '$dateFormat') AS periodo_formateado,
                area,
                COUNT(*) AS total
            FROM novedades
            GROUP BY $groupBy, area
            ORDER BY periodo, area";
    
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function buscar_novedades_por_texto($texto, $conn) {
    $sql = "SELECT n.*, p.nombre AS nombre_personal 
            FROM novedades n
            JOIN personal p ON n.id_personal_reporta = p.id_personal
            WHERE n.descripcion LIKE ? OR n.area LIKE ?
            ORDER BY n.fecha_registro DESC
            LIMIT 50";
    
    $textoBusqueda = "%$texto%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $textoBusqueda, $textoBusqueda);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function registrar_actividad_novedad($id_usuario, $tipo, $id_novedad, $conn) {
    $sql = "INSERT INTO actividades_novedades 
            (id_usuario, tipo_actividad, id_novedad) 
            VALUES (?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $id_usuario, $tipo, $id_novedad);
    return $stmt->execute();
}