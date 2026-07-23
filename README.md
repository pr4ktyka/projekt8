# orzeszekstudies - Platforma e-learningowa do nauki programowania

Kompletna platforma e-learningowa do nauki programowania zbudowana w PHP, MySQL, HTML, CSS, JavaScript i Docker.

## 🚀 Szybki start

### Wymagania
- Docker i Docker Compose
- Linux/Mac (lub WSL na Windows)

### Instalacja i uruchomienie

```bash
cd /home/praktyka/php-docker/projekt8

# Uruchom setup script
chmod +x setup.sh
./setup.sh

# Lub ręcznie
docker-compose up -d
```

### Dostęp do aplikacji
- **Aplikacja**: http://localhost:8080
- **MySQL**: localhost:3306

## 📚 Testowe konta

### Administrator
- **Email**: `admin@orzeszekstudies.pl`
- **Hasło**: `admin123`

### Zwykły użytkownik
- **Email**: `user@orzeszekstudies.pl`
- **Hasło**: `user123`

## 📁 Struktura projektu

```
projekt8/
├── app/
│   ├── public/              # Frontend (HTML, CSS, JS)
│   │   ├── index.php        # Strona główna
│   │   ├── register.php     # Rejestracja
│   │   ├── login.php        # Logowanie
│   │   ├── learn.php        # Panel nauki
│   │   ├── quiz.php         # Quizy
│   │   ├── profile.php      # Profil użytkownika
│   │   ├── logout.php       # Wylogowanie
│   │   ├── css/styles.css   # Główny styl
│   │   ├── js/main.js       # JavaScript
│   │   ├── api/             # API endpoints
│   │   │   ├── submit-quiz.php
│   │   │   └── toggle-favorite.php
│   │   └── admin/
│   │       └── dashboard.php # Panel administratora
│   ├── src/                 # Backend (PHP classes)
│   │   ├── config/
│   │   │   ├── Config.php   # Konfiguracja
│   │   │   └── Database.php # Połączenie PDO
│   │   ├── classes/
│   │   │   ├── User.php     # Zarządzanie użytkownikami
│   │   │   ├── Lesson.php   # Zarządzanie lekcjami
│   │   │   ├── Quiz.php     # Zarządzanie quizami
│   │   │   └── Badge.php    # System odznak
│   │   ├── pages/           # Kontrolery stron
│   │   └── auth/
│   │       ├── SessionManager.php  # Zarządzanie sesjami
│   │       └── AuthHandler.php     # Autentykacja
│   ├── database/
│   │   └── init.sql         # Schemat bazy danych
│   ├── templates/           # Szablony HTML
│   ├── Dockerfile
│   └── docker-compose.yml
├── .env.example             # Zmienne środowiskowe
├── .dockerignore
└── setup.sh                 # Script inicjalizacyjny
```

## 🎓 Zawartość kursu

### Poziom Podstawowy
1. **HTML** - Podstawy struktury stron
2. **CSS** - Stylizacja i layouty

### Poziom Średniozaawansowany
3. **JavaScript** - Interaktywność
4. **PHP** - Backend basics

### Poziom Zaawansowany
5. **PHP + MySQL** - Bazy danych
6. **Logowanie i sesje** - Zarządzanie użytkownikami

## 🎯 Funkcjonalności

### Dla użytkowników
- ✅ Rejestracja i logowanie
- ✅ Przeglądanie 6 lekcji (2 na każdy poziom)
- ✅ Quizy po każdej lekcji (5 pytań, 4 odpowiedzi)
- ✅ Śledzenie postępu (%)
- ✅ System odznak (6 typów)
- ✅ Oznaczanie ulubionych lekcji
- ✅ Profil z statystykami

### Dla administratorów
- ✅ Panel administratora
- ✅ Statystyki globalne
- ✅ Lista użytkowników z postępem
- ✅ Zarządzanie bazą danych

## 🛠️ Technologia

- **Backend**: PHP 8.1 + Apache
- **Baza danych**: MySQL 8.0
- **Frontend**: HTML5 + CSS3 + JavaScript
- **Konteneryzacja**: Docker + Docker Compose

## 🎨 Design

- **Motyw**: Ciemno zielony (#2d5016) - przypomina ziemię
- **Czcionka**: Serif (Georgia/Garamond)
- **Responsive**: Dostosowany do mobile, tablet, desktop
- **Header**: Sticky (zawsze widoczny)

## 📊 Baza danych

### Tabele
- `users` - Użytkownicy
- `lesson_levels` - Poziomy nauki
- `lessons` - Lekcje
- `quizzes` - Quizy
- `questions` - Pytania
- `answers` - Odpowiedzi
- `user_progress` - Postęp użytkownika
- `user_favorites` - Ulubione lekcje
- `badges` - Definicje odznak
- `user_badges` - Zdobyte odznaki

## 🔒 Bezpieczeństwo

- ✅ Haszowanie haseł (password_hash/verify)
- ✅ Prepared statements (PDO)
- ✅ Sesje PHP z timeout
- ✅ Zarządzanie rolami (user/admin)
- ✅ Ochrona przed SQL injection

## 📝 Użyteczne komendy

```bash
# Uruchom kontenery
docker-compose up -d

# Wyświetl logi
docker-compose logs -f

# Zatrzymaj kontenery
docker-compose down

# Restartuj kontenery
docker-compose restart

# Usuń wszystkie dane (w tym bazę)
docker-compose down -v

# Wejdź do containera PHP
docker exec -it orzeszekstudies-php bash

# Wejdź do bazy MySQL
docker exec -it orzeszekstudies-mysql mysql -u user -ppassword orzeszekstudies
```

## 🧪 Testowanie

### Rejestracja nowego użytkownika
1. Wejdź na http://localhost:8080
2. Kliknij "Rejestracja"
3. Wpisz email i hasło
4. Zaloguj się

### Test quizu
1. Zaloguj się
2. Kliknij "Rozpocznij naukę" na dowolnym kursie
3. Przeczytaj lekcję
4. Kliknij "Rozwiąż quiz"
5. Odpowiedz na 5 pytań
6. Wynik musi być ≥70% aby zalicyć

### Test panelu admina
1. Zaloguj się jako `admin@orzeszekstudies.pl` / `admin123`
2. Kliknij "Panel Admina"
3. Przeglądaj statystyki i listę użytkowników

## 📱 Responsywność

Aplikacja jest w pełni responsywna:
- **Desktop** (1024px+) - Pełny layout
- **Tablet** (768px-1023px) - Zoptymalizowany
- **Mobile** (320px-767px) - Mobilny view

## 🐛 Troubleshooting

### Baza danych nie inicjalizuje się
```bash
# Sprawdź logi MySQL
docker-compose logs mysql

# Usuń volume i spróbuj ponownie
docker-compose down -v
docker-compose up -d
```

### Port 8080 już zajęty
```bash
# Zmień port w docker-compose.yml
# zmień "8080:80" na "8888:80"
docker-compose up -d
# Dostęp: http://localhost:8888
```

### Nie mogę się zalogować
- Sprawdź email i hasło
- Testowe konta: `admin@orzeszekstudies.pl` / `admin123`

## 📄 Licencja

Projekt utworzony do celów edukacyjnych.

## 👨‍💻 Autor

orzeszekstudies - Platforma e-learningowa

---

**Wersja**: 1.0  
**Data**: 2026-07-23  
**Status**: ✅ Gotowy do użytku
