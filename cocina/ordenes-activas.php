<!-- /cocina/ordenes-activas.php -->
<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'cocina') {
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
  <title>Cola de Producción - Cocina</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- GSAP (opcional) -->
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.2/dist/gsap.min.js"></script>
</head>
<body class="bg-gray-100 text-gray-800 pt-16">
  <?php include '../components/header.php'; ?>

  <main class="max-w-4xl mx-auto p-6">
    <section id="cola-produccion" class="bg-white rounded-lg shadow p-6 animate-fade-in-up">
      <h2 class="text-2xl font-semibold mb-4">Cola de Producción</h2>
      <?php
        // Quitamos los campos de tiempo_estimado_preparacion y tiempo_real_preparacion
        $ordenes = $pdo->query("
          SELECT o.id,
                 o.estado,
                 o.canal,
                 m.numero AS mesa_numero,
                 DATE_FORMAT(o.fecha_hora_inicio, '%d/%m/%Y %H:%i') AS inicio
          FROM ordenes o
          LEFT JOIN mesas m ON o.mesa_id = m.id
          WHERE o.estado IN ('pendiente', 'en_proceso')
          ORDER BY o.fecha_hora_inicio ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
      ?>
      <?php if (empty($ordenes)): ?>
        <p class="text-gray-500">No hay órdenes pendientes o en proceso.</p>
      <?php else: ?>
        <div class="space-y-4">
          <?php foreach ($ordenes as $ord): ?>
            <div class="bg-gray-50 rounded-lg p-4 shadow flex flex-col sm:flex-row sm:justify-between sm:items-center">
              <div>
                <p class="font-medium">
                  Orden #<?= htmlspecialchars($ord['id']) ?>
                  <?php if ($ord['mesa_numero'] !== null): ?>
                    — Mesa <?= htmlspecialchars($ord['mesa_numero']) ?>
                  <?php endif; ?>
                </p>
                <p class="text-sm text-gray-600">
                  Canal: <?= ucfirst(htmlspecialchars($ord['canal'])) ?> |
                  Inicio: <?= htmlspecialchars($ord['inicio']) ?>
                </p>
                <p class="mt-2 text-sm">
                  <span class="font-medium">Estado:</span>
                  <?= ucfirst(str_replace('_', ' ', htmlspecialchars($ord['estado']))) ?>
                </p>
              </div>
              <div class="mt-4 sm:mt-0">
                <a href="update_order_status.php?id=<?= $ord['id'] ?>"
                   class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                  Actualizar Estado
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </main>

  <?php include '../components/footer.php'; ?>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      gsap.registerPlugin(ScrollTrigger);
      gsap.utils.toArray('#cola-produccion').forEach(section => {
        gsap.fromTo(
          section,
          { opacity: 0, y: 20 },
          { opacity: 1, y: 0, duration: 0.8, scrollTrigger: { trigger: section, start: 'top 85%' } }
        );
      });
    });
  </script>
</body>
</html>
