# 🎓 Gestión Académica — IES Hermenegildo Lanz

Sistema de gestión académica para el **IES P. Hermenegildo Lanz** de Granada.

<div align="center">

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php)
![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql)
![License](https://img.shields.io/badge/License-MIT-3F51B5?style=for-the-badge)

</div>

---

## 📋 Descripción

Aplicación web para la gestión académica del centro, desarrollada con **Laravel 10** y siguiendo las mejores prácticas de desarrollo moderno.

## 🛠️ Tecnologías

- **Backend:** PHP 8.x, Laravel 10
- **Frontend:** Blade Templates, JavaScript, CSS
- **Base de datos:** MySQL
- **Autenticación:** Laravel Breeze / Jetstream
- **Servidor:** Apache (puerto 9000)

## ✨ Funcionalidades

- 📚 Gestión de alumnos y clases
- 👨‍🏫 Administración de profesores
- 📊 Control de notas y evaluaciones
- 📅 Calendario académico
- 📈 Informes y estadísticas

## 📦 Instalación

```bash
# Clonar el repositorio
git clone https://github.com/nachocabrero/gestion-dual.git
cd gestion-dual

# Instalar dependencias
composer install
npm install && npm run build

# Configurar la base de datos
cp .env.example .env
php artisan key:generate

# Ejecutar migraciones
php artisan migrate

# Iniciar el servidor
php artisan serve
```

## 📁 Estructura del Proyecto

```
gestion-dual/
├── app/
│   ├── Http/
│   ├── Models/
│   └── Providers/
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/
│   └── js/
├── routes/
└── tests/
```

## 📄 Licencia

Este proyecto es de uso interno del IES P. Hermenegildo Lanz.

---

<div align="center">

Hecho con ❤️ por **Nacho Cabrero** · Granada, España

</div>
