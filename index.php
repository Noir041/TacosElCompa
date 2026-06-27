<?php
require_once 'includes/db.php';
require_once 'includes/funciones.php';
require_once 'includes/horario.php';

$titulo = 'Nuestro Menú';
$nombre_negocio = 'Tortas El Compa';
$badge = getBadgeHorario();

include 'includes/header.php';
include 'includes/navbar.php';

// Promoción activa
$stmt = $pdo->query("SELECT * FROM promociones WHERE activa = TRUE AND fecha_fin >= CURDATE() LIMIT 1");
$promo = $stmt->fetch();

// Categorías y platillos
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY orden ASC")->fetchAll();

// Total de platillos disponibles
$totalPlatillos = $pdo->query("SELECT COUNT(*) FROM platillos WHERE disponible = TRUE")->fetchColumn();
?>

<!-- Promoción -->
<?php if($promo): ?>
<div class="alert alert-warning alert-dismissible fade show text-center mb-0 rounded-0 fw-bold" role="alert">
    🎉 <?php echo htmlspecialchars($promo['titulo']); ?> - <?php echo htmlspecialchars($promo['descripcion']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Hero -->
<div class="bg-danger bg-gradient text-white text-center py-5">
    <div class="container">
        <h1 class="display-4 fw-bold">🥪 <?php echo $nombre_negocio; ?></h1>
        <p class="lead">Comida casera con el sazón de siempre</p>
        <div class="d-flex justify-content-center gap-4 flex-wrap">
            <span>
                <i class="bi bi-clock"></i> 
                <span class="badge bg-<?php echo $badge['color']; ?> fs-6">
                    <i class="bi bi-<?php echo $badge['icono']; ?> me-1"></i>
                    <?php echo $badge['texto']; ?>
                </span>
                <small class="ms-1 text-white-50"><?php echo $badge['mensaje']; ?></small>
            </span>
            <span><i class="bi bi-geo-alt"></i> Zona Centro</span>
            <span><i class="bi bi-truck"></i> Entrega $30</span>
        </div>
        <a href="#menu" class="btn btn-light btn-lg fw-bold mt-3 px-5">Ver Menú <i class="bi bi-arrow-down"></i></a>
    </div>
</div>

<!-- Horario semanal -->
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clock text-danger"></i> Horario de Atención</h5>
                    <span class="badge bg-<?php echo $badge['color']; ?> fs-6">
                        <i class="bi bi-<?php echo $badge['icono']; ?> me-1"></i>
                        <?php echo $badge['texto']; ?>
                    </span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <tbody>
                            <?php 
                            $diasOrden = ['lunes','martes','miércoles','jueves','viernes','sábado','domingo'];
                            foreach($diasOrden as $dia): 
                                $esHoy = ($dia == $diaActual);
                                $h = $horarios[$dia];
                            ?>
                            <tr class="<?php echo $esHoy ? 'table-active fw-bold' : ''; ?>">
                                <td class="ps-4 text-capitalize">
                                    <?php echo $dia; ?>
                                    <?php if($esHoy): ?>
                                        <span class="badge bg-<?php echo $badge['color']; ?> ms-2">HOY</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php echo $h['abre']; ?> - <?php echo $h['cierra']; ?> hrs
                                </td>
                                <td class="text-end pe-4">
                                    <?php if($esHoy): ?>
                                        <i class="bi bi-circle-fill text-<?php echo $abierto ? 'success' : 'danger'; ?>"></i>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Menú -->
<div class="container my-5" id="menu">

    <!-- 🔍 BUSCADOR DE PLATILLOS -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-6 col-lg-5">
            <div class="input-group shadow-sm">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-danger"></i>
                </span>
                <input type="text" 
                       id="buscadorPlatillos" 
                       class="form-control border-start-0 ps-0" 
                       placeholder="Buscar platillo... (ej: taco, pastor, queso)"
                       autocomplete="off">
                <button class="btn btn-outline-danger" type="button" onclick="limpiarBusqueda()" title="Limpiar búsqueda">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <small class="text-muted ms-2">
                <span id="contadorPlatillos"><?php echo $totalPlatillos; ?></span> platillos disponibles
            </small>
        </div>
    </div>

    <!-- 🏷️ FILTROS POR CATEGORÍA -->
    <div class="row justify-content-center mb-5">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <button class="btn btn-danger btn-sm rounded-pill filtro-categoria active" data-categoria="todas">
                    <i class="bi bi-grid"></i> Todas
                </button>
                <?php foreach($categorias as $cat): ?>
                    <?php 
                    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM platillos WHERE categoria_id = ? AND disponible = TRUE");
                    $stmtCount->execute([$cat['id']]);
                    $countCat = $stmtCount->fetchColumn();
                    ?>
                    <?php if($countCat > 0): ?>
                    <button class="btn btn-outline-danger btn-sm rounded-pill filtro-categoria" data-categoria="<?php echo strtolower(htmlspecialchars($cat['nombre'])); ?>">
                        <?php echo htmlspecialchars($cat['nombre']); ?>
                        <span class="badge bg-danger ms-1"><?php echo $countCat; ?></span>
                    </button>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php foreach($categorias as $cat): ?>
        <?php 
        $stmt = $pdo->prepare("SELECT * FROM platillos WHERE categoria_id = ? AND disponible = TRUE ORDER BY nombre ASC");
        $stmt->execute([$cat['id']]);
        $platillos = $stmt->fetchAll();
        if(count($platillos) == 0) continue;
        ?>
        
        <div class="mb-5 seccion-categoria">
            <h2 class="border-bottom border-danger border-3 pb-2 mb-4 d-flex align-items-center">
                <i class="bi bi-caret-right-fill text-danger me-2"></i>
                <?php echo htmlspecialchars($cat['nombre']); ?>
            </h2>
            
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                <?php foreach($platillos as $p): ?>
                <div class="col col-platillo">
                    <div class="card h-100 border-0 shadow-sm card-platillo" 
                         data-nombre="<?php echo strtolower(htmlspecialchars($p['nombre'] . ' ' . $p['descripcion'] . ' ' . $cat['nombre'])); ?>">
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
                                    
                                    <!-- FAVORITOS -->
                                    <?php if(isset($_SESSION['usuario_id'])): ?>
                                        <?php 
                                        $stmtFav = $pdo->prepare("SELECT id FROM favoritos WHERE usuario_id = ? AND platillo_id = ?");
                                        $stmtFav->execute([$_SESSION['usuario_id'], $p['id']]);
                                        $esFavorito = $stmtFav->fetch() ? true : false;
                                        ?>
                                        <a href="toggle-favorito.php?platillo_id=<?php echo $p['id']; ?>&redirect=index" 
                                           class="btn btn-sm <?php echo $esFavorito ? 'btn-danger' : 'btn-outline-danger'; ?>"
                                           title="<?php echo $esFavorito ? 'Quitar de favoritos' : 'Agregar a favoritos'; ?>">
                                            <i class="bi <?php echo $esFavorito ? 'bi-heart-fill' : 'bi-heart'; ?>"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
    
    <?php if(count($categorias) == 0): ?>
        <div class="text-center py-5">
            <i class="bi bi-emoji-frown text-muted" style="font-size: 4rem;"></i>
            <h3 class="text-muted mt-3">Menú no disponible</h3>
            <p class="text-muted">Estamos actualizando nuestro menú. Vuelve pronto.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Script del buscador y filtros -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const buscador = document.getElementById('buscadorPlatillos');
    const cards = document.querySelectorAll('.card-platillo');
    const contador = document.getElementById('contadorPlatillos');
    const secciones = document.querySelectorAll('.seccion-categoria');
    const botonesFiltro = document.querySelectorAll('.filtro-categoria');
    const totalOriginal = cards.length;
    let categoriaActiva = 'todas';

    function aplicarFiltros() {
        const texto = buscador.value.toLowerCase().trim();
        let visibles = 0;

        cards.forEach(card => {
            const nombre = card.dataset.nombre;
            const colPlatillo = card.closest('.col-platillo');
            const seccionCategoria = colPlatillo.closest('.seccion-categoria');
            const nombreCategoria = seccionCategoria ? seccionCategoria.querySelector('h2').textContent.trim().toLowerCase() : '';
            
            const coincideTexto = texto === '' || nombre.includes(texto);
            const coincideCategoria = categoriaActiva === 'todas' || nombreCategoria.includes(categoriaActiva);
            
            if(coincideTexto && coincideCategoria) {
                colPlatillo.style.display = '';
                visibles++;
            } else {
                colPlatillo.style.display = 'none';
            }
        });

        contador.textContent = visibles;

        secciones.forEach(seccion => {
            const cols = seccion.querySelectorAll('.col-platillo');
            let hayVisibles = false;
            cols.forEach(col => {
                if(col.style.display !== 'none') hayVisibles = true;
            });
            seccion.style.display = hayVisibles ? '' : 'none';
        });

        let sinResultados = document.getElementById('sinResultados');
        if(visibles === 0 && (texto !== '' || categoriaActiva !== 'todas')) {
            if(!sinResultados) {
                sinResultados = document.createElement('div');
                sinResultados.id = 'sinResultados';
                sinResultados.className = 'text-center py-5';
                sinResultados.innerHTML = `
                    <i class="bi bi-search text-muted" style="font-size: 4rem;"></i>
                    <h3 class="text-muted mt-3">Sin resultados</h3>
                    <p class="text-muted" id="mensajeSinResultados"></p>
                    <button class="btn btn-outline-danger" onclick="limpiarBusqueda()">
                        <i class="bi bi-x-lg"></i> Limpiar filtros
                    </button>
                `;
                document.getElementById('menu').appendChild(sinResultados);
            }
            sinResultados.style.display = '';
            document.getElementById('mensajeSinResultados').innerHTML = 
                'No encontramos platillos' + 
                (texto !== '' ? ' con "<strong>' + texto + '</strong>"' : '') +
                (categoriaActiva !== 'todas' ? ' en <strong>' + categoriaActiva + '</strong>' : '');
        } else {
            if(sinResultados) sinResultados.style.display = 'none';
        }
    }

    buscador.addEventListener('input', aplicarFiltros);

    botonesFiltro.forEach(btn => {
        btn.addEventListener('click', function() {
            botonesFiltro.forEach(b => {
                b.classList.remove('active', 'btn-danger');
                b.classList.add('btn-outline-danger');
            });
            this.classList.add('active', 'btn-danger');
            this.classList.remove('btn-outline-danger');
            
            categoriaActiva = this.dataset.categoria;
            aplicarFiltros();
        });
    });

    buscador.addEventListener('keypress', function(e) {
        if(e.key === 'Enter') {
            const primeraVisible = document.querySelector('.col-platillo:not([style*="display: none"])');
            if(primeraVisible) primeraVisible.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
});

function limpiarBusqueda() {
    const buscador = document.getElementById('buscadorPlatillos');
    buscador.value = '';
    
    document.querySelectorAll('.filtro-categoria').forEach(b => {
        b.classList.remove('active', 'btn-danger');
        b.classList.add('btn-outline-danger');
    });
    const btnTodas = document.querySelector('.filtro-categoria[data-categoria="todas"]');
    if(btnTodas) {
        btnTodas.classList.add('active', 'btn-danger');
        btnTodas.classList.remove('btn-outline-danger');
    }
    
    buscador.dispatchEvent(new Event('input'));
    buscador.focus();
    window.scrollTo({ top: document.getElementById('menu').offsetTop - 80, behavior: 'smooth' });
}
</script>

<!-- Estilos -->
<style>
#buscadorPlatillos {
    transition: all 0.3s ease;
}
#buscadorPlatillos:focus {
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    border-color: #dc3545;
}
.card-platillo {
    transition: opacity 0.3s ease;
}
.filtro-categoria {
    transition: all 0.2s ease;
}
</style>

<?php include 'includes/footer.php'; ?>