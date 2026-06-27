<?php
session_start();
if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
// Redirigir al panel
header('Location: panel.php');
exit;
?>