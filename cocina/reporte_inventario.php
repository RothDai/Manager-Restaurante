<!-- /cocina/reporte_inventario.php -->
<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'cocina') {
    header('Location: /login.php');
    exit;
}

include '../config/db.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action               = $_POST['action'] ?? '';
    $nombre               = trim($_POST['nombre_ingrediente'] ?? '');
    $cantidad             = $_POST['cantidad'] ?? '';
    $unidad               = trim($_POST['unidad'] ?? '');
    $umbral               = $_POST['umbral_minimo'] ?? '';
    $proveedor            = trim($_POST['proveedor'] ?? '');
    $fecha_reposicion     = $_POST['fecha_ultima_reposicion'] ?? '';
    $desperdicio_cantidad = $_POST['desperdicio_cantidad'] ?? '';
    $desperdicio_causa    = trim($_POST['desperdicio_causa'] ?? '');
    $usuario_reporta_id   = $_SESSION['id'];

    // Validaciones
    if ($nombre === '') {
        $errors[] = 'El nombre del ingrediente es obligatorio.';
    }
    if ($unidad === '') {
        $errors[] = 'La unidad es obligatoria.';
    }
    if (!is_numeric($cantidad) || $cantidad < 0) {
        $errors[] = 'La cantidad debe ser un número ≥ 0.';
    }
    if (!is_numeric($umbral) || $umbral < 0) {
        $errors[] = 'El umbral mínimo debe ser un número ≥ 0.';
    }
    if ($proveedor === '') {
        $errors[] = 'El proveedor es obligatorio.';
    }
    if ($fecha_reposicion === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_reposicion)) {
        $errors[] = 'La fecha de última reposición es inválida.';
    } else {
        $d = DateTime::createFromFormat('Y-m-d', $fecha_reposicion);
        if (!$d || $d->format('Y-m-d') !== $fecha_reposicion) {
            $errors[] = 'La fecha de última reposición no es válida.';
        }
    }
    if (!is_numeric($desperdicio_cantidad) || $desperdicio_cantidad < 0) {
        $errors[] = 'La cantidad de desperdicio debe ser un número ≥ 0.';
    }

    if ($action === 'create' && empty($errors)) {
        $stmt = $pdo->prepare("
          INSERT INTO inventario 
            (nombre_ingrediente, cantidad, unidad, umbral_minimo, proveedor, 
             fecha_ultima_reposicion, desperdicio_cantidad, desperdicio_causa, usuario_reporta_id)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $nombre,
            $cantidad,
            $unidad,
            $umbral,
            $proveedor,
            $fecha_reposicion,
            $desperdicio_cantidad,
            $desperdicio_causa === '' ? null : $desperdicio_causa,
            $usuario_reporta_id
        ]);
        header('Location: reporte_inventario.php');
        exit;
    }

    if ($action === 'edit' && isset($_POST['id']) && empty($errors)) {
        $id   = intval($_POST['id']);
        $stmt = $pdo->prepare("
          UPDATE inventario
          SET 
            nombre_ingrediente        = ?,
            cantidad                  = ?,
            unidad                    = ?,
            umbral_minimo             = ?,
            proveedor                 = ?,
            fecha_ultima_reposicion   = ?,
            desperdicio_cantidad      = ?,
            desperdicio_causa         = ?,
            usuario_reporta_id        = ?
          WHERE id = ?
        ");
        $stmt->execute([
            $nombre,
            $cantidad,
            $unidad,
            $umbral,
            $proveedor,
            $fecha_reposicion,
            $desperdicio_cantidad,
            $desperdicio_causa === '' ? null : $desperdicio_causa,
            $usuario_reporta_id,
            $id
        ]);
        header('Location: reporte_inventario.php');
        exit;
    }
}

