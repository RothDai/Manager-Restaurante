<?php
// /gerente/inventario-avanzado.php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'gerente') {
    header('Location: /login.php');
    exit;
}
header('Location: /gerente/dashboard.php#inventario-avanzado');
exit;
