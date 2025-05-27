<?php
// /login.php
session_start();
require 'config/db.php';

$errors = [];
if (isset($_SESSION['id'])) {
    // Si ya está autenticado, redirigir según rol
    switch ($_SESSION['rol']) {
        case 'gerente': header('Location: /gerente/dashboard.php'); break;
        case 'mesero':  header('Location: /mesero/ordenes.php');   break;
        case 'cocina':  header('Location: /cocina/ordenes-activas.php'); break;
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['user'] ?? '');
    $pass = $_POST['pass'] ?? '';

    if (!$user) $errors[] = 'Usuario o email requerido';
    if (!$pass) $errors[] = 'Contraseña requerida';

    if (empty($errors)) {
        $stmt = $pdo->prepare("
          SELECT id, nombre, email, contrasena_hash, rol
          FROM usuarios
          WHERE email = ? OR nombre = ?
        ");
        $stmt->execute([$user, $user]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($u && password_verify($pass, $u['contrasena_hash'])) {
            session_regenerate_id(true);
            $_SESSION['id']     = $u['id'];
            $_SESSION['nombre'] = $u['nombre'];
            $_SESSION['email']  = $u['email'];
            $_SESSION['rol']    = $u['rol'];

            switch ($u['rol']) {
                case 'gerente': header('Location: /gerente/dashboard.php'); break;
                case 'mesero':  header('Location: /mesero/ordenes.php');   break;
                case 'cocina':  header('Location: /cocina/ordenes-activas.php'); break;
            }
            exit;
        } else {
            $errors[] = 'Credenciales incorrectas';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login - RestAI</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">
  <div class="w-full max-w-md">
    <div class="bg-white rounded-lg shadow-lg p-6 animate-fade-in-up">
      <h1 class="text-2xl font-bold text-center text-blue-600 mb-4">Iniciar Sesión</h1>

      <?php if (!empty($_GET['registered'])): ?>
        <div class="text-green-500 mb-4 text-center">Registro exitoso. Inicia sesión.</div>
      <?php endif; ?>

      <?php if ($errors): ?>
        <ul class="text-red-500 mb-4 list-disc list-inside text-sm">
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <form method="POST" class="space-y-4">
        <div>
          <label for="user" class="block text-sm font-medium text-gray-700">Usuario o Email</label>
          <input
            id="user"
            name="user"
            type="text"
            required
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          />
        </div>
        <div>
          <label for="pass" class="block text-sm font-medium text-gray-700">Contraseña</label>
          <input
            id="pass"
            name="pass"
            type="password"
            required
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          />
        </div>
        <div class="flex justify-between items-center">
          <a href="register.php" class="text-blue-600 hover:text-blue-800 text-sm">Crear cuenta</a>
          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
          >
            Entrar
          </button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
