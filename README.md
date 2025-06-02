# Manager-Restaurante


## 📚 Tabla de Contenido

- [📖 Manual de Usuario](https://docs.google.com/document/d/1Dvp0cd15RI-we0PziBg0gDC31e2FcY2ddWNS92s2354/edit?usp=sharing)
- [🗄️ Documentación de la Base de Datos](https://docs.google.com/document/d/1RaU1gad9mQC69lfkX2WcBfGdqdKWW8h6MZqmoTfuP_c/edit?usp=sharing)
- [🧩 Casos de Uso](https://docs.google.com/document/d/14RPvU2MLxRvRc85gIQGYeqnKHrquCEvMgNIyAkO7rZ0/edit?usp=sharing)
- [🏗️ Diagramas de Clase](https://docs.google.com/document/d/1nzWHJZXHsxvmYPf9gPcbLclBxtiiT9-yu2-uRjaQ_Q4/edit?usp=sharing)
- [🎨 Diseño](https://docs.google.com/document/d/1LypPbvmPPfEW8P0qiIA8h6h5kIirQiP1zbga7PbogEY/edit?usp=sharing)
- [📝 Requerimientos](https://docs.google.com/document/d/1Izq0plk2Mm_UR2h0HG7V4HXcY02aYJFeaoTI6-Gcpy0/edit?usp=sharing)
- [🎓 Manual de Capacitación](https://docs.google.com/document/d/11TOHg2slYAQ8QR0EAwXxbaPaQJcxV30jZvy5qrI4V3s/edit?usp=sharing)

---

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
