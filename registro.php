<?php
require_once 'includes/db.php';
$titulo = 'Crear Cuenta';
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmar = $_POST['confirmar'];
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $foto_perfil = 'default-user.png'; // Valor por defecto
    
    // Procesar foto si se subió
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $tamano_max = 2 * 1024 * 1024; // 2MB
        
        if(!in_array($_FILES['foto']['type'], $permitidos)) {
            $error = 'Formato de imagen no válido. Usa JPG, PNG, GIF o WebP';
        } elseif($_FILES['foto']['size'] > $tamano_max) {
            $error = 'La imagen no debe superar 2MB';
        } else {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $foto_perfil = 'user_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], 'subir/' . $foto_perfil);
        }
    }
    
    // Validaciones normales
    if(empty($nombre) || empty($email) || empty($password)) {
        $error = 'Todos los campos con * son obligatorios';
    } elseif($password !== $confirmar) {
        $error = 'Las contraseñas no coinciden';
    } elseif(strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    }
    
    if(empty($error)) {
        // Verificar email duplicado
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if($stmt->fetch()) {
            $error = 'Este correo ya está registrado';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, telefono, direccion, foto_perfil, rol) VALUES (?, ?, ?, ?, ?, ?, 'cliente')");
            $stmt->execute([$nombre, $email, $hash, $telefono, $direccion, $foto_perfil]);
            
            header('Location: login.php?registro=exitoso');
            exit;
        }
    }
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card shadow border-0">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-plus-fill text-danger" style="font-size: 3rem;"></i>
                        <h2 class="mt-2">Crear Cuenta</h2>
                    </div>
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <!-- Foto de perfil -->
                        <div class="text-center mb-4">
                            <img id="preview" src="subir/default-user.png" 
                                 class="rounded-circle border border-3 border-danger" 
                                 width="120" height="120" 
                                 style="object-fit: cover; cursor: pointer;"
                                 onclick="document.getElementById('foto').click()"
                                 title="Haz clic para cambiar la foto">
                            <br>
                            <small class="text-muted">Haz clic en la imagen para cambiarla</small>
                            <input type="file" id="foto" name="foto" accept="image/*" 
                                   style="display: none;" onchange="previewImage(event)">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nombre completo *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="nombre" class="form-control" required 
                                       value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo electrónico *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" class="form-control" required
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" class="form-control" required minlength="6">
                            </div>
                            <div class="form-text">Mínimo 6 caracteres</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmar contraseña *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" name="confirmar" class="form-control" required minlength="6">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="tel" name="telefono" class="form-control"
                                       value="<?php echo htmlspecialchars($_POST['telefono'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dirección de entrega</label>
                            <textarea name="direccion" class="form-control" rows="2"
                                      placeholder="Calle, número, colonia, referencias"><?php echo htmlspecialchars($_POST['direccion'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger w-100 fw-bold py-2">
                            <i class="bi bi-person-check"></i> Registrarme
                        </button>
                    </form>
                    
                    <p class="text-center mt-3">¿Ya tienes cuenta? <a href="login.php" class="text-danger fw-bold">Inicia sesión</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function() {
        document.getElementById('preview').src = reader.result;
    }
    reader.readAsDataURL(event.target.files[0]);
}
</script>

<?php include 'includes/footer.php'; ?>