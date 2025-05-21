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
        // 1. Inicialización del documento PDF
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // 2. Configuración básica del PDF
        $pdf->SetCreator('Sistema de Guardias Hospitalarias');
        $pdf->SetAuthor('Hospital Naval "TN. Pedro Manuel Chirinos"');
        $pdf->SetTitle('Detalle de Guardia #' . $guardia['id_guardia']);
        $pdf->SetSubject('Reporte Oficial de Guardia');
        
        // 3. Configuración de márgenes y página (con margen superior mayor)
        $pdf->SetMargins(5, 45, 5); // Margen superior aumentado a 45mm
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(true, 30);
        $pdf->AddPage();

        // 4. Encabezado institucional con logos
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

       // Línea "A. TRANSCRIPCIONES" con subrayado parcial
        $pdf->SetFont('helvetica', 'B', 10); // Establece negrita para todo
        $pdf->Cell(7, 7, 'A.', 0, 0, 'L'); // "A." sin subrayado

        $pdf->SetFont('helvetica', 'BU', 10); // Cambia a negrita + subrayado
        $pdf->Cell(0, 7, 'TRANSCRIPCIONES', 0, 1, 'L'); // "TRANSCRIPCIONES" subrayado
        $pdf->Ln(5);

        // Configurar estilos iniciales
        $pdf->SetFont('helvetica', '', 10); // Establecer fuente normal primero
        $pdf->SetTextColor(0, 0, 0); // Color negro

        // Texto completo con formato mixto
        $html = '<style>p {text-align: justify;}</style>
        <p><strong>ARTÍCULO 49. DE LA CONSTITUCIÓN DE LA REPÚBLICA BOLIVARIANA DE VENEZUELA QUE TEXTUALMENTE DICE:</strong> 
        TODA PERSONA TIENE EL DERECHO DE ACCEDER A LA INFORMACIÓN Y A LOS DATOS QUE SOBRE SÍ MISMA O SOBRE SUS BIENES CONSTEN EN REGISTROS OFICIALES O PRIVADOS, CON LAS EXCEPCIONES QUE ESTABLEZCA LA LEY, ASÍ COMO DE CONOCER EL USO QUE SE HAGA DE LOS MISMOS Y SU FINALIDAD, Y DE SOLICITAR ANTE EL TRIBUNAL COMPETENTE LA ACTUALIZACIÓN, LA RECTIFICACIÓN O LA DESTRUCCIÓN DE AQUELLOS, SI FUESEN ERRÓNEOS O AFECTASEN ILEGÍTIMAMENTE SUS DERECHOS. IGUALMENTE, PODRÁ ACCEDER A DOCUMENTOS DE CUALQUIER NATURALEZA QUE CONTENGAN INFORMACIÓN CUYO CONOCIMIENTO SEA DE INTERÉS PARA COMUNIDADES O GRUPOS DE PERSONAS.</p>';

        $pdf->writeHTML($html, true, false, true, false, '');

        // Texto completo del pensamiento
        $pdf->Ln(5);
        $pensamiento = '<style>p {text-align: justify;}</style>
        <p><strong>PENSAMIENTO DEL LIBERTADOR:</strong> "LA EDUCACIÓN FORMA AL HOMBRE MORAL, Y PARA FORMAR UN LEGISLADOR SE NECESITA CIERTAMENTE EDUCARLO EN UNA ESCUELA DE MORAL, DE JUSTICIA Y DE LEYES. (CARTA A GUILLERMO WHITE, 26 DE MAYO DE 1820)"</p>';
        $pdf->writeHTML($pensamiento, true, false, true, false, '');
        $pdf->Ln(5);

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 5, 'SIMÓN BOLÍVAR', 0, 1, 'R');

        // 7. Personal asignado por turnos
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'PERSONAL ASIGNADO', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);

        foreach ($asignaciones_por_turno as $turno => $miembros) {
    if (!empty($miembros)) {
        // Encabezado de turno
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 8, 'TURNO: ' . strtoupper($turno), 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);
        
        // Cabecera de la tabla
        $pdf->SetFillColor(230, 230, 230); // Gris claro
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetDrawColor(150, 150, 150);
        
        // Nuevos encabezados y anchos de columna
        $header = ['PERSONAL', 'ROL'];
        $widths = [90, 0];
        
        // Dibujar cabecera con bordes completos
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
        
        // 8. Pie de página oficial con dos firmas
        $pdf->SetY(-50); // Posición más arriba para evitar salto de página

        // Primera firma (Sub-Director Administrativo)
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(95, 10, '__________________________', 0, 0, 'C');
        $pdf->Cell(95, 10, '__________________________', 0, 1, 'C');

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(95, 5, 'SUB-DIRECTOR ADMINISTRATIVO', 0, 0, 'C');
        $pdf->Cell(95, 5, 'DIRECTOR', 0, 1, 'C');

        // 9. Generar PDF
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
