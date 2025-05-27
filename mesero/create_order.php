<?php
// /mesero/create_order.php

session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'mesero') {
    header('Location: /login.php');
    exit;
}

require '../config/db.php';

$errors    = [];
$exito     = false;

// Traer mesas libres (canal local)
$mesas = $pdo->query("
    SELECT id, numero
    FROM mesas
    WHERE estado = 'libre'
    ORDER BY numero ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Traer todos los platillos activos del menú
$menuItems = $pdo->query("
    SELECT id, nombre, precio_local, precio_entrega, precio_takeaway
    FROM menu
    WHERE activo = 1
    ORDER BY nombre ASC
")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Recoger datos del formulario
    $mesa_id    = isset($_POST['mesa_id']) && $_POST['mesa_id'] !== '' 
                  ? (int)$_POST['mesa_id'] 
                  : null;
    $canal      = $_POST['canal'] ?? '';
    $usuario_id = $_SESSION['id'];

    // Arreglos dinámicos de ítems:
    // - itemsSel: [0 => menu_id, 1 => menu_id, ...]
    // - itemsQty: [0 => cantidad, 1 => cantidad, ...]
    // - itemsMods: [0 => modificadorJSON, ...]
    // - itemsNotas: [0 => nota, ...]
    $itemsSel  = $_POST['itemsSel']  ?? [];
    $itemsQty  = $_POST['itemsQty']  ?? [];
    $itemsMods = $_POST['itemsMods'] ?? [];
    $itemsNotas= $_POST['itemsNotas']?? [];

    // 2) Validar canal
    if (!in_array($canal, ['local','entrega','takeaway'], true)) {
        $errors[] = "Debes seleccionar un canal válido.";
    }

    // 3) Preparar datos de cada ítem (solo los con menu_id != '' y cantidad ≥1)
    $detalles = [];
    $totalCalculado = 0.0;
    foreach ($itemsSel as $idx => $menu_id_raw) {
        $menu_id = (int)$menu_id_raw;
        $cantidad = isset($itemsQty[$idx]) ? (int)$itemsQty[$idx] : 0;
        if ($menu_id === 0 || $cantidad < 1) {
            continue;
        }

        // Buscar precios
        $stmtP = $pdo->prepare("
            SELECT precio_local, precio_entrega, precio_takeaway
            FROM menu
            WHERE id = ?
        ");
        $stmtP->execute([$menu_id]);
        $precios = $stmtP->fetch(PDO::FETCH_ASSOC);
        if (!$precios) {
            $errors[] = "Platillo con ID $menu_id no existe.";
            continue;
        }

        switch ($canal) {
            case 'local':
                $precioUnit = (float)$precios['precio_local'];
                break;
            case 'entrega':
                $precioUnit = (float)$precios['precio_entrega'];
                break;
            default:
                $precioUnit = (float)$precios['precio_takeaway'];
        }

        if ($precioUnit <= 0) {
            $errors[] = "El platillo ID $menu_id no tiene precio válido para canal '$canal'.";
            continue;
        }

        $mods = trim($itemsMods[$idx] ?? '');
        $mods = ($mods !== '') ? $mods : null;
        $nota = trim($itemsNotas[$idx] ?? '');

        $subtotal = $precioUnit * $cantidad;
        $totalCalculado += $subtotal;

        $detalles[] = [
            'menu_id'       => $menu_id,
            'cantidad'      => $cantidad,
            'modificadores' => $mods,
            'notas'         => $nota
        ];
    }

    if (empty($detalles)) {
        $errors[] = "Debes agregar al menos un ítem con cantidad ≥ 1.";
    }

    // 4) Insertar la orden si no hay errores
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // 4.1) Insertar encabezado en "ordenes"
            $stmtO = $pdo->prepare("
                INSERT INTO ordenes 
                  (mesa_id, usuario_id, canal, estado, total, fecha_hora_inicio)
                VALUES 
                  (?, ?, ?, 'pendiente', ?, NOW())
            ");
            $stmtO->execute([
                $mesa_id,
                $usuario_id,
                $canal,
                $totalCalculado
            ]);
            $nuevaOrdenId = $pdo->lastInsertId();

            // 4.2) Si canal = local y se eligió mesa, marcarla ocupada
            if ($canal === 'local' && $mesa_id !== null) {
                $pdo->prepare("
                    UPDATE mesas SET estado = 'ocupada' WHERE id = ?
                ")->execute([$mesa_id]);
            }

            // 4.3) Insertar cada línea en "detalle_orden"
            $stmtD = $pdo->prepare("
                INSERT INTO detalle_orden 
                  (orden_id, menu_id, cantidad, modificadores, notas)
                VALUES 
                  (?, ?, ?, ?, ?)
            ");
            foreach ($detalles as $d) {
                $stmtD->execute([
                    $nuevaOrdenId,
                    $d['menu_id'],
                    $d['cantidad'],
                    $d['modificadores'],
                    $d['notas']
                ]);
            }

            $pdo->commit();
            $exito = true;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Error al crear la orden: " . $e->getMessage();
        }
    }
}

// Para JavaScript: convertir $menuItems a JSON
$menuJSON = json_encode($menuItems);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Crear Orden - Mesero</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 pt-16">
  <?php include '../components/header.php'; ?>

  <main class="max-w-3xl mx-auto p-6">
    <section class="bg-white rounded-lg shadow p-6 animate-fade-in-up">
      <h2 class="text-2xl font-semibold mb-4">Crear Nueva Orden</h2>

      <?php if ($exito): ?>
        <div class="mb-4 px-4 py-2 bg-green-100 text-green-800 rounded">
          ¡Orden creada correctamente! 
          <a href="ordenes.php" class="underline">Ver listado de órdenes</a>.
        </div>
      <?php endif; ?>

      <?php if (!empty($errors)): ?>
        <ul class="mb-4 text-sm text-red-500 list-disc list-inside">
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <form method="POST" class="space-y-6" id="formOrden">
        <!-- Selección de canal -->
        <div>
          <label for="canal" class="block text-sm font-medium text-gray-700">Canal de la Orden</label>
          <select
            id="canal"
            name="canal"
            required
            onchange="toggleMesaSelection(this.value)"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Selecciona canal...</option>
            <option value="local"   <?= (($_POST['canal'] ?? '') === 'local') ? 'selected' : '' ?>>Local</option>
            <option value="entrega" <?= (($_POST['canal'] ?? '') === 'entrega') ? 'selected' : '' ?>>Entrega</option>
            <option value="takeaway"<?= (($_POST['canal'] ?? '') === 'takeaway') ? 'selected' : '' ?>>Takeaway</option>
          </select>
        </div>

        <!-- Selección de mesa (solo si canal = local) -->
        <div id="campo-mesa" class="<?= (($_POST['canal'] ?? '') === 'local') ? '' : 'hidden' ?>">
          <label for="mesa_id" class="block text-sm font-medium text-gray-700">Mesa (solo si canal Local)</label>
          <select
            id="mesa_id"
            name="mesa_id"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Selecciona mesa...</option>
            <?php foreach ($mesas as $m): ?>
              <option value="<?= $m['id'] ?>" <?= ((int)($_POST['mesa_id'] ?? -1) === $m['id']) ? 'selected' : '' ?>>
                Mesa <?= htmlspecialchars($m['numero']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Sección de ítems dinámicos -->
        <div>
          <h3 class="text-lg font-medium mb-2">Ítems de la Orden</h3>
          <table class="min-w-full divide-y divide-gray-200 mb-4">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-3 py-2 text-left text-sm font-medium text-gray-700">Platillo</th>
                <th class="px-3 py-2 text-left text-sm font-medium text-gray-700">Cantidad</th>
                <th class="px-3 py-2 text-left text-sm font-medium text-gray-700">Modificadores (JSON)</th>
                <th class="px-3 py-2 text-left text-sm font-medium text-gray-700">Notas</th>
                <th class="px-3 py-2 text-center text-sm font-medium text-gray-700">Eliminar</th>
              </tr>
            </thead>
            <tbody id="cuerpo-items" class="bg-white divide-y divide-gray-200">
              <!-- Aquí se agregarán filas dinámicamente -->
            </tbody>
          </table>
          <button
            type="button"
            onclick="agregarFila()"
            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
          >
            + Agregar Ítem
          </button>
        </div>

        <!-- Botones generales -->
        <div class="flex justify-between mt-6">
          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
          >
            Crear Orden
          </button>
          <a
            href="ordenes.php"
            class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400"
          >
            Cancelar
          </a>
        </div>
      </form>
    </section>
  </main>

  <?php include '../components/footer.php'; ?>

  <script>
    // Array de platillos (id + nombre) para generar select dinámico
    const menuItems = <?= $menuJSON ?>;

    // Función para mostrar/ocultar campo de mesa según canal
    function toggleMesaSelection(canal) {
      const campoMesa = document.getElementById('campo-mesa');
      if (canal === 'local') {
        campoMesa.classList.remove('hidden');
      } else {
        campoMesa.classList.add('hidden');
        document.getElementById('mesa_id').value = '';
      }
    }

    // Función para agregar una nueva fila de ítem
    function agregarFila() {
      const cuerpo = document.getElementById('cuerpo-items');
      const idx = cuerpo.children.length;

      // Crear elemento <tr>
      const tr = document.createElement('tr');
      tr.classList.add('hover:bg-gray-50');

      // Celda de Platillo (select)
      const tdPlat = document.createElement('td');
      tdPlat.classList.add('px-3','py-2','text-sm');
      const selectPlat = document.createElement('select');
      selectPlat.setAttribute('name', `itemsSel[${idx}]`);
      selectPlat.required = true;
      selectPlat.className = 'block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm';
      let optDef = document.createElement('option');
      optDef.value = '';
      optDef.text = 'Selecciona platillo...';
      selectPlat.appendChild(optDef);
      menuItems.forEach(item => {
        let opt = document.createElement('option');
        opt.value = item.id;
        opt.text = item.nombre;
        selectPlat.appendChild(opt);
      });
      tdPlat.appendChild(selectPlat);
      tr.appendChild(tdPlat);

      // Celda de Cantidad
      const tdCant = document.createElement('td');
      tdCant.classList.add('px-3','py-2','text-sm');
      const inputCant = document.createElement('input');
      inputCant.setAttribute('type', 'number');
      inputCant.setAttribute('name', `itemsQty[${idx}]`);
      inputCant.setAttribute('min', '1');
      inputCant.value = '1';
      inputCant.required = true;
      inputCant.className = 'w-16 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm';
      tdCant.appendChild(inputCant);
      tr.appendChild(tdCant);

      // Celda de Modificadores
      const tdMods = document.createElement('td');
      tdMods.classList.add('px-3','py-2','text-sm');
      const inputMods = document.createElement('input');
      inputMods.setAttribute('type', 'text');
      inputMods.setAttribute('name', `itemsMods[${idx}]`);
      inputMods.placeholder = '{"sin_sal":true}';
      inputMods.className = 'block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm';
      tdMods.appendChild(inputMods);
      tr.appendChild(tdMods);

      // Celda de Notas
      const tdNotas = document.createElement('td');
      tdNotas.classList.add('px-3','py-2','text-sm');
      const inputNotas = document.createElement('input');
      inputNotas.setAttribute('type', 'text');
      inputNotas.setAttribute('name', `itemsNotas[${idx}]`);
      inputNotas.className = 'block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm';
      tdNotas.appendChild(inputNotas);
      tr.appendChild(tdNotas);

      // Celda de Eliminar
      const tdDel = document.createElement('td');
      tdDel.classList.add('px-3','py-2','text-center');
      const btnDel = document.createElement('button');
      btnDel.setAttribute('type', 'button');
      btnDel.innerText = '🗑️';
      btnDel.title = 'Eliminar ítem';
      btnDel.className = 'text-red-600 hover:text-red-800';
      btnDel.onclick = () => tr.remove();
      tdDel.appendChild(btnDel);
      tr.appendChild(tdDel);

      cuerpo.appendChild(tr);
    }

    // Al cargar, agregar una fila inicial
    document.addEventListener('DOMContentLoaded', () => {
      agregarFila();
      const canalSel = document.getElementById('canal');
      if (canalSel) toggleMesaSelection(canalSel.value);
    });
  </script>
</body>
</html>
