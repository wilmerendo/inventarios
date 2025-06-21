<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$db = new Database();
$conn = $db->getConnection();

// Obtener parámetros de búsqueda
$busqueda = $_GET['busqueda'] ?? '';
$categoria_id = $_GET['categoria'] ?? '';
$stock_bajo = isset($_GET['stock_bajo']);

// Construir consulta SQL
$sql = "SELECT p.*, c.nombre as categoria_nombre 
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

$sql .= " ORDER BY p.fecha_actualizacion DESC";

// Ejecutar consulta
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener categorías para el filtro
$categorias = getCategorias($conn);

// Verificar productos con stock bajo
$stmt = $conn->prepare("SELECT COUNT(*) FROM productos WHERE cantidad < ?");
$stmt->execute([STOCK_MINIMO]);
$stock_bajo_count = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Inventario</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Inventario de Productos</h1>
        
        <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="mensaje success"><?= $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></div>
        <?php endif; ?>
        
        <?php if ($stock_bajo_count > 0 && !$stock_bajo): ?>
        <div class="alerta-stock">
            <span class="icono">⚠️</span>
            Hay <?= $stock_bajo_count ?> producto(s) con stock bajo. 
            <a href="index.php?stock_bajo=1" class="btn warning">Ver productos con stock bajo</a>
        </div>
        <?php endif; ?>
        
        <div class="toolbar">
            <a href="agregar.php" class="btn">➕ Agregar Producto</a>
            <a href="categorias.php" class="btn">🗂️ Administrar Categorías</a>
            
            <div class="export-buttons">
                <a href="exportar-excel.php?<?= http_build_query($_GET) ?>" class="btn success">📊 Exportar a Excel</a>
                <a href="exportar-pdf.php?<?= http_build_query($_GET) ?>" class="btn danger">📄 Exportar a PDF</a>
            </div>
        </div>
        
        <form method="get" action="index.php" class="search-form">
            <div class="form-group">
                <input type="text" name="busqueda" placeholder="Buscar productos..." 
                       value="<?= htmlspecialchars($busqueda) ?>">
                <select name="categoria">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($categoria_id == $cat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn">🔍 Buscar</button>
                <?php if ($busqueda || $categoria_id || $stock_bajo): ?>
                <a href="index.php" class="btn">🔄 Limpiar</a>
                <?php endif; ?>
            </div>
        </form>
        
        <?php if (count($productos) > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Categoría</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $producto): ?>
                    <tr>
                        <td><?= $producto['id'] ?></td>
                        <td><?= htmlspecialchars($producto['nombre']) ?></td>
                        <td><?= htmlspecialchars($producto['descripcion']) ?></td>
                        <td><?= formatCurrency($producto['precio']) ?></td>
                        <td class="<?= $producto['cantidad'] < STOCK_MINIMO ? 'stock-bajo' : '' ?>">
                            <?= $producto['cantidad'] ?>
                            <?php if ($producto['cantidad'] < STOCK_MINIMO): ?>
                            <span class="icono-alerta">⚠️</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($producto['categoria_nombre'] ?? 'N/A') ?></td>
                        <td class="actions">
                            <a href="ver.php?id=<?= $producto['id'] ?>" class="btn" title="Ver">👁️</a>
                            <a href="editar.php?id=<?= $producto['id'] ?>" class="btn" title="Editar">✏️</a>
                            <a href="eliminar.php?id=<?= $producto['id'] ?>" class="btn danger" 
                               title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este producto?')">🗑️</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="mensaje warning">No se encontraron productos con los criterios de búsqueda.</div>
        <?php endif; ?>
    </div>
</body>
</html>