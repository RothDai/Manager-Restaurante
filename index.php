<?php
// /index.php
session_start();
if (isset($_SESSION['id'])) {
    // Redirigir según rol
    switch ($_SESSION['rol']) {
        case 'gerente':
            header('Location: /gerente/dashboard.php');
            break;
        case 'mesero':
            header('Location: /mesero/ordenes.php');
            break;
        case 'cocina':
            header('Location: /cocina/ordenes-activas.php');
            break;
        default:
            header('Location: /login.php');
    }
    exit;
} else {
    header('Location: /login.php');
    exit;
}
