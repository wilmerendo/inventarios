<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$db = new Database();
$conn = $db->getConnection();

// Obtener productos con stock bajo
$sql = "SELECT p.*, c.nombre as categoria_nombre 
        FROM productos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        WHERE p.cantidad < ? 
        ORDER BY p.cantidad ASC, p.nombre ASC";

$productos = Database::executeQuery($sql, [STOCK_MINIMO])->fetchAll(PDO::FETCH_ASSOC);

// Obtener categorías para el filtro (opcional)
$categorias = getCategorias($conn);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos con Stock Bajo</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>📉 Productos con Stock Bajo</h1>
        
        <div class="stock-info">
            <p>Mostrando productos con menos de <strong><?= STOCK_MINIMO ?></strong> unidades en stock</p>
            <a href="index.php" class="btn">← Volver al inventario completo</a>
        </div>
        
        <?php if (count($productos) > 0): ?>
            <table class="stock-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Stock Actual</th>
                        <th>Precio</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $producto): ?>
                    <tr class="<?= $producto['cantidad'] == 0 ? 'stock-agotado' : 'stock-bajo' ?>">
                        <td><?= $producto['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($producto['nombre']) ?></strong>
                            <?php if (!empty($producto['descripcion'])): ?>
                            <div class="product-description"><?= htmlspecialchars($producto['descripcion']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($producto['categoria_nombre'] ?? 'N/A') ?></td>
                        <td class="stock-quantity">
                            <span class="quantity-number"><?= $producto['cantidad'] ?></span>
                            <span class="stock-alert">⚠️</span>
                        </td>
                        <td><?= formatCurrency($producto['precio']) ?></td>
                        <td class="actions">
                            <a href="editar.php?id=<?= $producto['id'] ?>" class="btn" title="Editar">✏️</a>
                            <a href="ver.php?id=<?= $producto['id'] ?>" class="btn" title="Ver">👁️</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="export-section">
                <p>Exportar reporte de stock bajo:</p>
                <a href="exportar-excel.php?stock_bajo=1" class="btn success">📊 Excel</a>
                <a href="exportar-pdf.php?stock_bajo=1" class="btn danger">📄 PDF</a>
            </div>
        <?php else: ?>
            <div class="mensaje success">
                🎉 ¡No hay productos con stock bajo! Todos los productos están por encima del mínimo establecido.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>