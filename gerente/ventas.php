<?php
// /gerente/ventas.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'gerente') {
    header('Location: /login.php');
    exit;
}

require '../config/db.php';

// Mapeo de mes número a nombre en español
$mesesEspañol = [
    1  => 'enero',
    2  => 'febrero',
    3  => 'marzo',
    4  => 'abril',
    5  => 'mayo',
    6  => 'junio',
    7  => 'julio',
    8  => 'agosto',
    9  => 'septiembre',
    10 => 'octubre',
    11 => 'noviembre',
    12 => 'diciembre'
];

// 1. Ventas históricas totales por canal
$stmtCanal = $pdo->query("
    SELECT 
      canal, 
      SUM(total) AS suma_ventas
    FROM ordenes
    GROUP BY canal
");
$ventasPorCanal = [];
while ($row = $stmtCanal->fetch(PDO::FETCH_ASSOC)) {
    $ventasPorCanal[$row['canal']] = (float)$row['suma_ventas'];
}

// 2. Venta total histórica
$stmtTotal = $pdo->query("
    SELECT COALESCE(SUM(total), 0) AS total_historico
    FROM ordenes
");
$totalHistorico = (float)$stmtTotal->fetchColumn();

// 3. Promedio diario histórico (solo días con órdenes)
$stmtPromedio = $pdo->query("
    SELECT AVG(dia_total) AS promedio_diario FROM (
      SELECT DATE(fecha_hora_inicio) AS dia, SUM(total) AS dia_total
      FROM ordenes
      GROUP BY dia
    ) sub
");
$promedioDiario = (float)$stmtPromedio->fetchColumn();

// 4. Meses (últimos 6) con sus totales
$stmtMeses = $pdo->query("
    SELECT 
      DATE_FORMAT(fecha_hora_inicio, '%Y-%m') AS mes,
      SUM(total) AS total_mes
    FROM ordenes
    GROUP BY mes
    ORDER BY mes DESC
    LIMIT 6
");
$ventasUltimosMeses = $stmtMeses->fetchAll(PDO::FETCH_ASSOC);

// 5. Top 5 días con mayor venta
$stmtTopDias = $pdo->query("
    SELECT 
      DATE(fecha_hora_inicio) AS dia, 
      SUM(total) AS total_dia
    FROM ordenes
    GROUP BY dia
    ORDER BY total_dia DESC
    LIMIT 5
");
$topDias = $stmtTopDias->fetchAll(PDO::FETCH_ASSOC);

// 6. Ticket promedio histórico (suma total / número de órdenes completadas)
$stmtTicket = $pdo->query("
    SELECT 
      ROUND(COALESCE(SUM(total)/COUNT(*), 0), 2) AS ticket_promedio
    FROM ordenes
    WHERE estado = 'completada'
");
$ticketPromedio = (float)$stmtTicket->fetchColumn();

// Asegurar que cada canal exista
$canales = ['local', 'entrega', 'takeaway'];
foreach ($canales as $c) {
    if (!isset($ventasPorCanal[$c])) {
        $ventasPorCanal[$c] = 0.0;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Estadísticas de Ventas - Gerente</title>

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
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
    .card h3 { font-size: 1rem; font-weight: 600; color: #374151; }
    .card .amount { font-size: 1.75rem; font-weight: 700; color: #111827; }
    .card .label { font-size: 0.875rem; color: #6B7280; }
    .pt-header { padding-top: 4rem; }
  </style>
</head>
<body class="pt-header bg-gray-100 text-gray-800">
  <?php include '../components/header.php'; ?>

  <main class="max-w-6xl mx-auto p-6 space-y-12">
    <!-- Sección 1: Resumen Histórico General -->
    <section>
      <h1 class="text-2xl font-semibold mb-4">Resumen Histórico de Ventas</h1>
      <?php if ($totalHistorico <= 0): ?>
        <p class="text-gray-500">No hay ventas registradas.</p>
      <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Tarjeta: Ventas Totales -->
          <div class="card text-center">
            <h3>Ventas Totales</h3>
            <div class="amount">$<?= number_format($totalHistorico, 2) ?></div>
            <div class="label">Todos los canales</div>
          </div>

          <!-- Tarjeta: Canal Local -->
          <div class="card text-center">
            <h3>Canal Local</h3>
            <div class="amount">$<?= number_format($ventasPorCanal['local'], 2) ?></div>
            <div class="label">En restaurante</div>
          </div>

          <!-- Tarjeta: Canal Entrega -->
          <div class="card text-center">
            <h3>Canal Entrega</h3>
            <div class="amount">$<?= number_format($ventasPorCanal['entrega'], 2) ?></div>
            <div class="label">A domicilio</div>
          </div>

          <!-- Tarjeta: Canal Takeaway -->
          <div class="card text-center">
            <h3>Canal Takeaway</h3>
            <div class="amount">$<?= number_format($ventasPorCanal['takeaway'], 2) ?></div>
            <div class="label">Para llevar</div>
          </div>
        </div>
      <?php endif; ?>
    </section>

    <!-- Sección 2: Promedio Diario y Ticket Promedio -->
    <section>
      <h2 class="text-xl font-semibold mb-4">Promedios de Ventas</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div class="card text-center">
          <h3>Promedio Diario de Ventas</h3>
          <div class="amount">$<?= number_format($promedioDiario, 2) ?></div>
          <div class="label">Basado en días con órdenes</div>
        </div>
        <div class="card text-center">
          <h3>Ticket Promedio</h3>
          <div class="amount">$<?= number_format($ticketPromedio, 2) ?></div>
          <div class="label">Órdenes completadas</div>
        </div>
      </div>
    </section>

    <!-- Sección 3: Últimos 6 Meses -->
    <section>
      <h2 class="text-xl font-semibold mb-4">Ventas por Mes (Últimos 6)</h2>
      <?php if (empty($ventasUltimosMeses)): ?>
        <p class="text-gray-500">No hay datos mensuales suficientes.</p>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 bg-white rounded-lg shadow">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Mes</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Ventas</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <?php foreach ($ventasUltimosMeses as $m): ?>
                <?php
                  list($ano, $mesNum) = explode('-', $m['mes']);
                  $mesLabel = ucfirst($mesesEspañol[(int)$mesNum]) . " " . $ano;
                ?>
                <tr class="hover:bg-gray-50">
                  <td class="px-4 py-2 text-sm"><?= htmlspecialchars($mesLabel) ?></td>
                  <td class="px-4 py-2 text-sm">$<?= number_format($m['total_mes'], 2) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>

    <!-- Sección 4: Top 5 Días de Mayor Venta -->
    <section>
      <h2 class="text-xl font-semibold mb-4">Top 5 Días con Mayor Venta</h2>
      <?php if (empty($topDias)): ?>
        <p class="text-gray-500">No hay datos de días suficientes.</p>
      <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
          <?php foreach ($topDias as $d): ?>
            <?php
              $fechaDia = new DateTime($d['dia']);
              $diaNum   = $fechaDia->format('d');
              $mesNum   = (int)$fechaDia->format('n');
              $ano      = $fechaDia->format('Y');
              $labelDia = $diaNum . ' ' . ucfirst($mesesEspañol[$mesNum]) . ' ' . $ano;
            ?>
            <div class="card text-center">
              <h3><?= htmlspecialchars($labelDia) ?></h3>
              <div class="amount">$<?= number_format($d['total_dia'], 2) ?></div>
              <div class="label">Venta del día</div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