// Recuperar registros únicos
$stmt = $pdo->query("
  SELECT 
    i.id,
    i.nombre_ingrediente,
    i.cantidad,
    i.unidad,
    i.umbral_minimo,
    i.proveedor,
    DATE_FORMAT(i.fecha_ultima_reposicion, '%d/%m/%Y') AS fecha_reposicion_fmt,
    i.desperdicio_cantidad,
    i.desperdicio_causa,
    u.nombre AS usuario_reporta
  FROM inventario i
  LEFT JOIN usuarios u ON i.usuario_reporta_id = u.id
  GROUP BY i.id
  ORDER BY i.nombre_ingrediente ASC
");
$inventario = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si se solicita editar, obtenemos el registro
$editItem = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $stmt2   = $pdo->prepare("SELECT * FROM inventario WHERE id = ?");
    $stmt2->execute([$edit_id]);
    $editItem = $stmt2->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Reporte de Inventario - Cocina</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- GSAP -->
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.2/dist/gsap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.2/dist/ScrollTrigger.min.js"></script>
</head>
<body class="bg-gray-100 text-gray-800 pt-16">
  <?php include '../components/header.php'; ?>

  <main class="max-w-6xl mx-auto p-6 space-y-8">
    <!-- Formulario para crear o editar inventario -->
    <section id="form-inventario" class="bg-white rounded-lg shadow p-6 animate-fade-in-up">
      <h2 class="text-2xl font-semibold mb-4">
        <?= $editItem ? 'Editar Ingrediente' : 'Agregar Nuevo Ingrediente' ?>
      </h2>
      <?php if (!empty($errors)): ?>
        <ul class="mb-4 text-sm text-red-500 list-disc list-inside">
          <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <form method="POST" class="space-y-4">
        <input type="hidden" name="action" value="<?= $editItem ? 'edit' : 'create' ?>">
        <?php if ($editItem): ?>
          <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
        <?php endif; ?>

        <div>
          <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre del Ingrediente</label>
          <input
            id="nombre"
            name="nombre_ingrediente"
            type="text"
            value="<?= htmlspecialchars($editItem['nombre_ingrediente'] ?? '') ?>"
            required
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label for="cantidad" class="block text-sm font-medium text-gray-700">Cantidad</label>
            <input
              id="cantidad"
              name="cantidad"
              type="number"
              step="0.01"
              min="0"
              value="<?= htmlspecialchars($editItem['cantidad'] ?? '') ?>"
              required
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
            />
          </div>
          <div>
            <label for="unidad" class="block text-sm font-medium text-gray-700">Unidad (kg, l, etc.)</label>
            <input
              id="unidad"
              name="unidad"
              type="text"
              value="<?= htmlspecialchars($editItem['unidad'] ?? '') ?>"
              required
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
            />
          </div>
          <div>
            <label for="umbral" class="block text-sm font-medium text-gray-700">Umbral Mínimo</label>
            <input
              id="umbral"
              name="umbral_minimo"
              type="number"
              step="0.01"
              min="0"
              value="<?= htmlspecialchars($editItem['umbral_minimo'] ?? '') ?>"
              required
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
            />
          </div>
        </div>

        <div>
          <label for="proveedor" class="block text-sm font-medium text-gray-700">Proveedor</label>
          <input
            id="proveedor"
            name="proveedor"
            type="text"
            value="<?= htmlspecialchars($editItem['proveedor'] ?? '') ?>"
            required
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="fecha_reposicion" class="block text-sm font-medium text-gray-700">Fecha Última Reposición</label>
            <input
              id="fecha_reposicion"
              name="fecha_ultima_reposicion"
              type="date"
              value="<?= htmlspecialchars($editItem['fecha_ultima_reposicion'] ?? '') ?>"
              required
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
            />
          </div>
          <div>
            <label for="desperdicio_cantidad" class="block text-sm font-medium text-gray-700">Cantidad de Desperdicio</label>
            <input
              id="desperdicio_cantidad"
              name="desperdicio_cantidad"
              type="number"
              step="0.01"
              min="0"
              value="<?= htmlspecialchars($editItem['desperdicio_cantidad'] ?? '0') ?>"
              required
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
            />
          </div>
        </div>

        <div>
          <label for="desperdicio_causa" class="block text-sm font-medium text-gray-700">Causa de Desperdicio (opcional)</label>
          <textarea
            id="desperdicio_causa"
            name="desperdicio_causa"
            rows="2"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
          ><?= htmlspecialchars($editItem['desperdicio_causa'] ?? '') ?></textarea>
        </div>

        <div class="flex justify-end space-x-4">
          <?php if ($editItem): ?>
            <a href="reporte_inventario.php" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
              Cancelar Edición
            </a>
          <?php endif; ?>
          <button
            type="submit"
            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
          >
            <?= $editItem ? 'Guardar Cambios' : 'Agregar Ingrediente' ?>
          </button>
        </div>
      </form>
    </section>

    <!-- Tabla de inventario -->
    <section id="reporte-inventario" class="bg-white rounded-lg shadow p-6 animate-fade-in-up">
      <h2 class="text-2xl font-semibold mb-4">Inventario Disponible</h2>
      <?php if (empty($inventario)): ?>
        <p class="text-gray-500">No hay registros de inventario disponibles.</p>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table id="tblInventario" class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-200">
              <tr>
                <th class="px-4 py-2 text-left text-gray-700 uppercase text-sm">Ingrediente</th>
                <th class="px-4 py-2 text-left text-gray-700 uppercase text-sm">Cantidad</th>
                <th class="px-4 py-2 text-left text-gray-700 uppercase text-sm">Unidad</th>
                <th class="px-4 py-2 text-left text-gray-700 uppercase text-sm">Umbral Mínimo</th>
                <th class="px-4 py-2 text-left text-gray-700 uppercase text-sm">Proveedor</th>
                <th class="px-4 py-2 text-left text-gray-700 uppercase text-sm">Última Reposición</th>
                <th class="px-4 py-2 text-left text-gray-700 uppercase text-sm">Desperdicio (cant.)</th>
                <th class="px-4 py-2 text-left text-gray-700 uppercase text-sm">Causa Desperdicio</th>
                <th class="px-4 py-2 text-left text-gray-700 uppercase text-sm">Reportó</th>
                <th class="px-4 py-2 text-left text-gray-700 uppercase text-sm">Estado</th>
                <th class="px-4 py-2 text-left text-gray-700 uppercase text-sm">Acciones</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <?php foreach ($inventario as $item): ?>
                <?php
                  // Determinar estado
                  $estado = 'OK';
                  $fila_class = '';
                  if ($item['cantidad'] <= 0) {
                    $estado = 'Agotado';
                    $fila_class = 'bg-red-100';
                  } elseif ($item['cantidad'] <= $item['umbral_minimo']) {
                    $estado = 'Bajo';
                    $fila_class = 'bg-yellow-100';
                  }
                ?>
                <tr class="<?= $fila_class ?>">
                  <td class="px-4 py-3 text-sm"><?= htmlspecialchars($item['nombre_ingrediente']) ?></td>
                  <td class="px-4 py-3 text-sm"><?= htmlspecialchars($item['cantidad']) ?></td>
                  <td class="px-4 py-3 text-sm"><?= htmlspecialchars($item['unidad']) ?></td>
                  <td class="px-4 py-3 text-sm"><?= htmlspecialchars($item['umbral_minimo']) ?></td>
                  <td class="px-4 py-3 text-sm"><?= htmlspecialchars($item['proveedor']) ?></td>
                  <td class="px-4 py-3 text-sm"><?= htmlspecialchars($item['fecha_reposicion_fmt']) ?></td>
                  <td class="px-4 py-3 text-sm"><?= htmlspecialchars($item['desperdicio_cantidad']) ?></td>
                  <td class="px-4 py-3 text-sm"><?= htmlspecialchars($item['desperdicio_causa'] ?? '—') ?></td>
                  <td class="px-4 py-3 text-sm"><?= htmlspecialchars($item['usuario_reporta'] ?? '—') ?></td>
                  <td class="px-4 py-3 font-medium">
                    <?php if ($estado === 'Agotado'): ?>
                      <span class="text-red-600"><?= $estado ?></span>
                    <?php elseif ($estado === 'Bajo'): ?>
                      <span class="text-yellow-600"><?= $estado ?></span>
                    <?php else: ?>
                      <span class="text-green-600"><?= $estado ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 space-x-2">
                    <!-- Botón Editar en cada fila -->
                    <button
                      data-id="<?= $item['id'] ?>"
                      class="btn-edit px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm"
                    >
                      Editar
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Botones de Filtrar y Exportar CSV -->
        <div class="mt-6 flex flex-col sm:flex-row sm:justify-between sm:items-center">
          <div class="flex items-center space-x-4">
            <button id="btn-filtro-estados" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
              Filtrar Bajo/Agotado
            </button>
            <button id="btn-exportar" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
              Exportar CSV
            </button>
          </div>
          <div class="mt-4 sm:mt-0">
            <a href="ordenes-activas.php" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 text-sm">
              Volver a Cola de Producción
            </a>
          </div>
        </div>
      <?php endif; ?>
    </section>
  </main>

  <?php include '../components/footer.php'; ?>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      console.log('DOM listo: iniciando JS de reporte_inventario');
      // Registrar plugin ScrollTrigger
      gsap.registerPlugin(ScrollTrigger);

      // Animación opcional con ScrollTrigger
      gsap.utils.toArray('#reporte-inventario, #form-inventario').forEach(section => {
        gsap.fromTo(
          section,
          { opacity: 0, y: 20 },
          {
            opacity: 1,
            y: 0,
            duration: 0.8,
            scrollTrigger: { trigger: section, start: 'top 85%' }
          }
        );
      });

      // 1) Toggle Filtrar Bajo/Agotado
      let filtroActivo = false;
      const btnFiltro = document.getElementById('btn-filtro-estados');
      btnFiltro.addEventListener('click', () => {
        const filas = document.querySelectorAll('#tblInventario tbody tr');
        filtroActivo = !filtroActivo;
        if (filtroActivo) {
          btnFiltro.innerText = 'Mostrar Todos';
          filas.forEach(fila => {
            const estadoText = fila.querySelector('td:nth-child(10)').innerText.trim().toLowerCase();
            if (estadoText === 'ok') {
              fila.style.display = 'none';
            }
          });
        } else {
          btnFiltro.innerText = 'Filtrar Bajo/Agotado';
          filas.forEach(fila => {
            fila.style.display = '';
          });
        }
      });

      // 2) Exportar a CSV solo filas visibles
      document.getElementById('btn-exportar').addEventListener('click', () => {
        const rows = [];
        // Encabezados
        const encabezados = Array.from(
          document.querySelectorAll('#tblInventario thead th')
        ).map(th => th.innerText.trim());
        rows.push(encabezados.join(','));

        // Filas visibles
        document.querySelectorAll('#tblInventario tbody tr').forEach(tr => {
          if (tr.style.display === 'none') return;
          const cols = Array.from(tr.querySelectorAll('td')).map(td => td.innerText.trim());
          rows.push(cols.join(','));
        });

        // Construir y descargar CSV
        const csvContent = rows.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.setAttribute('href', url);
        a.setAttribute('download', 'reporte_inventario.csv');
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
      });

      // 3) Botones Editar por fila
      document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', e => {
          const id = e.currentTarget.getAttribute('data-id');
          window.location.href = `reporte_inventario.php?edit_id=${id}`;
        });
      });
    });
  </script>
</body>
</html>
