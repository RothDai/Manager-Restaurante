<?php
// /gerente/desempeno.php

session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'gerente') {
    header('Location: /login.php');
    exit;
}

require '../config/db.php';

/*
  Esta página reemplaza reseñas.php y ofrece un análisis de desempeño:
  1. Ventas por empleado (meseros) históricas.
  2. Tiempo promedio de preparación (para cocina) y entrega (para entrega).
  3. Top 5 meseros por ventas totales.
  4. Distribución de órdenes completadas vs canceladas.
*/

// 1. Ventas históricas por mesero (usuarios con rol='mesero')
$stmtVentasMeseros = $pdo->query("
    SELECT u.id, u.nombre, COALESCE(SUM(o.total), 0) AS total_vendido
    FROM usuarios u
    LEFT JOIN ordenes o ON o.usuario_id = u.id AND o.estado = 'completada'
    WHERE u.rol = 'mesero'
    GROUP BY u.id
    ORDER BY total_vendido DESC
");
$ventasMeseros = $stmtVentasMeseros->fetchAll(PDO::FETCH_ASSOC);

// 2. Tiempo promedio de preparación y entrega (solo órdenes completadas)
$stmtTiempos = $pdo->query("
    SELECT 
      ROUND(AVG(o.tiempo_real_preparacion), 2) AS prom_preparacion,
      ROUND(AVG(o.tiempo_real_entrega), 2)    AS prom_entrega
    FROM ordenes o
    WHERE o.estado = 'completada'
");
$tiempos = $stmtTiempos->fetch(PDO::FETCH_ASSOC);
$promPrep   = $tiempos['prom_preparacion']   ?? 0;
$promEntrega= $tiempos['prom_entrega']      ?? 0;

// 3. Top 5 meseros (ya en $ventasMeseros)
$top5Meseros = array_slice($ventasMeseros, 0, 5);

// 4. Distribución órdenes completadas vs canceladas
$stmtDistOrdenes = $pdo->query("
    SELECT 
      CASE 
        WHEN estado = 'completada' THEN 'Completadas' 
        WHEN estado = 'cancelada'  THEN 'Canceladas' 
        ELSE 'Otras' 
      END AS estado_label,
      COUNT(*) AS cantidad
    FROM ordenes
    GROUP BY estado_label
");
$distRaw = $stmtDistOrdenes->fetchAll(PDO::FETCH_ASSOC);

// Asegurar que existan ambas categorías
$distOrdenes = ['Completadas' => 0, 'Canceladas' => 0, 'Otras' => 0];
foreach ($distRaw as $row) {
    $label = $row['estado_label'];
    $distOrdenes[$label] = (int)$row['cantidad'];
}

// Preparar JSON para Chart.js
$etiquetasDist = json_encode(array_keys($distOrdenes));
$valoresDist   = json_encode(array_values($distOrdenes));
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Desempeño Operativo - Gerente</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.2/dist/gsap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.2/dist/ScrollTrigger.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.1.0/chart.min.js"></script>
  <style>
    body { background: #F3F4F6; color: #1F2937; }
    .card {
      background: #FFFFFF;
      border-radius: .75rem;
      padding: 1.5rem;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      transition: transform 0.3s;
    }
    .card:hover { transform: translateY(-5px); }
    .pt-header { padding-top: 4rem; }
  </style>
</head>
<body class="pt-header bg-gray-100 text-gray-800">
  <?php include '../components/header.php'; ?>

  <main class="max-w-6xl mx-auto p-6 space-y-12">
    <!-- Sección 1: Ventas por Mesero -->
    <section>
      <h1 class="text-3xl font-semibold mb-4">Ventas Históricas por Mesero</h1>
      <?php if (empty($ventasMeseros)): ?>
        <p class="text-gray-500">No hay datos de ventas por mesero.</p>
      <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php foreach ($ventasMeseros as $m): ?>
            <div class="card">
              <div class="flex justify-between items-center">
                <span class="font-medium"><?= htmlspecialchars($m['nombre']) ?></span>
                <span class="text-lg font-bold">$<?= number_format($m['total_vendido'], 2) ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <!-- Sección 2: Tiempos Promedio -->
    <section>
      <h2 class="text-2xl font-semibold mb-4">Tiempos Promedio</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div class="card text-center">
          <h3 class="text-lg font-medium mb-2">Preparación Promedio</h3>
          <p class="text-4xl font-bold"><?= number_format($promPrep, 2) ?> min</p>
        </div>
        <div class="card text-center">
          <h3 class="text-lg font-medium mb-2">Entrega Promedio</h3>
          <p class="text-4xl font-bold"><?= number_format($promEntrega, 2) ?> min</p>
        </div>
      </div>
    </section>

    <!-- Sección 3: Top 5 Meseros -->
    <section>
      <h2 class="text-2xl font-semibold mb-4">Top 5 Meseros por Ventas</h2>
      <?php if (empty($top5Meseros)): ?>
        <p class="text-gray-500">No hay datos suficientes para Top 5.</p>
      <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
          <?php foreach ($top5Meseros as $idx => $tm): ?>
            <div class="card text-center">
              <h3 class="text-lg font-medium">#<?= $idx + 1 ?> <?= htmlspecialchars($tm['nombre']) ?></h3>
              <p class="text-2xl font-bold">$<?= number_format($tm['total_vendido'], 2) ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      gsap.registerPlugin(ScrollTrigger);
      gsap.utils.toArray('.card').forEach(card => {
        gsap.fromTo(card,
          { opacity: 0, y: 20 },
          { opacity: 1, y: 0, duration: 0.6, scrollTrigger: { trigger: card, start: 'top 90%' } }
        );
      });

      // Configurar Chart.js para distribución de órdenes
      const ctx = document.getElementById('ordenesChart').getContext('2d');
      const etiquetas = <?= $etiquetasDist ?>;  // ["Completadas","Canceladas","Otras"]
      const valores   = <?= $valoresDist ?>;    // [n1, n2, n3]
      new Chart(ctx, {
        type: 'pie',
        data: {
          labels: etiquetas,
          datasets: [{
            data: valores,
            backgroundColor: ['#10B981','#EF4444','#9CA3AF'],
            borderColor: '#FFFFFF',
            borderWidth: 2
          }]
        },
        options: {
          plugins: {
            legend: {
              position: 'bottom',
              labels: { color: '#374151', padding: 20 }
            },
            tooltip: {
              callbacks: {
                label: ctx => ctx.label + ': ' + ctx.parsed + ' órdenes'
              }
            }
          }
        }
      });
    });
  </script>
</body>
</html>
