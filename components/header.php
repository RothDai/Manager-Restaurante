<?php
// /components/header.php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['id'])) {
    header('Location: /login.php');
    exit;
}

$rol = $_SESSION['rol'];
?>
<header class="fixed top-0 left-0 w-full bg-white bg-opacity-80 backdrop-blur-md shadow-sm z-50">
  <div class="max-w-7xl mx-auto flex items-center justify-between px-4 py-3">
    <!-- Logo -->
    <div class="text-2xl font-bold text-blue-600">
      <a href="/index.php">RestAI</a>
    </div>

    <!-- Menú de escritorio -->
    <nav class="hidden md:flex space-x-6">
      <?php if ($rol === 'gerente'): ?>
        <a href="/gerente/dashboard.php#ventas-tiempo-real" class="text-gray-700 hover:text-blue-600">Métricas</a>
        <a href="/gerente/dashboard.php#gestion-personal" class="text-gray-700 hover:text-blue-600">Personal</a>
        <a href="/gerente/dashboard.php#inventario-avanzado" class="text-gray-700 hover:text-blue-600">Inventario</a>
        <a href="/gerente/dashboard.php#analisis-rese%C3%B1as" class="text-gray-700 hover:text-blue-600">Reseñas</a>
        <a href="/gerente/menu.php" class="text-gray-700 hover:text-blue-600">Menú</a>
      <?php elseif ($rol === 'mesero'): ?>
        <a href="/mesero/ordenes.php" class="text-gray-700 hover:text-blue-600">Órdenes</a>
        <a href="/mesero/mesas.php" class="text-gray-700 hover:text-blue-600">Mesas</a>
        <a href="/mesero/lealtad.php" class="text-gray-700 hover:text-blue-600">Lealtad</a>
      <?php elseif ($rol === 'cocina'): ?>
        <a href="/cocina/ordenes-activas.php" class="text-gray-700 hover:text-blue-600">Cola Producción</a>
        <a href="/cocina/control-calidad.php" class="text-gray-700 hover:text-blue-600">Calidad</a>
        <a href="/cocina/reposición.php" class="text-gray-700 hover:text-blue-600">Reposición</a>
      <?php endif; ?>
      <a href="/logout.php" class="text-red-500 hover:text-red-700">Cerrar Sesión</a>
    </nav>

    <!-- Botón menú móvil -->
    <button id="menu-toggle" class="md:hidden flex items-center justify-center p-2 rounded-md text-gray-700 hover:bg-gray-100 focus:outline-none">
      <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path id="menu-open" class="block" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16" />
        <path id="menu-close" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
  </div>

  <!-- Menú móvil -->
  <nav id="mobile-menu" class="hidden md:hidden bg-white bg-opacity-90 backdrop-blur-md">
    <div class="px-4 pt-2 pb-4 space-y-1">
      <?php if ($rol === 'gerente'): ?>
        <a href="/gerente/dashboard.php#ventas-tiempo-real" class="block px-3 py-2 text-gray-700 rounded hover:bg-gray-100">Métricas</a>
        <a href="/gerente/dashboard.php#gestion-personal" class="block px-3 py-2 text-gray-700 rounded hover:bg-gray-100">Personal</a>
        <a href="/gerente/dashboard.php#inventario-avanzado" class="block px-3 py-2 text-gray-700 rounded hover:bg-gray-100">Inventario</a>
        <a href="/gerente/dashboard.php#analisis-rese%C3%B1as" class="block px-3 py-2 text-gray-700 rounded hover:bg-gray-100">Reseñas</a>
        <a href="/gerente/menu.php" class="block px-3 py-2 text-gray-700 rounded hover:bg-gray-100">Menú</a>
      <?php elseif ($rol === 'mesero'): ?>
        <a href="/mesero/ordenes.php" class="block px-3 py-2 text-gray-700 rounded hover:bg-gray-100">Órdenes</a>
        <a href="/mesero/mesas.php" class="block px-3 py-2 text-gray-700 rounded hover:bg-gray-100">Mesas</a>
        <a href="/mesero/lealtad.php" class="block px-3 py-2 text-gray-700 rounded hover:bg-gray-100">Lealtad</a>
      <?php elseif ($rol === 'cocina'): ?>
        <a href="/cocina/ordenes-activas.php" class="block px-3 py-2 text-gray-700 rounded hover:bg-gray-100">Cola Producción</a>
        <a href="/cocina/control-calidad.php" class="block px-3 py-2 text-gray-700 rounded hover:bg-gray-100">Calidad</a>
        <a href="/cocina/reposición.php" class="block px-3 py-2 text-gray-700 rounded hover:bg-gray-100">Reposición</a>
      <?php endif; ?>
      <a href="/logout.php" class="block px-3 py-2 text-red-500 rounded hover:bg-gray-100">Cerrar Sesión</a>
    </div>
  </nav>

  <script>
    document.getElementById('menu-toggle').addEventListener('click', function () {
      const menu = document.getElementById('mobile-menu');
      const openIcon = document.getElementById('menu-open');
      const closeIcon = document.getElementById('menu-close');
      menu.classList.toggle('hidden');
      openIcon.classList.toggle('hidden');
      closeIcon.classList.toggle('hidden');
    });
  </script>
</header>
