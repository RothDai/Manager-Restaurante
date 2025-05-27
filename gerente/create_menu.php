<?php
// /gerente/create_menu.php

session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'gerente') {
    header('Location: /login.php');
    exit;
}

require '../config/db.php';

$errors = [];

// Categorías permitidas
$categorias = ['entrada', 'plato_fuerte', 'postre', 'bebida'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre            = trim($_POST['nombre'] ?? '');
    $descripcion       = trim($_POST['descripcion'] ?? '');
    $categoria         = $_POST['categoria'] ?? '';
    $precio_local      = $_POST['precio_local'] ?? '';
    $precio_entrega    = $_POST['precio_entrega'] ?? '';
    $precio_takeaway   = $_POST['precio_takeaway'] ?? '';
    $alergenos         = trim($_POST['alergenos'] ?? '');
    $food_cost         = $_POST['food_cost'] ?? '';
    $activo            = isset($_POST['activo']) ? 1 : 0;

    // Validaciones mínimas
    if ($nombre === '') {
        $errors[] = 'El nombre del platillo es obligatorio.';
    }
    if (!in_array($categoria, $categorias, true)) {
        $errors[] = 'Debes seleccionar una categoría válida.';
    }
    if (!is_numeric($precio_local) || (float)$precio_local < 0) {
        $errors[] = 'Precio Local inválido.';
    }
    if (!is_numeric($precio_entrega) || (float)$precio_entrega < 0) {
        $errors[] = 'Precio Entrega inválido.';
    }
    if (!is_numeric($precio_takeaway) || (float)$precio_takeaway < 0) {
        $errors[] = 'Precio Takeaway inválido.';
    }
    if (!is_numeric($food_cost) || (float)$food_cost < 0) {
        $errors[] = 'Food Cost inválido.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO menu 
                  (nombre, descripcion, categoria, precio_local, precio_entrega, precio_takeaway, alergenos, food_cost, activo)
                VALUES 
                  (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $nombre,
                $descripcion,
                $categoria,
                (float)$precio_local,
                (float)$precio_entrega,
                (float)$precio_takeaway,
                $alergenos,
                (float)$food_cost,
                $activo
            ]);
            header('Location: menu.php');
            exit;
        } catch (Exception $e) {
            $errors[] = 'Error al insertar en base de datos: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Crear Platillo - Gerente</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 pt-16">
  <?php include '../components/header.php'; ?>

  <main class="max-w-md mx-auto p-6">
    <section class="bg-white rounded-lg shadow p-6 animate-fade-in-up">
      <h2 class="text-2xl font-semibold mb-4">Crear Nuevo Platillo</h2>

      <?php if (!empty($errors)): ?>
        <ul class="mb-4 text-sm text-red-500 list-disc list-inside">
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
            value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
            required
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <div>
          <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción</label>
          <textarea
            id="descripcion"
            name="descripcion"
            rows="3"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          ><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
        </div>

        <div>
          <label for="categoria" class="block text-sm font-medium text-gray-700">Categoría</label>
          <select
            id="categoria"
            name="categoria"
            required
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Selecciona...</option>
            <?php foreach ($categorias as $cat): ?>
              <option value="<?= $cat ?>" <?= (($_POST['categoria'] ?? '') === $cat) ? 'selected' : '' ?>>
                <?= ucfirst(str_replace('_', ' ', $cat)) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="precio_local" class="block text-sm font-medium text-gray-700">Precio Local</label>
            <input
              type="number"
              step="0.01"
              id="precio_local"
              name="precio_local"
              value="<?= htmlspecialchars($_POST['precio_local'] ?? '') ?>"
              required
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div>
            <label for="precio_entrega" class="block text-sm font-medium text-gray-700">Precio Entrega</label>
            <input
              type="number"
              step="0.01"
              id="precio_entrega"
              name="precio_entrega"
              value="<?= htmlspecialchars($_POST['precio_entrega'] ?? '') ?>"
              required
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div>
            <label for="precio_takeaway" class="block text-sm font-medium text-gray-700">Precio Takeaway</label>
            <input
              type="number"
              step="0.01"
              id="precio_takeaway"
              name="precio_takeaway"
              value="<?= htmlspecialchars($_POST['precio_takeaway'] ?? '') ?>"
              required
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div>
            <label for="food_cost" class="block text-sm font-medium text-gray-700">Food Cost</label>
            <input
              type="number"
              step="0.01"
              id="food_cost"
              name="food_cost"
              value="<?= htmlspecialchars($_POST['food_cost'] ?? '') ?>"
              required
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
        </div>

        <div>
          <label for="alergenos" class="block text-sm font-medium text-gray-700">Alérgenos (comma-separated)</label>
          <input
            type="text"
            id="alergenos"
            name="alergenos"
            value="<?= htmlspecialchars($_POST['alergenos'] ?? '') ?>"
            placeholder="ej. gluten, lactosa, nueces"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <div class="flex items-center">
          <input
            type="checkbox"
            id="activo"
            name="activo"
            value="1"
            <?= isset($_POST['activo']) ? 'checked' : '' ?>
            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
          />
          <label for="activo" class="ml-2 block text-sm text-gray-700">Activo</label>
        </div>

        <div class="flex justify-between mt-6">
          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
          >
            Guardar Platillo
          </button>
          <a
            href="menu.php"
            class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400"
          >
            Cancelar
          </a>
        </div>
      </form>
    </section>
  </main>

  <?php include '../components/footer.php'; ?>
</body>
</html>
