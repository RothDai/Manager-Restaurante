<!-- /mesero/mesas.php -->
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
  <title>Mesas - Mesero</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- GSAP (opcional) -->
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.2/dist/gsap.min.js"></script>
</head>
<body class="bg-gray-100 text-gray-800 pt-16">
  <?php include '../components/header.php'; ?>

  <main class="max-w-4xl mx-auto p-6">
    <section class="bg-white rounded-lg shadow p-6 animate-fade-in-up">
      <h2 class="text-2xl font-semibold mb-4">Gestión de Mesas</h2>
      <?php
        $mesas = $pdo->query("
          SELECT id, numero, estado
          FROM mesas
          ORDER BY numero ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
      ?>
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php if (empty($mesas)): ?>
          <div class="col-span-full text-center text-gray-500">No hay mesas registradas.</div>
        <?php else: ?>
          <?php foreach ($mesas as $m): ?>
            <?php
              switch ($m['estado']) {
                case 'libre':
                  $colorBg = 'bg-green-100';
                  $colorText = 'text-green-800';
                  break;
                case 'ocupada':
                  $colorBg = 'bg-red-100';
                  $colorText = 'text-red-800';
                  break;
                case 'esperando_cuenta':
                  $colorBg = 'bg-yellow-100';
                  $colorText = 'text-yellow-800';
                  break;
                default:
                  $colorBg = 'bg-gray-100';
                  $colorText = 'text-gray-800';
              }
            ?>
            <div class="<?= $colorBg ?> rounded-lg p-4 flex flex-col items-center shadow">
              <div class="text-xl font-bold <?= $colorText ?>">Mesa <?= htmlspecialchars($m['numero']) ?></div>
              <div class="mt-2 text-sm <?= $colorText ?>">
                <?= ucfirst(str_replace('_', ' ', $m['estado'])) ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <?php include '../components/footer.php'; ?>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      gsap.registerPlugin(ScrollTrigger);
      gsap.utils.toArray('.animate-fade-in-up').forEach(section => {
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
