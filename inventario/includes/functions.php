<?php
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function redirect($location, $delay = 0) {
    if ($delay > 0) {
        header("Refresh: $delay; URL=$location");
    } else {
        header("Location: $location");
    }
    exit();
}

function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

function getCategorias($conn) {
    $stmt = $conn->query("SELECT * FROM categorias ORDER BY nombre");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
