<?php
// /gerente/personal.php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'gerente') {
    header('Location: /login.php');
    exit;
}
header('Location: /gerente/dashboard.php#gestion-personal');
exit;
