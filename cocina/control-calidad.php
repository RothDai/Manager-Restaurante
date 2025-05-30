<!-- /cocina/control-calidad.php -->
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
  <title>Control de Calidad - Cocina</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- GSAP (opcional) -->
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.2/dist/gsap.min.js"></script>
</head>
<body class="bg-gray-100 text-gray-800 pt-16">
  <?php include '../components/header.php'; ?>

  <main class="max-w-4xl mx-auto p-6">
    <section id="control-calidad" class="bg-white rounded-lg shadow p-6 animate-fade-in-up">
      <h2 class="text-2xl font-semibold mb-4">Control de Calidad de Órdenes</h2>

      <?php
        // Ahora usamos fecha_hora_inicio en lugar de fecha_hora_termino
        $ordenesQC = $pdo->query("
          SELECT
            o.id,
            o.mesa_id,
            m.numero AS mesa_numero,
            DATE_FORMAT(o.fecha_hora_inicio, '%d/%m/%Y %H:%i') AS terminado
          FROM ordenes o
          LEFT JOIN mesas m ON o.mesa_id = m.id
          WHERE o.estado = 'completada'
            AND (o.calidad_revision = 0 OR o.calidad_revision IS NULL)
          ORDER BY o.fecha_hora_inicio DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
      ?>

      <?php if (empty($ordenesQC)): ?>
        <p class="text-gray-500">No hay órdenes pendientes de control de calidad.</p>
      <?php else: ?>
        <div class="space-y-4">
          <?php foreach ($ordenesQC as $q): ?>
            <div class="bg-gray-50 rounded-lg p-4 shadow flex flex-col sm:flex-row sm:justify-between sm:items-center">
              <div>
                <p class="font-medium">
                  Orden #<?= htmlspecialchars($q['id']) ?>
                  <?php if ($q['mesa_numero'] !== null): ?>
                    — Mesa <?= htmlspecialchars($q['mesa_numero']) ?>
                  <?php endif; ?>
                </p>
                <p class="mt-1 text-sm text-gray-600">
                  Creada: <?= htmlspecialchars($q['terminado']) ?>
                </p>
              </div>
              <div class="mt-4 sm:mt-0">
                <form method="POST" action="control-calidad.php" class="inline">
                  <input type="hidden" name="id" value="<?= $q['id'] ?>">
                  <button type="submit" name="revisar" 
                          class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    Marcar como Revisada
                  </button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php
        // Procesamos el POST para marcar la orden como revisada
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['revisar'])) {
          $idQC = intval($_POST['id']);
          $stmtUpd = $pdo->prepare("UPDATE ordenes SET calidad_revision = 1 WHERE id = ?");
          $stmtUpd->execute([$idQC]);
          header('Location: control-calidad.php');
          exit;
        }
      ?>
    </section>
  </main>

  <?php include '../components/footer.php'; ?>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      gsap.registerPlugin(ScrollTrigger);
      gsap.utils.toArray('#control-calidad').forEach(section => {
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
