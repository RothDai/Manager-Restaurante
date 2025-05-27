<?php
// /gerente/menu.php

session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'gerente') {
    header('Location: /login.php');
    exit;
}

require '../config/db.php';

// Obtener todos los platillos del menú
$platillos = $pdo->query("
    SELECT 
      id,
      nombre,
      descripcion,
      categoria,
      precio_local,
      precio_entrega,
      precio_takeaway,
      alergenos,
      food_cost,
      activo
    FROM menu
    ORDER BY nombre ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gestión de Menú - Gerente</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- GSAP (opcional para animaciones) -->
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.2/dist/gsap.min.js"></script>
</head>
<body class="bg-gray-100 text-gray-800 pt-16">
  <?php include '../components/header.php'; ?>

  <main class="max-w-7xl mx-auto p-6 space-y-6">
    <div class="flex justify-between items-center">
      <h2 class="text-2xl font-semibold">Gestión de Menú</h2>
      <a href="create_menu.php" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
        + Crear Nuevo Platillo
      </a>
    </div>

    <section id="tabla-menu" class="bg-white rounded-lg shadow p-4 animate-fade-in-up">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Nombre</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Categoría</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Precio Local</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Precio Entrega</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Precio Takeaway</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Activo</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($platillos)): ?>
              <tr>
                <td colspan="7" class="px-4 py-4 text-center text-sm text-gray-500">
                  No hay platillos en el menú.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($platillos as $p): ?>
                <tr class="hover:bg-gray-50">
                  <td class="px-4 py-3 text-sm"><?= htmlspecialchars($p['nombre']) ?></td>
                  <td class="px-4 py-3 text-sm"><?= ucfirst(htmlspecialchars($p['categoria'])) ?></td>
                  <td class="px-4 py-3 text-sm">$<?= number_format($p['precio_local'], 2) ?></td>
                  <td class="px-4 py-3 text-sm">$<?= number_format($p['precio_entrega'], 2) ?></td>
                  <td class="px-4 py-3 text-sm">$<?= number_format($p['precio_takeaway'], 2) ?></td>
                  <td class="px-4 py-3 text-sm">
                    <?php if ((int)$p['activo'] === 1): ?>
                      <span class="text-green-600 font-medium">Sí</span>
                    <?php else: ?>
                      <span class="text-red-600 font-medium">No</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-sm">
                    <a href="edit_menu.php?id=<?= $p['id'] ?>"
                       class="text-blue-600 hover:text-blue-800 font-medium">
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
      gsap.utils.toArray('#tabla-menu').forEach(section => {
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
