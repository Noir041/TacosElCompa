<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/db.php';

// Verificar sesión
if(!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Verificar carrito
if(empty($_SESSION['carrito'])) {
    header('Location: carrito.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$direccion_entrega = trim($_POST['direccion_entrega'] ?? '');
$notas = trim($_POST['notas'] ?? '');

// Calcular total
$subtotal = 0;
foreach($_SESSION['carrito'] as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}
$envio = $subtotal > 200 ? 0 : 30;
$total = $subtotal + $envio;

// Guardar pedido en la base de datos
$stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id, total, estado, direccion_entrega, notas) VALUES (?, ?, 'pendiente', ?, ?)");
$stmt->execute([$usuario_id, $total, $direccion_entrega, $notas]);
$pedido_id = $pdo->lastInsertId();

// Guardar detalle del pedido
$stmtDetalle = $pdo->prepare("INSERT INTO detalle_pedidos (pedido_id, platillo_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");

foreach($_SESSION['carrito'] as $item) {
    $subtotal_item = $item['precio'] * $item['cantidad'];
    $stmtDetalle->execute([$pedido_id, $item['id'], $item['cantidad'], $item['precio'], $subtotal_item]);
}

// Guardar datos para la página de confirmación
$_SESSION['ultimo_pedido'] = $pedido_id;
$_SESSION['ultimo_total'] = $total;

// Vaciar carrito
$_SESSION['carrito'] = [];

// Redirigir a página de confirmación
header('Location: pedido-confirmado.php');
exit;
?>