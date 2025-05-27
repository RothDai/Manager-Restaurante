<?php
// /gerente/dashboard.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'gerente') {
    header('Location: /login.php');
    exit;
}

require '../config/db.php';

// 1. Ventas en Tiempo Real (Canal 'local')
// Ventas de hoy por hora
$stmtHoy = $pdo->prepare("
    SELECT HOUR(fecha_hora_inicio) AS hora,
           SUM(total) AS ventas
    FROM ordenes
    WHERE DATE(fecha_hora_inicio) = CURDATE()
      AND canal = 'local'
    GROUP BY hora
    ORDER BY hora
");
$stmtHoy->execute();
$ventasHoy = $stmtHoy->fetchAll(PDO::FETCH_ASSOC);

// Ventas de ayer por hora
$stmtAyer = $pdo->prepare("
    SELECT HOUR(fecha_hora_inicio) AS hora,
           SUM(total) AS ventas
    FROM ordenes
    WHERE DATE(fecha_hora_inicio) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
      AND canal = 'local'
    GROUP BY hora
    ORDER BY hora
");
$stmtAyer->execute();
$ventasAyer = $stmtAyer->fetchAll(PDO::FETCH_ASSOC);

// 2. Gestión de Personal (todos los usuarios menos 'mantenimiento')
$stmtEmpleados = $pdo->query("
    SELECT id, nombre, rol, fecha_registro
    FROM usuarios
    WHERE rol != 'mantenimiento'
    ORDER BY fecha_registro DESC
");
$empleados = $stmtEmpleados->fetchAll(PDO::FETCH_ASSOC);

// 3. Control de Inventario (todos los ingredientes)
$stmtInv = $pdo->query("
    SELECT nombre_ingrediente, cantidad, unidad, umbral_minimo
    FROM inventario
    ORDER BY nombre_ingrediente ASC
");
$inventario = $stmtInv->fetchAll(PDO::FETCH_ASSOC);

// Preparar JSON para Chart.js
$horasHoy      = array_column($ventasHoy, 'hora');
$ventasHoyVals = array_column($ventasHoy, 'ventas');
$horasAyer     = array_column($ventasAyer, 'hora');
$ventasAyerVals= array_column($ventasAyer, 'ventas');

$ventasHoyJSON  = json_encode($ventasHoyVals);
$ventasAyerJSON = json_encode($ventasAyerVals);
$horasHoyJSON   = json_encode($horasHoy);
$horasAyerJSON  = json_encode($horasAyer);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard Gerente - RestAI</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Librerías JS -->
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.2/dist/gsap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.2/dist/ScrollTrigger.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.1.0/chart.min.js"></script>
</head>
<body class="bg-gray-100 text-gray-800 pt-16"> <!-- pt-16 para dejar espacio al header fijo -->
  <?php include '../components/header.php'; ?>

  <main class="max-w-7xl mx-auto p-6 space-y-12">
    <!-- =========================== -->
    <!-- 1. Ventas en Tiempo Real   -->
    <!-- =========================== -->
    <section id="ventas-tiempo-real" class="bg-white rounded-lg shadow p-6 animate-fade-in-up">
      <h2 class="text-2xl font-semibold mb-4">Ventas en Tiempo Real (Canal Local)</h2>
      <div class="relative h-72">
        <canvas id="ventasComparativoChart"></canvas>
      </div>
      <p class="mt-2 text-sm text-gray-500">Comparativo: Hoy vs Ayer</p>
    </section>

    <!-- =========================== -->
    <!-- 2. Gestión de Personal     -->
    <!-- =========================== -->
    <section id="gestion-personal" class="bg-white rounded-lg shadow p-6 animate-fade-in-up">
      <h2 class="text-2xl font-semibold mb-4">Gestión de Personal</h2>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Nombre</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Rol</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Fecha Registro</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($empleados)): ?>
              <tr>
                <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">No hay empleados registrados.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($empleados as $e): ?>
                <tr class="hover:bg-gray-50">
                  <td class="px-4 py-3 text-sm"><?= htmlspecialchars($e['nombre']) ?></td>
                  <td class="px-4 py-3 text-sm"><?= ucfirst(htmlspecialchars($e['rol'])) ?></td>
                  <td class="px-4 py-3 text-sm"><?= htmlspecialchars($e['fecha_registro']) ?></td>
                  <td class="px-4 py-3 text-sm">
                    <a href="edit_employee.php?id=<?= $e['id'] ?>"
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

    <!-- =========================== -->
    <!-- 3. Control de Inventario   -->
    <!-- =========================== -->
    <section id="inventario-avanzado" class="bg-white rounded-lg shadow p-6 animate-fade-in-up">
      <h2 class="text-2xl font-semibold mb-4">Control de Inventario</h2>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Ingrediente</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Cantidad</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Unidad</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Umbral Mínimo</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($inventario)): ?>
              <tr>
                <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">No hay elementos en inventario.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($inventario as $ing): ?>
                <tr class="hover:bg-gray-50">
                  <td class="px-4 py-3 text-sm"><?= htmlspecialchars($ing['nombre_ingrediente']) ?></td>
                  <td class="px-4 py-3 text-sm"><?= htmlspecialchars($ing['cantidad']) ?></td>
                  <td class="px-4 py-3 text-sm"><?= htmlspecialchars($ing['unidad']) ?></td>
                  <td class="px-4 py-3 text-sm"><?= htmlspecialchars($ing['umbral_minimo']) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <!-- =========================== -->
    <!-- 4. Análisis de Reseñas     -->
    <!-- =========================== -->
    <section id="analisis-reseñas" class="bg-white rounded-lg shadow p-6 animate-fade-in-up">
      <h2 class="text-2xl font-semibold mb-4">Análisis de Reseñas</h2>
      <p class="text-gray-500">Esta sección puede rellenarse con análisis de texto, gráficas de puntuaciones y nube de palabras. (Placeholder)</p>
    </section>
  </main>

  <?php include '../components/footer.php'; ?>

  <!-- Scripts Adicionales -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      gsap.registerPlugin(ScrollTrigger);

      // 1. Gráfico de Ventas Comparativo (Hoy vs Ayer)
      const ctxVentas = document.getElementById('ventasComparativoChart').getContext('2d');
      const horasHoy       = <?= $horasHoyJSON ?>;
      const ventasHoyVals  = <?= $ventasHoyJSON ?>;
      const horasAyer      = <?= $horasAyerJSON ?>;
      const ventasAyerVals = <?= $ventasAyerJSON ?>;

      const ventasAyerAlineado = horasHoy.map(h => {
        const idx = horasAyer.indexOf(h);
        return idx !== -1 ? ventasAyerVals[idx] : 0;
      });

      new Chart(ctxVentas, {
        type: 'line',
        data: {
          labels: horasHoy,
          datasets: [
            {
              label: 'Hoy',
              data: ventasHoyVals,
              borderColor: 'rgba(59,130,246,0.8)',
              backgroundColor: 'rgba(59,130,246,0.2)',
              tension: 0.3
            },
            {
              label: 'Ayer',
              data: ventasAyerAlineado,
              borderColor: 'rgba(16,185,129,0.8)',
              backgroundColor: 'rgba(16,185,129,0.2)',
              tension: 0.3
            }
          ]
        },
        options: {
          responsive: true,
          scales: {
            x: { title: { display: true, text: 'Hora' } },
            y: { title: { display: true, text: 'Ventas (MXN)' }, beginAtZero: true }
          },
          animation: { duration: 800 }
        }
      });

      // Animar secciones
      gsap.utils.toArray('.animate-fade-in-up').forEach(section => {
        gsap.fromTo(
          section,
          { opacity: 0, y: 20 },
          { opacity: 1, y: 0, duration: 0.8, scrollTrigger: { trigger: section, start: 'top 85%' } }
        );
      });

      // Animar menú
      gsap.from('.nav-menu li', { opacity: 0, y: -10, duration: 0.6, stagger: 0.1 });
    });
  </script>
</body>
</html>
