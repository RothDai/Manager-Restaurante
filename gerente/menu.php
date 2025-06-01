<?php
// /gerente/menu.php

session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'gerente') {
    header('Location: /login.php');
    exit;
}

require '../config/db.php';

$errors = [];
$success = '';

// Procesamiento de formulario: eliminar platillo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $idToDelete = intval($_POST['id'] ?? 0);
    if ($idToDelete > 0) {
        // Verificar existencia antes de borrar
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM menu WHERE id = ?");
        $stmtCheck->execute([$idToDelete]);
        if ($stmtCheck->fetchColumn() > 0) {
            $stmtDel = $pdo->prepare("DELETE FROM menu WHERE id = ?");
            $stmtDel->execute([$idToDelete]);
            $success = "Platillo eliminado satisfactoriamente.";
        } else {
            $errors[] = "No se encontró el platillo especificado.";
        }
    } else {
        $errors[] = "ID de platillo inválido.";
    }
}

// Obtener todos los platillos del menú (agrupando por id para evitar duplicados)
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
    GROUP BY id
    ORDER BY nombre ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gestión de Menú - Gerente</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.2/dist/gsap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.2/dist/ScrollTrigger.min.js"></script>
</head>
<body class="bg-gray-100 text-gray-800 pt-16">
  <?php include '../components/header.php'; ?>

  <main class="max-w-7xl mx-auto p-6 space-y-6">
    <!-- Mensajes de éxito o error -->
    <?php if ($success): ?>
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
        <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        <ul class="list-disc pl-5">
          <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="flex justify-between items-center">
      <h2 class="text-2xl font-semibold">Gestión de Menú</h2>
      <a href="/gerente/create_menu.php" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
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
                  <td class="px-4 py-3 text-sm space-x-2">
                    <a href="/gerente/edit_menu.php?id=<?= $p['id'] ?>"
                       class="px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs">
                      Editar
                    </a>
                    <form method="POST" class="inline">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= $p['id'] ?>">
                      <button
                        type="submit"
                        class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs"
                        onclick="return confirm('¿Estás seguro de eliminar este platillo?');"
                      >
                        Eliminar
                      </button>
                    </form>
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
