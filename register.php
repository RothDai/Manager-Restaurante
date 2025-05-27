<?php
// /register.php
session_start();
require 'config/db.php';

$errors = [];
$nombre = '';
$email = '';
$rol = '';

$roles = ['gerente', 'mesero', 'cocina'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $rol = $_POST['rol'] ?? '';
    $pass = $_POST['pass'] ?? '';

    if (!$nombre) $errors[] = 'Nombre es requerido';
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email válido es requerido';
    }
    if (!$rol) $errors[] = 'Rol es requerido';
    if (strlen($pass) < 6) $errors[] = 'La contraseña debe tener al menos 6 caracteres';

    if (empty($errors)) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
          INSERT INTO usuarios (nombre, email, contrasena_hash, rol, fecha_registro)
          VALUES (?, ?, ?, ?, NOW())
        ");
        try {
            $stmt->execute([$nombre, $email, $hash, $rol]);
            header('Location: login.php?registered=1');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Error al registrar usuario. Quizá el email ya existe.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Registrar - RestAI</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">
  <div class="w-full max-w-md">
    <div class="bg-white rounded-lg shadow-lg p-6 animate-fade-in-up">
      <h1 class="text-2xl font-bold text-center text-blue-600 mb-4">Crear Cuenta</h1>

      <?php if (!empty($errors)): ?>
        <ul class="text-red-500 mb-4 list-disc list-inside text-sm">
          <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <form method="POST" class="space-y-4">
        <div>
          <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
          <input
            type="text"
            id="nombre"
            name="nombre"
            value="<?= htmlspecialchars($nombre) ?>"
            required
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          />
        </div>
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <input
            type="email"
            id="email"
            name="email"
            value="<?= htmlspecialchars($email) ?>"
            required
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          />
        </div>
        <div>
          <label for="rol" class="block text-sm font-medium text-gray-700">Rol</label>
          <select
            id="rol"
            name="rol"
            required
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Selecciona...</option>
            <?php foreach ($roles as $r): ?>
              <option value="<?= $r ?>" <?= ($rol === $r) ? 'selected' : '' ?>>
                <?= ucfirst($r) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label for="pass" class="block text-sm font-medium text-gray-700">Contraseña</label>
          <input
            type="password"
            id="pass"
            name="pass"
            minlength="6"
            required
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          />
        </div>
        <button
          type="submit"
          class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        >
          Registrar
        </button>
      </form>
      <p class="mt-4 text-center text-sm">
        <a href="login.php" class="text-blue-600 hover:text-blue-800">¿Ya tienes cuenta? Inicia sesión</a>
      </p>
    </div>
  </div>
</body>
</html>
