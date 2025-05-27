<?php
// /gerente/ventas.php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'gerente') {
    header('Location: /login.php');
    exit;
}
// Redirigir a sección correspondiente del dashboard
header('Location: /gerente/dashboard.php#ventas-tiempo-real');
exit;
