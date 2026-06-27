<?php
session_start();

require_once 'includes/db.php';
require_once 'includes/funciones.php';
requiereCliente();

$titulo = 'Mis Favoritos';
$usuario_id = $_SESSION['usuario_id'];

$stmt = $pdo->prepare("
    SELECT p.*, c.nombre as categoria 
    FROM favoritos f 
    JOIN platillos p ON f.platillo_id = p.id 
    LEFT JOIN categorias c ON p.categoria_id = c.id 
    WHERE f.usuario_id = ? 
    ORDER BY f.fecha_agregado DESC
");
$stmt->execute([$usuario_id]);
$favoritos = $stmt->fetchAll();

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-5">
    <h2 class="mb-4 d-flex align-items-center">
        <i class="bi bi-heart-fill text-danger me-2"></i> Mis Favoritos
    </h2>
    
    <?php if(count($favoritos) == 0): ?>
        <div class="text-center py-5">
            <i class="bi bi-heartbreak text-muted" style="font-size: 5rem;"></i>
            <h3 class="text-muted mt-3">No tienes favoritos aún</h3>
            <p class="text-muted">Explora el menú y guarda tus platillos favoritos</p>
            <a href="index.php" class="btn btn-danger btn-lg mt-3">Ver Menú</a>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach($favoritos as $p): ?>
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm">
                        <span class="badge bg-secondary position-absolute top-0 start-0 m-2">
                            <?php echo htmlspecialchars($p['categoria']); ?>
                        </span>
                        <img src="subir/<?php echo htmlspecialchars($p['imagen']); ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($p['nombre']); ?>"
                             style="height: 200px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($p['nombre']); ?></h5>
                            <p class="card-text text-muted flex-grow-1"><?php echo htmlspecialchars($p['descripcion']); ?></p>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="fs-4 fw-bold text-danger">$<?php echo number_format($p['precio'], 2); ?></span>
                                <div class="d-flex gap-1">
                                    <!-- Agregar al carrito -->
                                    <form method="POST" action="agregar-carrito.php" class="d-inline">
                                        <input type="hidden" name="platillo_id" value="<?php echo $p['id']; ?>">
                                        <input type="hidden" name="cantidad" value="1">
                                        <button type="submit" class="btn btn-success btn-sm" title="Agregar al carrito">
                                            <i class="bi bi-cart-plus"></i>
                                        </button>
                                    </form>
                                    <!-- Quitar de favoritos -->
                                    <a href="toggle-favorito.php?platillo_id=<?php echo $p['id']; ?>&redirect=favoritos" 
                                       class="btn btn-danger btn-sm"
                                       title="Quitar de favoritos">
                                        <i class="bi bi-heart-fill"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>