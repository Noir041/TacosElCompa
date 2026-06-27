<?php
session_start();
require_once 'includes/db.php';

// Verificar sesión
if(!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$platillo_id = intval($_GET['platillo_id'] ?? 0);
$redirect = $_GET['redirect'] ?? 'index';

if($platillo_id == 0) {
    header('Location: index.php');
    exit;
}

// Verificar si ya es favorito
$stmt = $pdo->prepare("SELECT id FROM favoritos WHERE usuario_id = ? AND platillo_id = ?");
$stmt->execute([$usuario_id, $platillo_id]);

if($stmt->fetch()) {
    // Eliminar favorito
    $stmt = $pdo->prepare("DELETE FROM favoritos WHERE usuario_id = ? AND platillo_id = ?");
    $stmt->execute([$usuario_id, $platillo_id]);
} else {
    // Agregar favorito
    $stmt = $pdo->prepare("INSERT INTO favoritos (usuario_id, platillo_id) VALUES (?, ?)");
    $stmt->execute([$usuario_id, $platillo_id]);
}

// Redirigir de vuelta
if($redirect == 'favoritos') {
    header('Location: favoritos.php');
} else {
    header('Location: index.php');
}
exit;
?>