<!-- /gerente/inventario-avanzado.php -->
<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'gerente') {
    header('Location: /login.php');
    exit;
}

include '../config/db.php';

$errors = [];
$success = '';

// Procesamiento de formulario: crear, editar o eliminar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action               = $_POST['action'] ?? '';

    // Campos comunes
    $nombre               = trim($_POST['nombre_ingrediente'] ?? '');
    $cantidad             = $_POST['cantidad'] ?? '';
    $unidad               = trim($_POST['unidad'] ?? '');
    $umbral               = $_POST['umbral_minimo'] ?? '';
    $proveedor            = trim($_POST['proveedor'] ?? '');
    $fecha_reposicion     = $_POST['fecha_ultima_reposicion'] ?? '';
    $desperdicio_cantidad = $_POST['desperdicio_cantidad'] ?? '';
    $desperdicio_causa    = trim($_POST['desperdicio_causa'] ?? '');
    $usuario_reporta_id   = $_SESSION['id']; // Siempre el gerente que reporta

    if ($action === 'delete' && isset($_POST['id'])) {
        // Eliminar registro
        $idToDelete = intval($_POST['id']);
        $stmtDel = $pdo->prepare("DELETE FROM inventario WHERE id = ?");
        $stmtDel->execute([$idToDelete]);
        $success = "Ingrediente eliminado satisfactoriamente.";
    } else {
        // VALIDACIONES COMUNES (crear / editar)

        // Nombre
        if ($nombre === '') {
            $errors[] = 'El nombre del ingrediente es obligatorio.';
        } elseif (mb_strlen($nombre) > 100) {
            $errors[] = 'El nombre no puede tener más de 100 caracteres.';
        }

        // Unidad
        if ($unidad === '') {
            $errors[] = 'La unidad es obligatoria.';
        } elseif (mb_strlen($unidad) > 10) {
            $errors[] = 'La unidad no puede tener más de 10 caracteres.';
        }

        // Cantidad
        if ($cantidad === '') {
            $errors[] = 'La cantidad es obligatoria.';
        } elseif (!is_numeric($cantidad) || $cantidad < 0) {
            $errors[] = 'La cantidad debe ser un número mayor o igual a 0.';
        }

        // Umbral mínimo
        if ($umbral === '') {
            $errors[] = 'El umbral mínimo es obligatorio.';
        } elseif (!is_numeric($umbral) || $umbral < 0) {
            $errors[] = 'El umbral mínimo debe ser un número mayor o igual a 0.';
        }

        // Proveedor
        if ($proveedor === '') {
            $errors[] = 'El proveedor es obligatorio.';
        } elseif (mb_strlen($proveedor) > 100) {
            $errors[] = 'El proveedor no puede tener más de 100 caracteres.';
        }

        // Fecha de última reposición
        if ($fecha_reposicion === '') {
            $errors[] = 'La fecha de última reposición es obligatoria.';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_reposicion)) {
            $errors[] = 'El formato de fecha debe ser YYYY-MM-DD.';
        } else {
            $d = DateTime::createFromFormat('Y-m-d', $fecha_reposicion);
            if (!$d || $d->format('Y-m-d') !== $fecha_reposicion) {
                $errors[] = 'La fecha de última reposición no es válida.';
            }
        }

        // Desperdicio (cantidad)
        if ($desperdicio_cantidad === '') {
            $errors[] = 'La cantidad de desperdicio es obligatoria.';
        } elseif (!is_numeric($desperdicio_cantidad) || $desperdicio_cantidad < 0) {
            $errors[] = 'La cantidad de desperdicio debe ser un número mayor o igual a 0.';
        }

        // Desperdicio (causa) – opcional, máximo 200 caracteres
        if ($desperdicio_causa !== '' && mb_strlen($desperdicio_causa) > 200) {
            $errors[] = 'La causa de desperdicio no puede tener más de 200 caracteres.';
        }

        // Si no hay errores, procedemos según acción
        if ($action === 'create' && empty($errors)) {
            $stmtIns = $pdo->prepare("
              INSERT INTO inventario 
                (nombre_ingrediente, cantidad, unidad, umbral_minimo, proveedor, 
                 fecha_ultima_reposicion, desperdicio_cantidad, desperdicio_causa, usuario_reporta_id)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtIns->execute([
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
            $success = "Ingrediente agregado satisfactoriamente.";
        }

        if ($action === 'edit' && isset($_POST['id']) && empty($errors)) {
            $idEdit = intval($_POST['id']);
            $stmtUpd = $pdo->prepare("
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
            $stmtUpd->execute([
                $nombre,
                $cantidad,
                $unidad,
                $umbral,
                $proveedor,
                $fecha_reposicion,
                $desperdicio_cantidad,
                $desperdicio_causa === '' ? null : $desperdicio_causa,
                $usuario_reporta_id,
                $idEdit
            ]);
            $success = "Ingrediente actualizado satisfactoriamente.";
        }
    }
}

// Recuperar todos los registros
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

// Si se solicita editar, cargar datos
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
  <title>Inventario Avanzado - Gerente</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- GSAP y ScrollTrigger -->
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.2/dist/gsap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.2/dist/ScrollTrigger.min.js"></script>
</head>
<body class="bg-gray-100 text-gray-800 pt-16">
  <?php include '../components/header.php'; ?>

  <main class="max-w-7xl mx-auto p-6 space-y-8">
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

    <!-- Formulario para agregar o editar ingredientes -->
    <section id="form-inventario" class="bg-white rounded-lg shadow p-6 animate-fade-in-up">
      <h2 class="text-2xl font-semibold mb-4"><?= $editItem ? 'Editar Ingrediente' : 'Agregar Nuevo Ingrediente' ?></h2>
      <form method="POST" class="space-y-4">
        <input type="hidden" name="action" value="<?= $editItem ? 'edit' : 'create' ?>">
        <?php if ($editItem): ?>
          <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
        <?php endif; ?>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre del Ingrediente</label>
            <input
              id="nombre"
              name="nombre_ingrediente"
              type="text"
              value="<?= htmlspecialchars($editItem['nombre_ingrediente'] ?? '') ?>"
              required
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
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
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
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
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
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
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div>
            <label for="proveedor" class="block text-sm font-medium text-gray-700">Proveedor</label>
            <input
              id="proveedor"
              name="proveedor"
              type="text"
              value="<?= htmlspecialchars($editItem['proveedor'] ?? '') ?>"
              required
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div>
            <label for="fecha_reposicion" class="block text-sm font-medium text-gray-700">Fecha Última Reposición</label>
            <input
              id="fecha_reposicion"
              name="fecha_ultima_reposicion"
              type="date"
              value="<?= htmlspecialchars($editItem['fecha_ultima_reposicion'] ?? '') ?>"
              required
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div>
            <label for="desperdicio_causa" class="block text-sm font-medium text-gray-700">Causa de Desperdicio (opcional)</label>
            <textarea
              id="desperdicio_causa"
              name="desperdicio_causa"
              rows="2"
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
            ><?= htmlspecialchars($editItem['desperdicio_causa'] ?? '') ?></textarea>
          </div>
        </div>

        <div class="flex justify-end space-x-4 pt-4">
          <?php if ($editItem): ?>
            <a href="inventario-avanzado.php" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
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

    <!-- Buscador y acciones globales -->
    <section class="bg-white rounded-lg shadow p-6 animate-fade-in-up">
      <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
        <input
          id="buscador"
          type="text"
          placeholder="Buscar ingrediente..."
          class="w-full sm:w-1/3 mb-2 sm:mb-0 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
        />
        <div class="flex space-x-2">
          <button
            id="btn-filtro-estados"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm"
          >
            Filtrar Bajo/Agotado
          </button>
          <button
            id="btn-exportar"
            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm"
          >
            Exportar CSV
          </button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table id="tblInvAvanzado" class="min-w-full divide-y divide-gray-200">
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
                // Determinar estado y clase de fila
                $estado   = 'OK';
                $fila_cls = '';
                if ($item['cantidad'] <= 0) {
                  $estado   = 'Agotado';
                  $fila_cls = 'bg-red-100';
                } elseif ($item['cantidad'] <= $item['umbral_minimo']) {
                  $estado   = 'Bajo';
                  $fila_cls = 'bg-yellow-100';
                }
              ?>
              <tr class="<?= $fila_cls ?>">
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
                  <!-- Botón Eliminar en cada fila -->
                  <form method="POST" class="inline">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                    <button
                      type="submit"
                      class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-sm"
                      onclick="return confirm('¿Estás seguro de eliminar este ingrediente?');"
                    >
                      Eliminar
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <?php include '../components/footer.php'; ?>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      console.log('DOM listo: iniciando JS de inventario-avanzado');

      gsap.registerPlugin(ScrollTrigger);
      gsap.utils.toArray('#form-inventario, #buscador').forEach(section => {
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

      // 1) Search/Buscador en tiempo real
      const buscador = document.getElementById('buscador');
      buscador.addEventListener('input', () => {
        const texto = buscador.value.trim().toLowerCase();
        document.querySelectorAll('#tblInvAvanzado tbody tr').forEach(tr => {
          const nombre = tr.querySelector('td:nth-child(1)').innerText.trim().toLowerCase();
          tr.style.display = (nombre.includes(texto) ? '' : 'none');
        });
      });

      // 2) Toggle Filtrar Bajo/Agotado
      let filtroActivo = false;
      const btnFiltro = document.getElementById('btn-filtro-estados');
      btnFiltro.addEventListener('click', () => {
        const filas = document.querySelectorAll('#tblInvAvanzado tbody tr');
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

      // 3) Exportar a CSV (sólo filas visibles)
      document.getElementById('btn-exportar').addEventListener('click', () => {
        const rows = [];
        // Encabezados
        const encabezados = Array.from(
          document.querySelectorAll('#tblInvAvanzado thead th')
        ).map(th => th.innerText.trim());
        rows.push(encabezados.join(','));

        // Filas visibles
        document.querySelectorAll('#tblInvAvanzado tbody tr').forEach(tr => {
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
        a.setAttribute('download', 'inventario_avanzado.csv');
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
      });

      // 4) Botones Editar por fila
      document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', e => {
          const id = e.currentTarget.getAttribute('data-id');
          window.location.href = `inventario-avanzado.php?edit_id=${id}`;
        });
      });
    });
  </script>
</body>
</html>
