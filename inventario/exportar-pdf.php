<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'vendor/autoload.php';

use TCPDF as TCPDF;

$db = new Database();
$conn = $db->getConnection();

// Obtener productos con los mismos filtros que index.php
$busqueda = $_GET['busqueda'] ?? '';
$categoria_id = $_GET['categoria'] ?? '';
$stock_bajo = isset($_GET['stock_bajo']);

// Construir consulta SQL
$sql = "SELECT p.id, p.nombre, p.descripcion, p.precio, p.cantidad, c.nombre as categoria 
        FROM productos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        WHERE 1=1";

$params = [];

if (!empty($busqueda)) {
    $sql .= " AND (p.nombre LIKE ? OR p.descripcion LIKE ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}

if (!empty($categoria_id)) {
    $sql .= " AND p.categoria_id = ?";
    $params[] = $categoria_id;
}

if ($stock_bajo) {
    $sql .= " AND p.cantidad < ?";
    $params[] = STOCK_MINIMO;
}

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Crear PDF
$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator('Sistema de Inventario');
$pdf->SetAuthor('Tu Nombre');
$pdf->SetTitle('Reporte de Inventario');
$pdf->SetSubject('Inventario de Productos');

$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 15, 'Reporte de Inventario', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);

// Información del reporte
$pdf->Cell(0, 10, 'Generado: ' . date('d/m/Y H:i'), 0, 1);

// Mostrar filtros aplicados
if (!empty($busqueda) || !empty($categoria_id) || $stock_bajo) {
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 10, 'Filtros aplicados:', 0, 1);
    $pdf->SetFont('helvetica', '', 10);
    
    if (!empty($busqueda)) {
        $pdf->Cell(0, 10, 'Búsqueda: ' . $busqueda, 0, 1);
    }
    
    if (!empty($categoria_id)) {
        $stmt = $conn->prepare("SELECT nombre FROM categorias WHERE id = ?");
        $stmt->execute([$categoria_id]);
        $cat_nombre = $stmt->fetchColumn();
        $pdf->Cell(0, 10, 'Categoría: ' . $cat_nombre, 0, 1);
    }
    
    if ($stock_bajo) {
        $pdf->Cell(0, 10, 'Mostrando solo productos con stock bajo (< ' . STOCK_MINIMO . ' unidades)', 0, 1);
    }
    
    $pdf->Ln(5);
}

// Crear tabla
$html = '<table border="1" cellpadding="4">
    <tr style="background-color:#f2f2f2; font-weight:bold;">
        <th width="10%">ID</th>
        <th width="25%">Nombre</th>
        <th width="30%">Descripción</th>
        <th width="10%">Precio</th>
        <th width="10%">Cantidad</th>
        <th width="15%">Categoría</th>
    </tr>';

foreach ($productos as $producto) {
    $stock_style = ($producto['cantidad'] < STOCK_MINIMO) ? 'color:#e74c3c; font-weight:bold;' : '';
    $html .= '<tr>
        <td>' . $producto['id'] . '</td>
        <td>' . htmlspecialchars($producto['nombre']) . '</td>
        <td>' . htmlspecialchars($producto['descripcion']) . '</td>
        <td>' . number_format($producto['precio'], 2) . '</td>
        <td style="' . $stock_style . '">' . $producto['cantidad'] . 
          ($producto['cantidad'] < STOCK_MINIMO ? ' ⚠️' : '') . '</td>
        <td>' . htmlspecialchars($producto['categoria'] ?? 'N/A') . '</td>
    </tr>';
}

$html .= '</table>';

$pdf->writeHTML($html, true, false, false, false, '');
$pdf->Output('inventario_' . date('Y-m-d') . '.pdf', 'D');
exit();
?>