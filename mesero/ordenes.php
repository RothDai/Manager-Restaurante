<!-- /mesero/ordenes.php -->
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
  <title>Registro de Órdenes - Mesero</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- GSAP (opcional para animaciones) -->
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.2/dist/gsap.min.js"></script>
</head>
<body class="bg-gray-100 text-gray-800 pt-16">
  <?php include '../components/header.php'; ?>

  <main class="max-w-4xl mx-auto p-6">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-2xl font-semibold">Registro de Órdenes</h2>
      <a href="create_order.php" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
        + Crear Orden
      </a>
    </div>

    <?php
      $ordenes = $pdo->query("
        SELECT 
          o.id,
          o.estado,
          o.total,
          o.canal,
          m.numero AS mesa_numero,
          DATE_FORMAT(o.fecha_hora_inicio, '%d/%m/%Y %H:%i') AS inicio
        FROM ordenes o
        LEFT JOIN mesas m ON o.mesa_id = m.id
        ORDER BY o.fecha_hora_inicio DESC
      ")->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <section id="registro-ordenes" class="bg-white rounded-lg shadow p-6 animate-fade-in-up">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">ID</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Mesa</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Canal</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Estado</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Total</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Inicio</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($ordenes)): ?>
              <tr>
                <td colspan="7" class="px-4 py-4 text-center text-sm text-gray-500">
                  No hay órdenes registradas.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($ordenes as $o): ?>
                <tr class="hover:bg-gray-50">
                  <td class="px-4 py-3 text-sm"><?= htmlspecialchars($o['id']) ?></td>
                  <td class="px-4 py-3 text-sm">
                    <?= $o['mesa_numero'] !== null 
                        ? 'Mesa ' . htmlspecialchars($o['mesa_numero']) 
                        : '—' ?>
                  </td>
                  <td class="px-4 py-3 text-sm"><?= ucfirst(htmlspecialchars($o['canal'])) ?></td>
                  <td class="px-4 py-3 text-sm"><?= ucfirst(str_replace('_', ' ', htmlspecialchars($o['estado']))) ?></td>
                  <td class="px-4 py-3 text-sm">$<?= number_format($o['total'], 2) ?></td>
                  <td class="px-4 py-3 text-sm"><?= htmlspecialchars($o['inicio']) ?></td>
                  <td class="px-4 py-3 text-sm">
                    <a href="edit_order.php?id=<?= $o['id'] ?>"
                       class="inline-block px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                      Editar
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <?php include '../components/footer.php'; ?>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      gsap.registerPlugin(ScrollTrigger);
      gsap.utils.toArray('#registro-ordenes tbody tr').forEach((row, i) => {
        gsap.from(row, { opacity: 0, y: 20, duration: 0.5, delay: i * 0.05,
          scrollTrigger: { trigger: row, start: 'top 90%' }
        });
      });
    });
  </script>
</body>
</html>
