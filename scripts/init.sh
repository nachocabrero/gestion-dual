#!/bin/bash
# Validación inicial del proyecto IESH Lanz
# Debe pasar antes de cualquier implementación

set -e

PROJECT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
ERRORS=0

echo "🔍 Validando proyecto IESH Lanz..."
echo ""

# 1. Verificar estructura de directorios (SDD harness)
echo "📁 Verificando estructura SDD..."

REQUIRED_DIRS=("agents" "progress" "scripts")
for dir in "${REQUIRED_DIRS[@]}"; do
    if [ ! -d "$PROJECT_DIR/$dir" ]; then
        echo "  ❌ Falta directorio: $dir"
        ERRORS=$((ERRORS + 1))
    else
        echo "  ✅ $dir"
    fi
done

# Verificar estructura Laravel (si existe)
if [ -d "$PROJECT_DIR/src" ]; then
    echo ""
    echo "📁 Verificando estructura Laravel..."
    REQUIRED_DIRS=("src/app/Models" "src/app/Http/Controllers" "src/database/migrations" "src/routes" "src/resources/views" "src/public")
    for dir in "${REQUIRED_DIRS[@]}"; do
        if [ ! -d "$PROJECT_DIR/$dir" ]; then
            echo "  ⚠️  Pendiente: $dir"
        else
            echo "  ✅ $dir"
        fi
    done
fi

# 2. Verificar archivos obligatorios
echo ""
echo "📄 Verificando archivos obligatorios..."

REQUIRED_FILES=("SPEC.md" "AGENTS.md" "features.json" "scripts/init.sh" "progress/README.md" "progress/current.json" "progress/history.md" "agents/implementer.md" "agents/reviewer.md")
for file in "${REQUIRED_FILES[@]}"; do
    if [ ! -f "$PROJECT_DIR/$file" ]; then
        echo "  ❌ Falta archivo: $file"
        ERRORS=$((ERRORS + 1))
    else
        echo "  ✅ $file"
    fi
done

# 3. Verificar SPEC.md tenga requisitos
echo ""
echo "📋 Verificando SPEC.md..."

if grep -q "RF1" "$PROJECT_DIR/SPEC.md"; then
    echo "  ✅ SPEC.md tiene requisitos funcionales"
else
    echo "  ❌ SPEC.md no tiene requisitos funcionales"
    ERRORS=$((ERRORS + 1))
fi

# 4. Verificar features.json es JSON válido
echo ""
echo "🔧 Verificando features.json..."

if python3 -m json.tool "$PROJECT_DIR/features.json" > /dev/null 2>&1; then
    echo "  ✅ features.json es JSON válido"
else
    echo "  ❌ features.json tiene errores de sintaxis"
    ERRORS=$((ERRORS + 1))
fi

# 5. Verificar progress/current.json
echo ""
echo "📊 Verificando progress/current.json..."

if python3 -m json.tool "$PROJECT_DIR/progress/current.json" > /dev/null 2>&1; then
    echo "  ✅ progress/current.json es JSON válido"
else
    echo "  ❌ progress/current.json tiene errores de sintaxis"
    ERRORS=$((ERRORS + 1))
fi

# 6. Verificar init.sh es ejecutable
echo ""
echo "⚡ Verificando init.sh..."

if [ -x "$PROJECT_DIR/scripts/init.sh" ]; then
    echo "  ✅ init.sh es ejecutable"
else
    echo "  ⚠️  init.sh no es ejecutable (chmod +x recomendado)"
fi

# Resultado
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
if [ $ERRORS -eq 0 ]; then
    echo "✅ Validación completada — todo OK"
    exit 0
else
    echo "❌ $ERRORS errores encontrados"
    exit 1
fi