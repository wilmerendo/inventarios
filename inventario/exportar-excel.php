<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

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

// Configurar headers para descarga Excel
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="inventario_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Crear contenido Excel
echo "<html>";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
echo "<table border='1'>";
echo "<tr>
        <th colspan='6' style='background-color: #f2f2f2; font-size: 16px;'>Reporte de Inventario</th>
      </tr>
      <tr>
        <th colspan='6'>Generado: " . date('d/m/Y H:i') . "</th>
      </tr>";

// Mostrar filtros aplicados
if (!empty($busqueda) || !empty($categoria_id) || $stock_bajo) {
    echo "<tr><th colspan='6' style='text-align:left;'>Filtros aplicados:</th></tr>";
    
    if (!empty($busqueda)) {
        echo "<tr><td colspan='6'>Búsqueda: " . htmlspecialchars($busqueda) . "</td></tr>";
    }
    
    if (!empty($categoria_id)) {
        $stmt = $conn->prepare("SELECT nombre FROM categorias WHERE id = ?");
        $stmt->execute([$categoria_id]);
        $cat_nombre = $stmt->fetchColumn();
        echo "<tr><td colspan='6'>Categoría: " . htmlspecialchars($cat_nombre) . "</td></tr>";
    }
    
    if ($stock_bajo) {
        echo "<tr><td colspan='6'>Mostrando solo productos con stock bajo (< " . STOCK_MINIMO . " unidades)</td></tr>";
    }
}

// Encabezados de columnas
echo "<tr>
        <th style='background-color: #f2f2f2;'>ID</th>
        <th style='background-color: #f2f2f2;'>Nombre</th>
        <th style='background-color: #f2f2f2;'>Descripción</th>
        <th style='background-color: #f2f2f2;'>Precio</th>
        <th style='background-color: #f2f2f2;'>Cantidad</th>
        <th style='background-color: #f2f2f2;'>Categoría</th>
      </tr>";

// Datos de productos
foreach ($productos as $producto) {
    echo "<tr>
            <td>{$producto['id']}</td>
            <td>" . htmlspecialchars($producto['nombre']) . "</td>
            <td>" . htmlspecialchars($producto['descripcion']) . "</td>
            <td>" . number_format($producto['precio'], 2) . "</td>
            <td style='" . ($producto['cantidad'] < STOCK_MINIMO ? "color: #e74c3c; font-weight: bold;" : "") . "'>
                {$producto['cantidad']}" . ($producto['cantidad'] < STOCK_MINIMO ? " ⚠️" : "") . "
            </td>
            <td>" . htmlspecialchars($producto['categoria'] ?? 'N/A') . "</td>
          </tr>";
}

echo "</table>";
echo "</html>";
exit();
?>