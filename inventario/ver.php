<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$db = new Database();
$conn = $db->getConnection();

if (!isset($_GET['id'])) {
    redirect('index.php');
}

$id = intval($_GET['id']);

// Obtener producto con información de categoría
$stmt = $conn->prepare("SELECT p.*, c.nombre as categoria_nombre 
                       FROM productos p 
                       LEFT JOIN categorias c ON p.categoria_id = c.id 
                       WHERE p.id = ?");
$stmt->execute([$id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    redirect('index.php');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Producto</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1><?= htmlspecialchars($producto['nombre']) ?></h1>
        
        <a href="index.php" class="btn">⬅️ Volver al listado</a>
        
        <div class="product-details">
            <div class="detail-row">
                <span class="detail-label">Descripción:</span>
                <span class="detail-value"><?= $producto['descripcion'] ? nl2br(htmlspecialchars($producto['descripcion'])) : 'N/A' ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Precio:</span>
                <span class="detail-value"><?= formatCurrency($producto['precio']) ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Cantidad en stock:</span>
                <span class="detail-value <?= $producto['cantidad'] < STOCK_MINIMO ? 'stock-bajo' : '' ?>">
                    <?= $producto['cantidad'] ?>
                    <?php if ($producto['cantidad'] < STOCK_MINIMO): ?>
                    <span class="icono-alerta">⚠️ Stock bajo</span>
                    <?php endif; ?>
                </span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Categoría:</span>
                <span class="detail-value"><?= htmlspecialchars($producto['categoria_nombre'] ?? 'N/A') ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Última actualización:</span>
                <span class="detail-value"><?= date('d/m/Y H:i', strtotime($producto['fecha_actualizacion'])) ?></span>
            </div>
        </div>
        
        <div class="action-buttons">
            <a href="editar.php?id=<?= $producto['id'] ?>" class="btn">✏️ Editar</a>
            <a href="eliminar.php?id=<?= $producto['id'] ?>" class="btn danger" 
               onclick="return confirm('¿Estás seguro de eliminar este producto?')">🗑️ Eliminar</a>
        </div>
    </div>
</body>
</html>