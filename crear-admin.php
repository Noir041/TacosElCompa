<?php
// ⚠️ EJECUTAR SOLO UNA VEZ Y LUEGO BORRAR ESTE ARCHIVO ⚠️

require_once 'includes/db.php';

// Configura los datos de tu admin
$nombre = 'Administrador';
$email = 'admin@tacoselcompa.com';  // Cambia esto
$password = 'admin123';              // Cambia esto (luego lo cambias desde el panel)
$rol = 'admin';

// Verificar si ya existe
$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->execute([$email]);

if($stmt->fetch()) {
    echo "<h2>⚠️ Ya existe un usuario con ese correo</h2>";
    echo "<p>El admin ya fue creado anteriormente.</p>";
    echo "<p><strong>BORRA ESTE ARCHIVO AHORA MISMO</strong></p>";
    exit;
}

// Crear hash de contraseña
$hash = password_hash($password, PASSWORD_BCRYPT);

// Insertar admin
$stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
$stmt->execute([$nombre, $email, $hash, $rol]);

echo "<div style='font-family: Arial; max-width: 500px; margin: 50px auto; padding: 30px; background: #d4edda; border-radius: 10px;'>";
echo "<h1 style='color: #155724;'>✅ Admin creado con éxito</h1>";
echo "<table style='width: 100%;'>";
echo "<tr><td><strong>Nombre:</strong></td><td>$nombre</td></tr>";
echo "<tr><td><strong>Email:</strong></td><td>$email</td></tr>";
echo "<tr><td><strong>Contraseña:</strong></td><td>$password</td></tr>";
echo "<tr><td><strong>Rol:</strong></td><td>$rol</td></tr>";
echo "</table>";
echo "<hr>";
echo "<p style='color: red; font-weight: bold;'>⚠️ IMPORTANTE: BORRA ESTE ARCHIVO (crear-admin.php) AHORA MISMO</p>";
echo "<p>Puedes iniciar sesión en: <a href='admin/login.php'>Panel de Administración</a></p>";
echo "</div>";
?>