<!-- /cocina/update_order_status.php -->
<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'cocina') {
    header('Location: /login.php');
    exit;
}

include '../config/db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: ordenes-activas.php');
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recogemos estado y canal enviados por el formulario
    $estado = $_POST['estado'] ?? '';
    $canal  = $_POST['canal']  ?? '';

    // Valores válidos para cada campo
    $estados_validos = ['en_proceso', 'completada', 'cancelada'];
    $canales_validos = ['local', 'entrega', 'takeaway'];

    // Valida estado
    if (!in_array($estado, $estados_validos, true)) {
        $errors[] = 'Estado inválido';
    }
    // Valida canal
    if (!in_array($canal, $canales_validos, true)) {
        $errors[] = 'Canal inválido';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
          UPDATE ordenes 
          SET estado = ?, canal = ?
          WHERE id = ?
        ");
        $stmt->execute([$estado, $canal, $id]);
        header('Location: ordenes-activas.php');
        exit;
    }
}

// Obtenemos datos actuales de la orden
$stmt = $pdo->prepare("
  SELECT 
    o.id,
    o.estado,
    o.canal,
    m.numero AS mesa_numero,
    DATE_FORMAT(o.fecha_hora_inicio, '%d/%m/%Y %H:%i') AS inicio
  FROM ordenes o
  LEFT JOIN mesas m ON o.mesa_id = m.id
  WHERE o.id = ?
");
$stmt->execute([$id]);
$o = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$o) {
    header('Location: ordenes-activas.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Actualizar Orden #<?= htmlspecialchars($o['id']) ?> - Cocina</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 pt-16">
  <?php include '../components/header.php'; ?>

  <main class="max-w-md mx-auto p-6">
    <section class="bg-white rounded-lg shadow p-6 animate-fade-in-up">
      <h2 class="text-2xl font-semibold mb-4">Actualizar Orden #<?= htmlspecialchars($o['id']) ?></h2>

      <div class="mb-4 text-sm text-gray-600">
        <?php if ($o['mesa_numero'] !== null): ?>
          <p><strong>Mesa:</strong> Mesa <?= htmlspecialchars($o['mesa_numero']) ?></p>
        <?php endif; ?>
        <p><strong>Canal Actual:</strong> <?= ucfirst(htmlspecialchars($o['canal'])) ?></p>
        <p><strong>Inicio:</strong> <?= htmlspecialchars($o['inicio']) ?></p>
        <p><strong>Estado Actual:</strong>
          <?= ucfirst(str_replace('_', ' ', htmlspecialchars($o['estado']))) ?>
        </p>
      </div>

      <?php if (!empty($errors)): ?>
        <ul class="mb-4 text-sm text-red-500 list-disc list-inside">
          <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <form 
        method="POST" 
        class="space-y-4"
        onsubmit="return confirm('¿Estás seguro de que deseas guardar estos cambios?');"
      >
        <!-- Selección de Nuevo Canal -->
        <div>
          <label for="canal" class="block text-sm font-medium text-gray-700">Nuevo Canal</label>
          <select
            id="canal"
            name="canal"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="local"    <?= ($o['canal'] === 'local')    ? 'selected' : '' ?>>Local</option>
            <option value="entrega"  <?= ($o['canal'] === 'entrega')  ? 'selected' : '' ?>>Entrega</option>
            <option value="takeaway" <?= ($o['canal'] === 'takeaway') ? 'selected' : '' ?>>Takeaway</option>
          </select>
        </div>

        <!-- Selección de Nuevo Estado -->
        <div>
          <label for="estado" class="block text-sm font-medium text-gray-700">Nuevo Estado</label>
          <select
            id="estado"
            name="estado"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="en_proceso" <?= ($o['estado'] === 'en_proceso')  ? 'selected' : '' ?>>
              En Proceso
            </option>
            <option value="completada"  <?= ($o['estado'] === 'completada')   ? 'selected' : '' ?>>
              Completada
            </option>
            <option value="cancelada"   <?= ($o['estado'] === 'cancelada')    ? 'selected' : '' ?>>
              Cancelada
            </option>
          </select>
        </div>

        <div class="flex justify-between mt-6">
          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
          >
            Guardar Cambios
          </button>
          <a
            href="ordenes-activas.php"
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
