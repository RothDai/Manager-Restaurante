# Manager-Restaurante

Un sistema web para la gestión integral de un restaurante/bar, construido en PHP y MySQL, con roles diferenciados (gerente, mesero, cocina) y una interfaz moderna basada en Tailwind CSS y animaciones con GSAP y Chart.js.
📂 Estructura del Proyecto
Manager-Restaurante/
├── components/
│   ├── header.php
│   └── footer.php
├── config/
│   └── db.php
├── gerente/
│   ├── dashboard.php
│   ├── menu.php
│   ├── create_menu.php
│   └── edit_menu.php
├── mesero/
│   ├── ordenes.php
│   ├── create_order.php
│   ├── edit_order.php
├── cocina/
│   ├── ordenes-activas.php
│   ├── update_order_status.php
├── assets/
│   ├── css/
│   │   └── tailwind.css
│   └── js/
│       ├── main.js
│       ├── mesero.js
│       ├── gerente.js
│       └── cocina.js
├── index.php
├── login.php
├── logout.php
├── register.php
├── README.md
└── restaurante_db.sql
🚀 Instalación y Configuración

    Clona el repositorio dentro de la carpeta de tu servidor local:

cd C:\xampp\htdocs
git clone https://github.com/RothDai/Manager-Restaurante.git

    Importa la base de datos usando phpMyAdmin o tu herramienta favorita. Usa restaurante_db.sql.

    Configura config/db.php según tus credenciales:

$host = '127.0.0.1';
$db = 'restaurante_db';
$user = 'root';
$pass = '';

    Abre http://localhost/Manager-Restaurante/login.php en tu navegador.

🔑 Usuarios de Prueba
Nombre	Email	Rol	Contraseña
Maria Lopez	maria@restai.com	gerente	Pulsar123
Luis Hernandez	luis@restai.com	mesero	Mesa2025
Pedro Morales	pedro@restai.com	cocina	Cocina#1

Usa el formulario de register.php para crear nuevos usuarios.
📋 Funcionalidades Principales

    Autenticación de usuarios por rol (gerente, mesero, cocina).

    Gerente: dashboard de ventas, gestión de empleados e inventario, edición de menú.

    Mesero: registro y edición de órdenes.

    Cocina: cola de producción y actualización de estados.

🛡️ Tecnologías Usadas

    PHP 8.x
    MySQL
    Tailwind CSS
    GSAP
    Chart.js
    JavaScript Vanilla

📦 .gitignore sugerido
node_modules/
.env
config/db.php
*.log
.DS_Store
.idea/
.vscode/
📫 Contribuciones

Bienvenido a contribuir: haz un fork, crea una rama, realiza cambios y envía un Pull Request.
📄 Licencia

Este proyecto está bajo la licencia MIT.
