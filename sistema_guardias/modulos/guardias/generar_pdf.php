<?php
require_once __DIR__.'/../../includes/conexion.php';
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/funciones/funciones_autenticacion.php';
require_once __DIR__.'/../../includes/funciones/funciones_personal.php';
require_once __DIR__.'/../../includes/funciones/funciones_guardias.php';
require_once __DIR__.'/../../includes/funciones/funciones_servicios.php'; // Nueva inclusión
require_once __DIR__.'/../../includes/funciones/funciones_vehiculos.php';  // Nueva inclusión

// Verificar permisos
if (!puede_ver_guardia()) {
    $_SESSION['error'] = "No tienes permisos para ver esta información";
    header('Location: listar_guardias.php');
    exit;
}

// Obtener ID de la guardia
$id_guardia = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_guardia <= 0) {
    $_SESSION['error'] = "Guardia no especificada";
    header('Location: listar_guardias.php');
    exit;
}

// Obtener información básica de la guardia
$sql_guardia = "SELECT 
                g.id_guardia,
                g.fecha as fecha,
                DATE_FORMAT(g.fecha, '%d/%m/%Y') as fecha_formateada,
                24 as horas_guardia
            FROM guardias g
            WHERE g.id_guardia = ?";

$stmt = $conn->prepare($sql_guardia);
$stmt->bind_param("i", $id_guardia);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Guardia no encontrada";
    header('Location: listar_guardias.php');
    exit;
}

$guardia = $result->fetch_assoc();

// Obtener asignaciones de personal
$sql_asignaciones = "SELECT 
                    a.id_asignacion,
                    p.id_personal,
                    p.nombre,
                    p.apellido,
                    p.grado,
                    r.nombre_rol,
                    a.turno
                  FROM asignaciones_guardia a
                  JOIN personal p ON a.id_personal = p.id_personal
                  JOIN roles_guardia r ON a.id_rol = r.id_rol
                  WHERE a.id_guardia = ?";

$stmt = $conn->prepare($sql_asignaciones);
$stmt->bind_param("i", $id_guardia);
$stmt->execute();
$asignaciones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Organizar asignaciones por turno
$asignaciones_por_turno = [
    'diurno' => [],
    'vespertino' => [],
    'nocturno' => [],
    'sin_turno' => []
];

foreach ($asignaciones as $asignacion) {
    $turno = $asignacion['turno'] ? $asignacion['turno'] : 'sin_turno';
    $asignaciones_por_turno[$turno][] = $asignacion;
}

$sql_servicios = "SELECT 
                    s.id_servicio,
                    s.tipo,
                    s.medida,
                    s.unidad,
                    s.observaciones,
                    p.nombre,
                    p.apellido,
                    p.grado
                FROM servicios s
                JOIN personal p ON s.responsable = p.id_personal
                WHERE DATE(s.fecha_registro) = ?";

$stmt = $conn->prepare($sql_servicios);
$stmt->bind_param("s", $guardia['fecha']);
$stmt->execute();
$servicios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener órdenes de salida del mismo día
$sql_ordenes = "SELECT 
                    os.id_orden,
                    os.destino,
                    os.motivo,
                    DATE_FORMAT(os.fecha_salida, '%H:%i') as hora_salida,
                    DATE_FORMAT(os.fecha_retorno, '%H:%i') as hora_retorno,
                    v.placa,
                    v.marca,
                    v.tipo,
                    p.nombre,
                    p.apellido,
                    p.grado
                FROM ordenes_salida os
                JOIN vehiculos v ON os.id_vehiculo = v.id_vehiculo
                JOIN personal p ON os.id_personal = p.id_personal
                WHERE DATE(os.fecha_salida) = ?";

$stmt = $conn->prepare($sql_ordenes);
$stmt->bind_param("s", $guardia['fecha']);
$stmt->execute();
$ordenes_salida = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Modificar la guardia para incluir los nuevos datos
$guardia['servicios'] = $servicios;
$guardia['ordenes_salida'] = $ordenes_salida;

// Llamar a la función con los parámetros originales
generarPDFGuardia($guardia, $asignaciones_por_turno, $conn);