<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/horario.php';

$titulo = 'Mi Carrito';
$carrito = $_SESSION['carrito'] ?? [];
$badge = getBadgeHorario();

// Calcular totales
$subtotal = 0;
foreach($carrito as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}
$envio = $subtotal > 200 ? 0 : 30;
$total = $subtotal + $envio;

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-5">
    <h2 class="mb-4">
        <i class="bi bi-cart3 text-danger"></i> Mi Carrito
    </h2>

    <?php if(isset($_GET['agregado'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> ¡Platillo agregado al carrito!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(empty($carrito)): ?>
        <div class="text-center py-5">
            <i class="bi bi-cart-x text-muted" style="font-size: 5rem;"></i>
            <h3 class="text-muted mt-3">Tu carrito está vacío</h3>
            <p class="text-muted">Agrega platillos del menú para empezar tu pedido</p>
            <a href="index.php" class="btn btn-danger btn-lg mt-3">Ver Menú</a>
        </div>
    <?php else: ?>
        <!-- Aviso de horario -->
        <?php if(!$badge['abierto']): ?>
            <div class="alert alert-warning alert-dismissible fade show text-center fw-bold" role="alert">
                <i class="bi bi-lock-fill"></i> 
                <strong>Estamos cerrados.</strong> <?php echo $badge['mensaje']; ?>
                <br><small>Puedes armar tu pedido y confirmarlo cuando abramos.</small>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Lista de productos -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Platillo</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-center">Precio</th>
                                    <th class="text-center">Subtotal</th>
                                    <th class="text-end pe-4">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($carrito as $id => $item): ?>
                                <tr>
                                    <td class="ps-4 fw-bold"><?php echo htmlspecialchars($item['nombre']); ?></td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center align-items-center gap-2">
                                            <a href="actualizar-carrito.php?accion=restar&id=<?php echo $id; ?>" 
                                               class="btn btn-sm btn-outline-secondary">-</a>
                                            <span class="fw-bold"><?php echo $item['cantidad']; ?></span>
                                            <a href="actualizar-carrito.php?accion=sumar&id=<?php echo $id; ?>" 
                                               class="btn btn-sm btn-outline-secondary">+</a>
                                        </div>
                                    </td>
                                    <td class="text-center">$<?php echo number_format($item['precio'], 2); ?></td>
                                    <td class="text-center fw-bold">$<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></td>
                                    <td class="text-end pe-4">
                                        <a href="actualizar-carrito.php?accion=eliminar&id=<?php echo $id; ?>" 
                                           class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Resumen FIJO -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 position-sticky" style="top: 90px; z-index: 100;">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="bi bi-receipt"></i> Resumen del pedido</h5>
                    </div>
                    <div class="card-body">
                        <!-- Estado del negocio -->
                        <div class="alert alert-<?php echo $badge['abierto'] ? 'success' : 'danger'; ?> py-2 text-center mb-3">
                            <i class="bi bi-<?php echo $badge['icono']; ?> me-1"></i>
                            <strong><?php echo $badge['texto']; ?></strong>
                            <br><small><?php echo $badge['mensaje']; ?></small>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span>$<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Envío</span>
                            <span><?php echo $envio == 0 ? '<span class="text-success fw-bold">GRATIS</span>' : '$' . number_format($envio, 2); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total</strong>
                            <strong class="fs-5 text-danger">$<?php echo number_format($total, 2); ?></strong>
                        </div>
                        
                        <?php if($envio > 0): ?>
                            <small class="text-muted">Agrega $<?php echo number_format(200 - $subtotal, 2); ?> más para envío gratis</small>
                        <?php endif; ?>

                        <form method="POST" action="realizar-pedido.php" class="mt-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Dirección de entrega</label>
                                <textarea name="direccion_entrega" class="form-control" rows="2" required 
                                          placeholder="Calle, número, colonia, referencias"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notas adicionales (opcional)</label>
                                <textarea name="notas" class="form-control" rows="2" 
                                          placeholder="Ej: Sin cebolla, salsa aparte..."></textarea>
                            </div>
                            
                            <?php if($badge['abierto']): ?>
                                <button type="submit" class="btn btn-danger w-100 fw-bold py-2">
                                    <i class="bi bi-check-lg"></i> Realizar pedido
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-secondary w-100 fw-bold py-2" disabled>
                                    <i class="bi bi-lock-fill"></i> Pedidos cerrados
                                </button>
                                <small class="text-muted text-center d-block mt-2">
                                    <?php echo $badge['mensaje']; ?>
                                </small>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>