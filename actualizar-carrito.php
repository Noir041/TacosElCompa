<?php
session_start();

$id = intval($_GET['id'] ?? 0);
$accion = $_GET['accion'] ?? '';

if($id > 0 && isset($_SESSION['carrito'][$id])) {
    if($accion == 'sumar') {
        $_SESSION['carrito'][$id]['cantidad']++;
    } elseif($accion == 'restar') {
        $_SESSION['carrito'][$id]['cantidad']--;
        if($_SESSION['carrito'][$id]['cantidad'] <= 0) {
            unset($_SESSION['carrito'][$id]);
        }
    } elseif($accion == 'eliminar') {
        unset($_SESSION['carrito'][$id]);
    }
}

header('Location: carrito.php');
exit;
?>