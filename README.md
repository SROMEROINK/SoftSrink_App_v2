# 🛠️ SoftSrink App v2

Aplicación desarrollada con Laravel 11 para la gestión de producción, control de calidad, mantenimiento, compras y movimientos de materia prima/herramental.

## 🚀 Funcionalidades principales

- CRUD completo de órdenes de fabricación
- Gestión de materias primas e insumos
- Registro de producción con control de turnos y operarios
- Vistas `index`, `show`, `edit`, `create` para todas las áreas
- Filtros personalizados por fecha, máquina y revisión de plano
- Integración con Laravel Breeze + Spatie Permissions
- Plantilla AdminLTE 3
- Alertas con SweetAlert2

## 📦 Requisitos

- PHP 8.4
- Composer
- MySQL
- Laravel 11
- Node.js y npm (para assets frontend, si usás Vite)

## 🛠️ Instalación

```bash
git clone https://github.com/SROMEROINK/SoftSrink_App_v2.git
cd SoftSrink_App_v2
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve

📁 Estructura del proyecto
pgsql
Copiar
Editar
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
├── tests/
└── .env.example

🔐 Autenticación y roles
Este sistema usa Laravel Breeze y Spatie Permission para gestionar accesos por área:

Producción

Calidad

Mantenimiento

Compras

Materia Prima

Herramental

🧪 Testing
bash
Copiar
Editar
php artisan test

📌 Notas Técnicas – Publicación de un Release en GitHub
🧪 ¿Cuándo crear un Release?
Hacer un Release sirve para marcar un hito estable del proyecto que puede ser descargado, clonado o compartido como una versión funcional.

✅ Crear un release cuando:

 Finalizaste una funcionalidad completa (ej.: CRUD, módulo de calidad, módulo de stock).

 Ya no vas a hacer cambios importantes por unos días o semanas.

 Querés subir una versión estable al servidor.

 Alguien más necesita trabajar sobre tu proyecto (ej.: frontend, testing, cliente).

 Querés dejar un punto de restauración claro (ej.: v1.0, v1.1).

 ⚠️ Este repositorio no incluye datos reales. La base de datos fue omitida por seguridad.

 👨‍💻 Autor
Desarrollado por Sergio Daniel Romero
📧 sistemas_automatizados@sromeroink.com
🔗 GitHub



