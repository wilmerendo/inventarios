<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$db = new Database();
$conn = $db->getConnection();

// Procesar formulario de categorías
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = sanitizeInput($_POST['nombre']);
    $descripcion = sanitizeInput($_POST['descripcion']);
    
    try {
        if (isset($_POST['editar_id'])) {
            // Editar categoría existente
            $id = intval($_POST['editar_id']);
            $stmt = $conn->prepare("UPDATE categorias SET nombre = ?, descripcion = ? WHERE id = ?");
            $stmt->execute([$nombre, $descripcion, $id]);
            $_SESSION['mensaje'] = "Categoría actualizada correctamente!";
        } else {
            // Agregar nueva categoría
            $stmt = $conn->prepare("INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)");
            $stmt->execute([$nombre, $descripcion]);
            $_SESSION['mensaje'] = "Categoría agregada correctamente!";
        }
        redirect('categorias.php');
    } catch (PDOException $e) {
        $error = "Error al guardar categoría: " . $e->getMessage();
    }
}

// Eliminar categoría
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    try {
        // Primero actualizamos productos que usan esta categoría
        $stmt = $conn->prepare("UPDATE productos SET categoria_id = NULL WHERE categoria_id = ?");
        $stmt->execute([$id]);
        
        // Luego eliminamos la categoría
        $stmt = $conn->prepare("DELETE FROM categorias WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['mensaje'] = "Categoría eliminada correctamente!";
        redirect('categorias.php');
    } catch (PDOException $e) {
        $error = "Error al eliminar categoría: " . $e->getMessage();
    }
}

// Obtener todas las categorías
$categorias = $conn->query("SELECT c.*, COUNT(p.id) as total_productos 
                            FROM categorias c 
                            LEFT JOIN productos p ON c.id = p.categoria_id 
                            GROUP BY c.id 
                            ORDER BY c.nombre")->fetchAll(PDO::FETCH_ASSOC);

// Obtener categoría para editar
$categoria_editar = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $stmt = $conn->prepare("SELECT * FROM categorias WHERE id = ?");
    $stmt->execute([$id]);
    $categoria_editar = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Categorías</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Administrar Categorías</h1>
        
        <a href="index.php" class="btn">⬅️ Volver al inventario</a>
        
        <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="mensaje success"><?= $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
        <div class="mensaje error"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="post">
            <?php if ($categoria_editar): ?>
            <input type="hidden" name="editar_id" value="<?= $categoria_editar['id'] ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="nombre">Nombre de la categoría:</label>
                <input type="text" id="nombre" name="nombre" 
                        value="<?= htmlspecialchars($categoria_editar['nombre'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion"><?= htmlspecialchars($categoria_editar['descripcion'] ?? '') ?></textarea>
            </div>
            
            <button type="submit" class="btn success">
                <?= $categoria_editar ? '💾 Actualizar' : '➕ Agregar' ?> Categoría
            </button>
            
            <?php if ($categoria_editar): ?>
            <a href="categorias.php" class="btn">❌ Cancelar</a>
            <?php endif; ?>
        </form>
        
        <h2>Lista de Categorías</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Productos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categorias as $cat): ?>
                    <tr>
                        <td><?= $cat['id'] ?></td>
                        <td><?= htmlspecialchars($cat['nombre']) ?></td>
                        <td><?= htmlspecialchars($cat['descripcion'] ?? 'N/A') ?></td>
                        <td><?= $cat['total_productos'] ?></td>
                        <td class="actions">
                            <a href="categorias.php?editar=<?= $cat['id'] ?>" class="btn" title="Editar">✏️</a>
                            <a href="categorias.php?eliminar=<?= $cat['id'] ?>" class="btn danger" 
                                title="Eliminar" onclick="return confirm('¿Estás seguro? Los productos con esta categoría quedarán sin categoría asignada.')">🗑️</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>