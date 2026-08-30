#!/bin/bash
# =============================================================================
# Despliegue de IESH Lanz (Docker)
#
# ⚠️  NO DESTRUCTIVO PARA LA BASE DE DATOS
# - La BD vive en el volumen Docker 'ieshlanz_mysql_data' y persiste entre
#   despliegues (docker compose up/down, --build). NO usar nunca 'down -v'.
# - Los despliegues solo ejecutan migraciones ADITIVAS:  php artisan migrate
# - Los seeders solo se ejecutan si la tabla de usuarios está VACÍA.
# =============================================================================
set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$PROJECT_DIR"

COMPOSE="docker compose"
CONTAINER=ieshlanz-app

echo "🚀 Desplegando IESH Lanz en Docker (aislado del resto de apps)"

# 0. Asegurar .env
if [ ! -f .env ]; then
    echo "ℹ️  Creando .env desde .env.example (ajusta luego las credenciales)"
    cp .env.example .env
fi

# 1. Dependencias PHP (host) — usadas por el bind-mount
if [ ! -d vendor ]; then
    echo "📦 composer install (host)..."
    composer install --no-dev --optimize-autoloader
else
    echo "✅ vendor ya existe (omitido)"
fi

# 2. Assets frontend (host) — public/build lo sirve nginx
if [ ! -d public/build ]; then
    echo "🎨 npm ci + build (host)..."
    npm ci
    npm run build
else
    echo "✅ public/build ya existe (omitido)"
fi

# 3. Permisos para que el contenedor (uid 1003) pueda escribir
echo "🔐 Permisos sobre storage/ y bootstrap/cache/"
chown -R 1003:1004 storage bootstrap/cache

# 4. Construir y levantar (db NO se reconstruye ni borra)
echo "🐳 docker compose up --build..."
$COMPOSE build app
$COMPOSE up -d

# 5. Esperar a que la BD esté lista (healthcheck del contenedor, sin credenciales)
echo "⏳ Esperando a que MySQL esté saludable..."
for i in $(seq 1 60); do
    STATUS="$(docker inspect -f '{{.State.Health.Status}}' ieshlanz-db 2>/dev/null || true)"
    if [ "$STATUS" = "healthy" ]; then
        break
    fi
    sleep 2
    if [ "$i" -eq 60 ]; then echo "❌ BD no responde (status=${STATUS:-no_container})"; exit 1; fi
done
echo "✅ MySQL listo"

# 6. Migraciones aditivas (NUNCA migrate:fresh)
echo "📊 Migraciones..."
$COMPOSE exec -T app php artisan migrate --force

# 7. Seed solo si la BD está vacía (evita duplicados en re-despliegues)
USER_COUNT="$($COMPOSE exec -T app php artisan tinker --execute='echo App\\Models\\User::count();' 2>/dev/null | tr -dc '0-9' || echo 'x')"
if [ "$USER_COUNT" = "0" ]; then
    echo "🌱 BD vacía: ejecutando seeders iniciales..."
    $COMPOSE exec -T app php artisan db:seed --class=AcademicStructureSeeder --force
    $COMPOSE exec -T app php artisan db:seed --class=IesDataSeeder --force
    $COMPOSE exec -T app php artisan db:seed --force
else
    echo "✅ BD con datos ($USER_COUNT usuarios): seeds omitidos"
fi

# 8. Cache de config/rutas/vistas de producción
$COMPOSE exec -T app php artisan config:cache --no-interaction
$COMPOSE exec -T app php artisan route:cache --no-interaction || true
$COMPOSE exec -T app php artisan view:cache --no-interaction || true

echo ""
echo "🎉 Despliegue completado."
echo "   App:      http://192.168.1.167:8093"
echo "   Dominio:  https://ieshlanz.vertigoapps.com (via NPM)"
echo ""
echo "⚠️  Para un re-despliegue de código sólo hace falta la línea:"
echo "   $COMPOSE up -d --build && $COMPOSE exec -T app php artisan migrate --force"
echo "   (la BD no se toca: volumen ieshlanz_mysql_data)"