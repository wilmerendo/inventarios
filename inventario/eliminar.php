<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$db = new Database();
$conn = $db->getConnection();

if (!isset($_GET['id'])) {
    redirect('index.php');
}

$id = intval($_GET['id']);

try {
    // Verificar si el producto existe
    $stmt = $conn->prepare("SELECT nombre FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $producto = $stmt->fetch();
    
    if ($producto) {
        // Eliminar el producto
        $stmt = $conn->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['mensaje'] = "Producto '{$producto['nombre']}' eliminado correctamente!";
    } else {
        $_SESSION['mensaje'] = "El producto no existe o ya fue eliminado.";
    }
} catch (PDOException $e) {
    $_SESSION['mensaje'] = "Error al eliminar producto: " . $e->getMessage();
}

redirect('index.php');
?>