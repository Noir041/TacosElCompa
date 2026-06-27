<?php 
require_once 'includes/horario.php'; 
$badgeFooter = getBadgeHorario(); 
?>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <!-- Indicador de horario -->
            <p class="mb-2">
                <span class="badge bg-<?php echo $badgeFooter['color']; ?> fs-6 px-3 py-2">
                    <i class="bi bi-<?php echo $badgeFooter['icono']; ?> me-1"></i>
                    <?php echo $badgeFooter['texto']; ?>
                </span>
                <small class="text-white-50 ms-2"><?php echo $badgeFooter['mensaje']; ?></small>
            </p>
            
            <p class="mb-1">&copy; <?php echo date('Y'); ?> <?php echo $nombre_negocio ?? 'Tortas El Compa'; ?></p>
            <small class="text-muted">Hecho con ❤️ y mucho sazón</small>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- JS mínimo -->
    <script src="assets/js/app.js"></script>
</body>
</html>