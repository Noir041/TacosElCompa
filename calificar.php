<?php
session_start();
require_once 'includes/db.php';

if(!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$pedido_id = intval($_POST['pedido_id'] ?? 0);
$comentario = trim($_POST['comentario'] ?? '');
$platillo_ids = $_POST['platillo_id'] ?? [];
$puntuaciones = [];

// Obtener puntuaciones por cada platillo
foreach($platillo_ids as $pid) {
    $key = 'puntuacion_' . $pid;
    if(isset($_POST[$key])) {
        $puntuaciones[$pid] = intval($_POST[$key]);
    }
}

// Guardar calificaciones
$stmt = $pdo->prepare("INSERT INTO calificaciones (usuario_id, platillo_id, pedido_id, puntuacion, comentario) VALUES (?, ?, ?, ?, ?)");

foreach($puntuaciones as $platillo_id => $puntuacion) {
    // Compartir el mismo comentario para todos los platillos del pedido
    $stmt->execute([$usuario_id, $platillo_id, $pedido_id, $puntuacion, $comentario]);
}

header('Location: perfil.php?calificado=1');
exit;
?>