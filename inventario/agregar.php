<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$db = new Database();
$conn = $db->getConnection();

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = sanitizeInput($_POST['nombre']);
    $descripcion = sanitizeInput($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $cantidad = intval($_POST['cantidad']);
    $categoria_id = !empty($_POST['categoria_id']) ? intval($_POST['categoria_id']) : null;

    try {
        $stmt = $conn->prepare("INSERT INTO productos (nombre, descripcion, precio, cantidad, categoria_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $descripcion, $precio, $cantidad, $categoria_id]);
        
        $_SESSION['mensaje'] = "Producto agregado correctamente!";
        redirect('index.php');
    } catch (PDOException $e) {
        $mensaje = "Error al agregar producto: " . $e->getMessage();
    }
}

$categorias = getCategorias($conn);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Producto</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Agregar Producto</h1>
        
        <a href="index.php" class="btn">⬅️ Volver al listado</a>
        
        <?php if ($mensaje): ?>
        <div class="mensaje error"><?= $mensaje ?></div>
        <?php endif; ?>
        
        <form action="agregar.php" method="post">
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            
            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion"></textarea>
            </div>
            
            <div class="form-group">
                <label for="precio">Precio:</label>
                <input type="number" id="precio" name="precio" step="0.01" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="cantidad">Cantidad en stock:</label>
                <input type="number" id="cantidad" name="cantidad" min="0" required>
                <small>Se mostrará una alerta cuando el stock sea menor a <?= STOCK_MINIMO ?> unidades.</small>
            </div>
            
            <div class="form-group">
                <label for="categoria_id">Categoría:</label>
                <select id="categoria_id" name="categoria_id">
                    <option value="">-- Sin categoría --</option>
                    <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn success">💾 Guardar Producto</button>
            <a href="index.php" class="btn">❌ Cancelar</a>
        </form>
    </div>
</body>
</html>