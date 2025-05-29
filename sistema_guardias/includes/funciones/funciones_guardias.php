<?php
// Ruta ABSOLUTA al autoload.php - versión corregida
$autoloadPath = realpath(__DIR__.'/../../vendor/autoload.php');

if (!$autoloadPath) {
    die("ERROR: autoload.php no encontrado. Ruta probada: ".__DIR__.'/../../vendor/autoload.php');
}

require_once $autoloadPath;

// Carga MANUAL de TCPDF como respaldo
if (!class_exists('TCPDF')) {
    $tcpdfMainFile = realpath(__DIR__.'/../../vendor/tecnickcom/tcpdf/tcpdf.php');
    if (!$tcpdfMainFile) {
        die("ERROR: TCPDF no encontrado en vendor/tecnickcom/tcpdf/");
    }
    require_once $tcpdfMainFile;
}

function generarPDFGuardia($guardia, $asignaciones_por_turno, $conn) {
    try {
        // Inicialización del documento PDF
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Configuración básica del PDF
        $pdf->SetCreator('Sistema de Guardias Hospitalarias');
        $pdf->SetAuthor('Hospital Naval "TN. Pedro Manuel Chirinos"');
        $pdf->SetTitle('Detalle de Guardia #' . $guardia['id_guardia']);
        $pdf->SetSubject('Reporte Oficial de Guardia');
        
        // Configuración de márgenes y página (con margen superior mayor)
        $pdf->SetMargins(5, 45, 5); // Margen superior aumentado a 45mm
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(true, 30);
        $pdf->AddPage();

        // Encabezado institucional con logos
        $pdf->SetY(15); // Posición inicial del encabezado
        
        // Logo izquierdo (Dirección - más grande)
        $logoLeftPath = realpath(__DIR__.'/../../assets/images/logo_direccion.jpg');
        if ($logoLeftPath && file_exists($logoLeftPath)) {
            $pdf->Image(
                $logoLeftPath, 
                5,       // Posición X (15mm desde izquierda)
                5,       // Posición Y (15mm desde arriba)
                30,       // Ancho aumentado a 30mm
                0,        // Alto automático (proporcional)
                'JPG',    // Tipo de imagen
                '',       // Enlace
                'T',      // Alineación superior
                false,    // No redimensionar
                300       // Resolución DPI
            );
        }
        
        // Logo derecho (Hospital - más pequeño)
        $logoRightPath = realpath(__DIR__.'/../../assets/images/logo_hospital.jpg');
        if ($logoRightPath && file_exists($logoRightPath)) {
            $xRight = $pdf->getPageWidth() - 25; // 20mm de ancho + 5mm margen
            $pdf->Image(
                $logoRightPath, 
                $xRight,  // Posición X (derecha)
                5,       // Posición Y (ajustada para alineación vertical)
                20,       // Ancho mantenido en 20mm
                0,        // Alto automático
                'JPG'     // Tipo de imagen
            );
        }

        // Texto institucional centrado (ajustado por logo más grande)
        $pdf->SetY(10); // Ajuste de posición vertical
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 5, 'MINISTERIO DEL PODER POPULAR PARA LA DEFENSA', 0, 1, 'C');
        $pdf->Cell(0, 5, 'VICEMINISTERIO DE SERVICIOS PARA LA DEFENSA', 0, 1, 'C');
        $pdf->Cell(0, 5, 'DIRECCIÓN GENERAL DE SALUD DEL MPPD', 0, 1, 'C');
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 7, 'HOSPITAL NAVAL "TN. PEDRO MANUEL CHIRINOS"', 0, 1, 'C');
        $pdf->Ln(5);

        // Línea alineada a la derecha con la fecha en formato "Punto Fijo 21 ENERO 2025"
        $meses = [
            1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL',
            5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO',
            9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
        ];
        $fecha_actual = date('d') . ' ' . $meses[date('n')] . ' ' . date('Y');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Punto Fijo ' . $fecha_actual, 0, 1, 'R');

        $dias_semana = [
            0 => 'DOMINGO', 1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES',
            4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO'
        ];

        // Obtener fecha de la guardia (usando fecha_formateada)
        $partes_fecha = explode('/', $guardia['fecha_formateada']);
        $dia_guardia = $partes_fecha[0];
        $mes_guardia = $meses[(int)$partes_fecha[1]];
        $ano_guardia = '2Ø' . substr($partes_fecha[2], -2); // Formato 2Ø25
        $fecha_para_dia = DateTime::createFromFormat('d/m/Y', $guardia['fecha_formateada']);
        $dia_semana_guardia = $dias_semana[(int)$fecha_para_dia->format('w')];
        $numero_orden = 'Ø' . $dia_guardia; // Número de orden basado en el día
        
        // Línea alineada a la izquierda (ORDEN DEL DÍA)
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 7, 'ORDEN DEL DÍA N° ' . $numero_orden . ' ' . $dia_semana_guardia . ' ' . $dia_guardia . ' ' . $mes_guardia . ' ' . $ano_guardia, 0, 1, 'L');
        $pdf->Ln(5);

        // Texto genérico
        $pdf->SetFont('helvetica', '', 10);
        $html = '<style>p {text-align: justify;}</style>
        <p>Registro oficial de la guardia hospitalaria correspondiente a la fecha indicada, generado automáticamente por el sistema de gestión de guardias del Hospital Naval.</p>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Ln(5);

        // Personal asignado por turnos
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'PERSONAL ASIGNADO', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);

        // Definir el orden de turnos según el nuevo sistema (12h y 24h)
        $orden_turnos = ['12h', '24h'];

        foreach ($orden_turnos as $turno) {
            $miembros = $asignaciones_por_turno[$turno] ?? [];
            
            if (!empty($miembros)) {
                // Encabezado de turno
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->Cell(0, 8, 'TURNO: ' . ($turno == '12h' ? '12 HORAS' : '24 HORAS'), 0, 1, 'L');
                $pdf->SetFont('helvetica', '', 10);
                
                // Cabecera de la tabla
                $pdf->SetFillColor(230, 230, 230);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetDrawColor(150, 150, 150);
                
                // Encabezados y anchos de columna
                $header = ['PERSONAL', 'ROL'];
                $widths = [90, 0];
                
                // Dibujar cabecera
                for ($i = 0; $i < count($header); $i++) {
                    $pdf->Cell($widths[$i], 7, $header[$i], 1, 0, 'C', true);
                }
                $pdf->Ln();
                
                // Contenido de la tabla
                $fill = false;
                $total_miembros = count($miembros);
                $contador = 0;
                
                foreach ($miembros as $miembro) {
                    $contador++;
                    $nombre_completo = $miembro['grado'] . ' ' . $miembro['nombre'] . ' ' . $miembro['apellido'];
                    
                    // Determinar bordes: LR para todas las filas, B solo para la última
                    $bordes = ($contador == $total_miembros) ? 'LRB' : 'LR';
                    
                    $pdf->Cell($widths[0], 7, $nombre_completo, $bordes, 0, 'L', $fill);
                    $pdf->Cell($widths[1], 7, $miembro['nombre_rol'], $bordes, 1, 'L', $fill);
                    $fill = !$fill;
                }
                
                // Espacio después de la tabla
                $pdf->Ln(8);
            }
        }

        // ========== NUEVA SECCIÓN: SERVICIOS ==========
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'REGISTRO DE SERVICIOS', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);

        // Obtener servicios del mismo día
        $sql_servicios = "SELECT 
                            s.id_servicio,
                            s.tipo,
                            s.medida,
                            s.unidad,
                            s.observaciones,
                            DATE_FORMAT(s.fecha_registro, '%d/%m/%Y %H:%i') as fecha_registro,
                            p.nombre,
                            p.apellido,
                            p.grado
                        FROM servicios s
                        JOIN personal p ON s.responsable = p.id_personal
                        WHERE DATE(s.fecha_registro) = ?
                        ORDER BY s.fecha_registro ASC";

        $stmt = $conn->prepare($sql_servicios);
        $stmt->bind_param("s", $guardia['fecha']);
        $stmt->execute();
        $servicios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (count($servicios) > 0) {
            // Cabecera de la tabla
            $pdf->SetFillColor(230, 230, 230);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetDrawColor(150, 150, 150);
            
            // Encabezados
            $header = ['Tipo', 'Medida', 'Unidad', 'Responsable'];
            $widths = [40, 30, 30, 0];
            
            // Dibujar cabecera
            for ($i = 0; $i < count($header); $i++) {
                $pdf->Cell($widths[$i], 7, $header[$i], 1, 0, 'C', true);
            }
            $pdf->Ln();
            
            // Contenido
            $fill = false;
            foreach ($servicios as $servicio) {
                $pdf->Cell($widths[0], 7, ucfirst($servicio['tipo']), 'LR', 0, 'L', $fill);
                $pdf->Cell($widths[1], 7, $servicio['medida'], 'LR', 0, 'C', $fill);
                $pdf->Cell($widths[2], 7, $servicio['unidad'], 'LR', 0, 'C', $fill);
                $pdf->Cell($widths[3], 7, $servicio['grado'] . ' ' . $servicio['nombre'] . ' ' . $servicio['apellido'], 'LR', 1, 'L', $fill);
                $fill = !$fill;
                
                // Observaciones como fila adicional
                if (!empty($servicio['observaciones'])) {
                    $pdf->SetFont('helvetica', 'I', 8);
                    $pdf->Cell(0, 6, 'Obs: ' . $servicio['observaciones'], 'LRB', 1, 'L', $fill);
                    $pdf->SetFont('helvetica', '', 10);
                    $fill = !$fill;
                }
            }
        } else {
            $pdf->SetFont('helvetica', 'I', 10);
            $pdf->Cell(0, 8, 'No se registraron servicios en esta fecha', 0, 1, 'C');
        }
        $pdf->Ln(10);

        // ========== NUEVA SECCIÓN: ÓRDENES DE SALIDA ==========
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'ÓRDENES DE SALIDA', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);

        // Obtener órdenes de salida del mismo día
        $sql_ordenes = "SELECT 
                        os.id_orden,
                        os.destino,
                        os.motivo,
                        DATE_FORMAT(os.fecha_salida, '%d/%m/%Y %H:%i') as fecha_salida,
                        DATE_FORMAT(os.fecha_retorno, '%d/%m/%Y %H:%i') as fecha_retorno,
                        v.placa,
                        v.marca,
                        v.tipo as tipo_vehiculo,
                        p.nombre as nombre_personal,
                        p.apellido,
                        p.grado
                    FROM ordenes_salida os
                    JOIN vehiculos v ON os.id_vehiculo = v.id_vehiculo
                    JOIN personal p ON os.id_personal = p.id_personal
                    WHERE DATE(os.fecha_salida) = ?
                    ORDER BY os.fecha_salida ASC";

        $stmt = $conn->prepare($sql_ordenes);
        $stmt->bind_param("s", $guardia['fecha']);
        $stmt->execute();
        $ordenes_salida = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (count($ordenes_salida) > 0) {
            foreach ($ordenes_salida as $orden) {
                // Verificar espacio para la siguiente orden
                if ($pdf->GetY() > ($pdf->getPageHeight() - 60)) {
                    $pdf->AddPage();
                }
                
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->Cell(0, 7, 'Orden #' . $orden['id_orden'] . ' - ' . $orden['fecha_salida'], 1, 1, 'L', true);
                
                $pdf->SetFont('helvetica', '', 10);
                $pdf->Cell(40, 6, 'Vehículo:', 0, 0, 'L');
                $pdf->Cell(0, 6, $orden['marca'] . ' (' . $orden['placa'] . ') - ' . ucfirst($orden['tipo_vehiculo']), 0, 1, 'L');
                
                $pdf->Cell(40, 6, 'Personal:', 0, 0, 'L');
                $pdf->Cell(0, 6, $orden['grado'] . ' ' . $orden['nombre_personal'] . ' ' . $orden['apellido'], 0, 1, 'L');
                
                $pdf->Cell(40, 6, 'Destino:', 0, 0, 'L');
                $pdf->Cell(0, 6, $orden['destino'], 0, 1, 'L');
                
                $pdf->Cell(40, 6, 'Motivo:', 0, 0, 'L');
                $pdf->Cell(0, 6, $orden['motivo'], 0, 1, 'L');
                
                $pdf->Cell(40, 6, 'Retorno:', 0, 0, 'L');
                $pdf->Cell(0, 6, $orden['fecha_retorno'] ? $orden['fecha_retorno'] : 'Pendiente', 0, 1, 'L');
                
                $pdf->Ln(5);
            }
        } else {
            $pdf->SetFont('helvetica', 'I', 10);
            $pdf->Cell(0, 8, 'No se registraron órdenes de salida en esta fecha', 0, 1, 'C');
        }
        $pdf->Ln(10);

        // Sección de Novedades
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'NOVEDADES REGISTRADAS', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);

        // Obtener novedades de la guardia
        $sql_novedades = "SELECT 
                            n.id_novedad,
                            n.descripcion,
                            DATE_FORMAT(n.fecha_registro, '%d/%m/%Y %H:%i') as fecha_formateada,
                            n.area,
                            p.nombre,
                            p.apellido,
                            p.grado
                        FROM novedades n
                        JOIN personal p ON n.id_personal_reporta = p.id_personal
                        WHERE n.id_guardia = ?
                        ORDER BY n.fecha_registro ASC";

        $stmt = $conn->prepare($sql_novedades);
        $stmt->bind_param("i", $guardia['id_guardia']);
        $stmt->execute();
        $novedades = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (count($novedades) > 0) {
            foreach ($novedades as $novedad) {
                // Verificar espacio para la siguiente novedad
                if ($pdf->GetY() > ($pdf->getPageHeight() - 60)) {
                    $pdf->AddPage();
                }
                
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->Cell(0, 7, strtoupper($novedad['area']) . ' - ' . $novedad['fecha_formateada'], 1, 1, 'L', true);
                
                $pdf->SetFont('helvetica', 'I', 9);
                $pdf->Cell(0, 6, 'Reportada por: ' . $novedad['grado'] . ' ' . $novedad['nombre'] . ' ' . $novedad['apellido'], 0, 1, 'R');
                
                $pdf->SetFont('helvetica', '', 10);
                $pdf->MultiCell(0, 6, $novedad['descripcion'], 1, 'L');
                $pdf->Ln(5);
            }
        } else {
            $pdf->SetFont('helvetica', 'I', 10);
            $pdf->Cell(0, 8, 'No se registraron novedades en esta guardia', 0, 1, 'C');
            $pdf->Ln(5);
        }

        // Sección de Firmas - Siempre en nueva página si no hay espacio
        if ($pdf->GetY() > ($pdf->getPageHeight() - 50)) {
            $pdf->AddPage();
        }

        $pdf->SetY(-50); // Posición fija desde el fondo para firmas
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(95, 10, '__________________________', 0, 0, 'C');
        $pdf->Cell(95, 10, '__________________________', 0, 1, 'C');

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(95, 5, 'SUB-DIRECTOR ADMINISTRATIVO', 0, 0, 'C');
        $pdf->Cell(95, 5, 'DIRECTOR', 0, 1, 'C');

        // Generar PDF
        $pdf->Output('Orden de operaciones ' . $dia_guardia . ' ' . $mes_guardia . ' ' . $ano_guardia . '.pdf', 'I');

    } catch (Exception $e) {
        error_log("[" . date('Y-m-d H:i:s') . "] Error al generar PDF: " . $e->getMessage());
        throw new Exception('Error al generar el documento oficial. Por favor intente nuevamente.');
    }
}

