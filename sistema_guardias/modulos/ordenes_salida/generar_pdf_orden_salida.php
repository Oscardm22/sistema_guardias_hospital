<?php
require_once __DIR__.'/../../includes/conexion.php';
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/funciones/funciones_autenticacion.php';
require_once __DIR__.'/../../includes/funciones/funciones_personal.php';
require_once __DIR__.'/../../includes/funciones/funciones_vehiculos.php';
require_once __DIR__.'/../../includes/funciones/funciones_ordenes.php';
require_once __DIR__.'/../../vendor/autoload.php';

// Verificar permisos
if (!puede_ver_ordenes_salida()) {
    $_SESSION['error'] = "No tienes permisos para ver esta información";
    header('Location: listar_ordenes.php');
    exit;
}

// Obtener ID de la orden
$id_orden = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_orden <= 0) {
    $_SESSION['error'] = "Orden no especificada";
    header('Location: listar_ordenes.php');
    exit;
}

// Obtener información de la orden y del vehículo
$orden = obtenerOrdenPorId($id_orden, $conn);
if (!$orden) {
    $_SESSION['error'] = "Orden no encontrada";
    header('Location: listar_ordenes.php');
    exit;
}

// Obtener datos completos del vehículo
$vehiculo = obtenerVehiculoPorId($conn, $orden['id_vehiculo']);
if (!$vehiculo) {
    $_SESSION['error'] = "Vehículo no encontrado";
    header('Location: listar_ordenes.php');
    exit;
}

// Combinar datos de orden y vehículo
$orden_completa = array_merge($orden, $vehiculo);

// Generar el PDF
generarPDFOrdenSalida($orden_completa, $conn);

function generarPDFOrdenSalida($orden, $conn) {
    // Limpiar cualquier salida previa
    if (ob_get_contents()) {
        ob_clean();
    }

    try {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        $pdf->SetCreator('Sistema de Gestión de Guardias Hospitalarias');
        $pdf->SetAuthor('Hospital Naval "TN. Pedro Manuel Chirinos"');
        $pdf->SetTitle('Orden de Salida #' . $orden['id_orden']);
        $pdf->SetSubject('Documento Oficial');
        
        $pdf->SetMargins(5, 45, 5);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(true, 30);
        $pdf->AddPage();

        // Encabezado con logos
        $pdf->SetY(15);
        
        $logoLeftPath = realpath(__DIR__.'/../../assets/images/logo_direccion.jpg');
        if ($logoLeftPath && file_exists($logoLeftPath)) {
            $pdf->Image($logoLeftPath, 5, 5, 30, 0, 'JPG', '', 'T', false, 300);
        }
        
        $logoRightPath = realpath(__DIR__.'/../../assets/images/logo_hospital.jpg');
        if ($logoRightPath && file_exists($logoRightPath)) {
            $xRight = $pdf->getPageWidth() - 25;
            $pdf->Image($logoRightPath, $xRight, 5, 20, 0, 'JPG');
        }

        // Texto institucional
        $pdf->SetY(10);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 5, 'MINISTERIO DEL PODER POPULAR PARA LA DEFENSA', 0, 1, 'C');
        $pdf->Cell(0, 5, 'VICEMINISTERIO DE SERVICIOS PARA LA DEFENSA', 0, 1, 'C');
        $pdf->Cell(0, 5, 'DIRECCIÓN GENERAL DE SALUD DEL MPPD', 0, 1, 'C');
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 7, 'HOSPITAL NAVAL "TN. PEDRO MANUEL CHIRINOS"', 0, 1, 'C');
        $pdf->Ln(5);

        // Fecha actual
        $meses = [
            1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL',
            5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO',
            9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
        ];
        $fecha_actual = date('d') . ' ' . $meses[date('n')] . ' ' . date('Y');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Punto Fijo ' . $fecha_actual, 0, 1, 'R');
        $pdf->Ln(5);

        // --- TÍTULO INSTITUCIONAL ANTES DE LA ORDEN ---
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 10, 'ORDEN DE SALIDA', 0, 1, 'C');
        $pdf->Ln(10);

        // --- NUEVO FORMATO DE ORDEN ---
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 7, 'ORDEN #' . $orden['id_orden'] . ' - ' . date('d/m/Y H:i', strtotime($orden['fecha_salida'])), 1, 1, 'L', true);
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(40, 6, 'Vehículo:', 0, 0, 'L');
        $pdf->Cell(0, 6, $orden['marca'] . ' (' . $orden['placa'] . ') - ' . ucfirst($orden['tipo']), 0, 1, 'L');
        
        $pdf->Cell(40, 6, 'Personal:', 0, 0, 'L');
        $pdf->Cell(0, 6, $orden['grado'] . ' ' . $orden['nombre'] . ' ' . $orden['apellido'], 0, 1, 'L');
        
        $pdf->Cell(40, 6, 'Destino:', 0, 0, 'L');
        $pdf->Cell(0, 6, $orden['destino'], 0, 1, 'L');
        
        $pdf->Cell(40, 6, 'Motivo:', 0, 0, 'L');
        $pdf->Cell(0, 6, $orden['motivo'], 0, 1, 'L');
        
        $pdf->Cell(40, 6, 'Retorno:', 0, 0, 'L');
        $pdf->Cell(0, 6, $orden['fecha_retorno'] ? date('d/m/Y H:i', strtotime($orden['fecha_retorno'])) : 'Pendiente', 0, 1, 'L');
        $pdf->Ln(10);

        // Observaciones (se mantiene igual)
        if (!empty($orden['observaciones'])) {
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 8, 'OBSERVACIONES', 0, 1);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->MultiCell(0, 6, $orden['observaciones'], 1, 'L');
            $pdf->Ln(10);
        }

        // Firmas (se mantiene igual)
        if ($pdf->GetY() > ($pdf->getPageHeight() - 50)) {
            $pdf->AddPage();
        }

        $pdf->SetY(-50);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(95, 10, '__________________________', 0, 0, 'C');
        $pdf->Cell(95, 10, '__________________________', 0, 1, 'C');

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(95, 5, 'SUB-DIRECTOR ADMINISTRATIVO', 0, 0, 'C');
        $pdf->Cell(95, 5, 'DIRECTOR', 0, 1, 'C');

        // Limpiar buffer antes de enviar el PDF
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Enviar PDF para descarga
        $pdf->Output('Orden_Salida_' . $orden['id_orden'] . '.pdf', 'D');
        exit;

    } catch (Exception $e) {
        // Limpiar buffer en caso de error
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        error_log("Error al generar PDF: " . $e->getMessage());
        $_SESSION['error'] = "Error al generar el documento. Por favor intente nuevamente.";
        header('Location: listar_ordenes.php');
        exit;
    }
}