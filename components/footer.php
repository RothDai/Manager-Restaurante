<?php
// /components/footer.php
?>
<footer class="mt-12 py-4 bg-white bg-opacity-60 backdrop-blur-md shadow-inner text-center text-sm text-gray-600">
  <div>
    Usuario: <span class="font-medium"><?= htmlspecialchars($_SESSION['nombre']) ?></span> |
    Rol: <span class="font-medium"><?= htmlspecialchars($_SESSION['rol']) ?></span>
  </div>
  <div class="mt-1">
    &copy; <?= date('Y') ?> RestAI. Todos los derechos reservados.
  </div>
</footer>
