<!-- /cocina/reposición.php -->
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
  <title>Reposición Rápida - Cocina</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- GSAP (opcional) -->
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.2/dist/gsap.min.js"></script>
</head>
<body class="bg-gray-100 text-gray-800 pt-16">
  <?php include '../components/header.php'; ?>

  <main class="max-w-4xl mx-auto p-6">
    <section id="reposición" class="bg-white rounded-lg shadow p-6 animate-fade-in-up">
      <h2 class="text-2xl font-semibold mb-4">Reposición Express de Ingredientes</h2>
      <?php
        $bajoStock = $pdo->query("
          SELECT id, nombre_ingrediente, cantidad, unidad, umbral_minimo
          FROM inventario
          WHERE cantidad <= umbral_minimo
          ORDER BY nombre_ingrediente ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
      ?>
      <?php if (empty($bajoStock)): ?>
        <p class="text-gray-500">No hay ingredientes por debajo del umbral mínimo.</p>
      <?php else: ?>
        <div class="overflow-x-auto mb-6">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Ingrediente</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Cantidad</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Unidad</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Umbral Mínimo</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Acción</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <?php foreach ($bajoStock as $ing): ?>
                <tr class="hover:bg-gray-50">
                  <td class="px-4 py-3 text-sm"><?= htmlspecialchars($ing['nombre_ingrediente']) ?></td>
                  <td class="px-4 py-3 text-sm"><?= htmlspecialchars($ing['cantidad']) ?></td>
                  <td class="px-4 py-3 text-sm"><?= htmlspecialchars($ing['unidad']) ?></td>
                  <td class="px-4 py-3 text-sm"><?= htmlspecialchars($ing['umbral_minimo']) ?></td>
                  <td class="px-4 py-3 text-sm">
                    <form method="POST" action="reposición.php" class="inline">
                      <input type="hidden" name="id" value="<?= $ing['id'] ?>">
                      <button type="submit" name="comprar"
                              class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
                        Reponer (+10)
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>

      <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comprar'])) {
          $idIng = (int) $_POST['id'];
          $stmtUpd = $pdo->prepare("
            UPDATE inventario
            SET cantidad = cantidad + 10
            WHERE id = ?
          ");
          $stmtUpd->execute([$idIng]);
          header('Location: reposición.php');
          exit;
        }
      ?>
    </section>
  </main>

  <?php include '../components/footer.php'; ?>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      gsap.registerPlugin(ScrollTrigger);
      gsap.utils.toArray('#reposición').forEach(section => {
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
