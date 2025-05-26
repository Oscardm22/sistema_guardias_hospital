<?php
require_once __DIR__.'/../../includes/conexion.php';
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/funciones/funciones_autenticacion.php';
require_once __DIR__.'/../../includes/funciones/funciones_personal.php';
require_once __DIR__.'/../../includes/funciones/funciones_guardias.php';

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

// Obtener información básica de la guardia (mismo código que en detalle_guardia.php)
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

// Obtener asignaciones de personal (mismo código que en detalle_guardia.php)
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
    'mañana' => [],
    'tarde' => [],
    'noche' => [],
];

foreach ($asignaciones as $asignacion) {
    $turno = $asignacion['turno'] ?? 'completo';
    $asignaciones_por_turno[$turno][] = $asignacion;
}

// Generar el PDF
generarPDFGuardia($guardia, $asignaciones_por_turno, $conn);