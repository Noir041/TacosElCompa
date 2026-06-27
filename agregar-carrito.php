<?php
session_start();
require_once 'includes/db.php';

// Verificar sesión
if(!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$platillo_id = intval($_POST['platillo_id'] ?? 0);
$cantidad = intval($_POST['cantidad'] ?? 1);

if($platillo_id == 0) {
    header('Location: index.php');
    exit;
}

// Obtener datos del platillo
$stmt = $pdo->prepare("SELECT id, nombre, precio FROM platillos WHERE id = ? AND disponible = TRUE");
$stmt->execute([$platillo_id]);
$platillo = $stmt->fetch();

if(!$platillo) {
    header('Location: index.php');
    exit;
}

// Inicializar carrito si no existe
if(!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Si ya existe en carrito, aumentar cantidad
if(isset($_SESSION['carrito'][$platillo_id])) {
    $_SESSION['carrito'][$platillo_id]['cantidad'] += $cantidad;
} else {
    $_SESSION['carrito'][$platillo_id] = [
        'id' => $platillo['id'],
        'nombre' => $platillo['nombre'],
        'precio' => $platillo['precio'],
        'cantidad' => $cantidad
    ];
}

header('Location: carrito.php?agregado=1');
exit;
?>