// Función auxiliar para mostrar texto alternativo
function mostrarTextoAlternativo($pdf) {
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'HOSPITAL REGIONAL', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 6, 'Sistema de Gestión de Guardias', 0, 1, 'C');
    $pdf->Ln(5);
}

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
    $hoy = date('Y-m-d');
    $resultado = $conn->query("SELECT COUNT(*) FROM guardias WHERE fecha = '$hoy'");
    return $resultado->fetch_row()[0];
}

/**
 * Obtiene las próximas guardias programadas
 */
function obtener_proximas_guardias($conn, $limite = 5) {
    $query = "SELECT 
                 g.id_guardia, 
                 g.fecha,
                 COUNT(a.id_asignacion) AS total_asignaciones
              FROM guardias g
              LEFT JOIN asignaciones_guardia a ON g.id_guardia = a.id_guardia
              WHERE g.fecha >= CURDATE()
              GROUP BY g.id_guardia, g.fecha
              ORDER BY g.fecha ASC
              LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limite);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Obtiene los datos de una guardia específica
 * @param mysqli $conn Conexión a la base de datos
 * @param int $id_guardia ID de la guardia a obtener
 * @return array|null Datos de la guardia o null si no existe
 */
function obtener_guardia($conn, $id_guardia) {
    $stmt = $conn->prepare("SELECT * FROM guardias WHERE id_guardia = ?");
    $stmt->bind_param("i", $id_guardia);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return null;
    }
    
    return $result->fetch_assoc();
}

/**
 * Obtiene todos los roles disponibles (versión simplificada)
 */
function obtener_roles_guardia($conn) {
    $sql = "SELECT id_rol, nombre_rol FROM roles_guardia ORDER BY nombre_rol";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Obtiene un rol específico por ID (versión simplificada)
 */
function obtener_rol_por_id($conn, $id_rol) {
    $stmt = $conn->prepare("SELECT id_rol, nombre_rol FROM roles_guardia WHERE id_rol = ?");
    $stmt->bind_param("i", $id_rol);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Verifica si un rol existe (sin cambios)
 */
function existe_rol($conn, $id_rol) {
    $stmt = $conn->prepare("SELECT 1 FROM roles_guardia WHERE id_rol = ?");
    $stmt->bind_param("i", $id_rol);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}