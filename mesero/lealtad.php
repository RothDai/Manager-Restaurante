<!-- /mesero/lealtad.php -->
<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'mesero') {
    header('Location: /login.php');
    exit;
}

include '../config/db.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Programa de Lealtad - Mesero</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 pt-16">
  <?php include '../components/header.php'; ?>

  <main class="max-w-4xl mx-auto p-6">
    <section class="bg-white rounded-lg shadow p-6 animate-fade-in-up">
      <h2 class="text-2xl font-semibold mb-4">Programa de Lealtad</h2>
      <?php
        $leales = $pdo->query("
          SELECT nombre, email, puntos_lealtad
          FROM usuarios
          WHERE puntos_lealtad > 0
          ORDER BY puntos_lealtad DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
      ?>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Nombre</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Email</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Puntos</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($leales)): ?>
              <tr>
                <td colspan="3" class="px-4 py-4 text-center text-sm text-gray-500">
                  No hay clientes con puntos de lealtad.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($leales as $u): ?>
                <tr class="hover:bg-gray-50">
                  <td class="px-4 py-3 text-sm"><?= htmlspecialchars($u['nombre']) ?></td>
                  <td class="px-4 py-3 text-sm"><?= htmlspecialchars($u['email']) ?></td>
                  <td class="px-4 py-3 text-sm"><?= htmlspecialchars($u['puntos_lealtad']) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <?php include '../components/footer.php'; ?>
</body>
</html>
