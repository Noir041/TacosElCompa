<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/db.php';
require_once 'includes/funciones.php';

$pedido_id = $_SESSION['ultimo_pedido'] ?? 0;
$total = $_SESSION['ultimo_total'] ?? 0;

// Limpiar variables de sesión
unset($_SESSION['ultimo_pedido'], $_SESSION['ultimo_total']);

$titulo = '¡Pedido Confirmado!';
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body py-5">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                    <h1 class="mt-3">¡Pedido Confirmado!</h1>
                    <p class="lead">Tu pedido <strong>#<?php echo $pedido_id; ?></strong> ha sido registrado.</p>
                    <p class="text-muted">Total: <strong class="text-success fs-4">$<?php echo number_format($total, 2); ?></strong></p>
                    <p class="text-muted">Te notificaremos cuando esté listo.</p>
                    
                    <div class="mt-4">
                        <a href="index.php" class="btn btn-danger btn-lg">
                            <i class="bi bi-house"></i> Volver al inicio
                        </a>
                        <a href="perfil.php" class="btn btn-outline-danger btn-lg ms-2">
                            <i class="bi bi-person"></i> Ver mis pedidos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>