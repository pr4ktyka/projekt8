#!/bin/bash
# Setup script - inicjalizacja projektu

echo "🌱 orzeszekstudies - Inicjalizacja projektu"
echo ""

# Sprawdzenie czy Docker jest zainstalowany
if ! command -v docker &> /dev/null; then
    echo "❌ Docker nie jest zainstalowany"
    exit 1
fi

# Sprawdzenie czy Docker Compose jest dostępny (wbudowany w Docker 2.0+)
if ! docker compose version &> /dev/null; then
    echo "❌ Docker Compose nie jest dostępny"
    exit 1
fi

echo "✅ Docker znaleziony"
echo "✅ Docker Compose znaleziony"
echo ""

# Build i uruchomienie kontenerów
echo "🐳 Budowanie i uruchamianie kontenerów..."
docker compose up -d

echo ""
echo "⏳ Czekanie na gotowość bazy danych..."
sleep 15

echo ""
echo "✅ Aplikacja uruchomiona!"
echo ""
echo "📱 Dostęp do aplikacji:"
echo "   🌐 http://localhost:8080"
echo ""
echo "📊 Baza danych MySQL:"
echo "   Host: localhost:3306"
echo "   Baza: orzeszekstudies"
echo "   Użytkownik: user"
echo "   Hasło: password"
echo ""
echo "👤 Testowe konta:"
echo "   Admin:  admin@orzeszekstudies.pl / admin123"
echo "   User:   user@orzeszekstudies.pl / user123"
echo ""
echo "💡 Użyteczne komendy:"
echo "   docker compose logs -f php      # Logi PHP"
echo "   docker compose logs -f mysql    # Logi MySQL"
echo "   docker compose ps               # Status kontenerów"
echo "   docker compose down             # Zatrzymaj kontenery"
echo "   docker compose restart          # Restartuj kontenery"
echo ""
