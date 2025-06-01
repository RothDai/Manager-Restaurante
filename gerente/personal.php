<?php
// /gerente/personal.php

session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'gerente') {
    header('Location: /login.php');
    exit;
}

require '../config/db.php';

// Obtener lista de empleados
$stmtEmpleados = $pdo->query("
    SELECT id, nombre, rol, fecha_registro
    FROM usuarios
    WHERE rol != 'mantenimiento'
    ORDER BY fecha_registro DESC
");
$empleados = $stmtEmpleados->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Personal - Gerente</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 pt-16">
  <?php include '../components/header.php'; ?>

  <main class="max-w-5xl mx-auto p-6">
    <h1 class="text-3xl font-semibold mb-6">Gestión de Personal</h1>
    <div class="bg-white rounded-lg shadow p-6">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-gray-50 text-gray-600">
            <tr>
              <th class="px-4 py-2 text-left">Nombre</th>
              <th class="px-4 py-2 text-left">Rol</th>
              <th class="px-4 py-2 text-left">Fecha Registro</th>
              <th class="px-4 py-2 text-left">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($empleados)): ?>
              <tr>
                <td colspan="4" class="px-4 py-4 text-center text-gray-500">No hay empleados registrados.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($empleados as $e): ?>
                <tr class="hover:bg-gray-50">
                  <td class="px-4 py-3"><?= htmlspecialchars($e['nombre']) ?></td>
                  <td class="px-4 py-3"><?= ucfirst($e['rol']) ?></td>
                  <td class="px-4 py-3"><?= htmlspecialchars($e['fecha_registro']) ?></td>
                  <td class="px-4 py-3">
                    <a href="/gerente/edit_employee.php?id=<?= $e['id'] ?>"
                       class="text-indigo-600 hover:underline">Editar</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</body>
</html>
