<nav class="navbar navbar-expand-lg navbar-dark bg-danger sticky-top shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-shop"></i> <?php echo $nombre_negocio ?? 'Tortas El Compa'; ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php"><i class="bi bi-house"></i> Inicio</a>
                </li>
                
                <?php if(isset($_SESSION['usuario_id'])): ?>
                    <!-- CLIENTE CON FOTO -->
                    
                    <!-- 🔔 NOTIFICACIONES -->
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="notificaciones.php">
                            <i class="bi bi-bell"></i>
                            <?php 
                            require_once 'includes/funciones.php';
                            $noLeidas = contarNotificaciones($pdo, $_SESSION['usuario_id']);
                            if($noLeidas > 0): 
                            ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">
                                <?php echo $noLeidas; ?>
                                <span class="visually-hidden">notificaciones sin leer</span>
                            </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    
                    <!-- CARRITO -->
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="carrito.php">
                            <i class="bi bi-cart3"></i> Carrito
                            <?php 
                            $cantidad_carrito = 0;
                            if(isset($_SESSION['carrito'])) {
                                foreach($_SESSION['carrito'] as $item) {
                                    $cantidad_carrito += $item['cantidad'];
                                }
                            }
                            if($cantidad_carrito > 0):
                            ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">
                                <?php echo $cantidad_carrito; ?>
                            </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    
                    <!-- FAVORITOS -->
                    <li class="nav-item">
                        <a class="nav-link" href="favoritos.php"><i class="bi bi-heart"></i> Favoritos</a>
                    </li>
                    
                    <!-- PERFIL DROPDOWN -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" 
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="subir/<?php echo $_SESSION['usuario_foto'] ?? 'default-user.png'; ?>" 
                                 class="rounded-circle me-2 border border-2 border-white" 
                                 width="32" height="32" 
                                 style="object-fit: cover;"
                                 alt="Foto de perfil">
                            <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="perfil.php">
                                    <i class="bi bi-person"></i> Mi Perfil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="favoritos.php">
                                    <i class="bi bi-heart"></i> Mis Favoritos
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="notificaciones.php">
                                    <i class="bi bi-bell"></i> Notificaciones
                                    <?php if($noLeidas > 0): ?>
                                        <span class="badge bg-danger ms-1"><?php echo $noLeidas; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="cerrar-sesion.php">
                                    <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                <?php elseif(isset($_SESSION['admin_id'])): ?>
                    <!-- ADMIN -->
                    <li class="nav-item">
                        <a class="nav-link" href="admin/index.php"><i class="bi bi-speedometer2"></i> Panel Admin</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-warning" href="admin/cerrar-sesion.php"><i class="bi bi-box-arrow-right"></i> Salir</a>
                    </li>
                    
                <?php else: ?>
                    <!-- NO HA INICIADO SESIÓN -->
                    <li class="nav-item">
                        <a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="registro.php"><i class="bi bi-person-plus"></i> Registrarme</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>