/*!40101 SET NAMES utf8mb4 */;
/*!40101 SET CHARACTER SET utf8mb4 */;

-- ===================================
-- BAZA DANYCH: orzeszekstudies
-- ===================================

CREATE DATABASE IF NOT EXISTS orzeszekstudies;
USE orzeszekstudies;

-- ===================================
-- 1. TABELA: Poziomy nauki
-- ===================================
CREATE TABLE lesson_levels (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO lesson_levels (name, description) VALUES
('Podstawowy', 'Podstawowe wiadomości o HTML i CSS'),
('Średniozaawansowany', 'JavaScript i PHP'),
('Zaawansowany', 'PHP z bazą danych i sesje');

-- ===================================
-- 2. TABELA: Lekcje
-- ===================================
CREATE TABLE lessons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    level_id INT NOT NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'published',
    content LONGTEXT NOT NULL,
    order_in_level INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (status),
    FOREIGN KEY (level_id) REFERENCES lesson_levels(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO lessons (title, level_id, content, order_in_level) VALUES
(
    'HTML',
    1,
    '<h2>Lekcja 1: HTML</h2><p>HTML to język znaczników służący do tworzenia stron internetowych. Każdy element HTML ma otwierający i zamykający tag.</p><h3>Podstawowe tagi:</h3><ul><li>&lt;html&gt; - główny element strony</li><li>&lt;head&gt; - metadane strony</li><li>&lt;body&gt; - zawartość widoczna</li><li>&lt;h1&gt; do &lt;h6&gt; - nagłówki</li><li>&lt;p&gt; - paragraf</li><li>&lt;a&gt; - link</li></ul><p>Pamiętaj: HTML opisuje strukturę, nie wygląd!</p>',
    1
),
(
    'CSS',
    1,
    '<h2>Lekcja 2: CSS</h2><p>CSS (Cascading Style Sheets) służy do stylizacji elementów HTML.</p><h3>Selektory CSS:</h3><ul><li>.klasa {} - selektor klasy</li><li>#id {} - selektor ID</li><li>element {} - selektor elementu</li></ul><h3>Właściwości:</h3><ul><li>color - kolor tekstu</li><li>background-color - kolor tła</li><li>font-size - rozmiar czcionki</li><li>padding - wewnętrzne marginesy</li><li>margin - zewnętrzne marginesy</li></ul>',
    2
),
(
    'JavaScript',
    2,
    '<h2>Lekcja 3: JavaScript</h2><p>JavaScript to język programowania dla przeglądarki. Pozwala tworzyć interaktywne strony.</p><h3>Zmienne:</h3><pre>let x = 5;\nconst imie = "Adam";\nvar stara_skladnia = true;</pre><h3>Funkcje:</h3><pre>function dodaj(a, b) {\n  return a + b;\n}</pre><h3>Obsługa zdarzeń:</h3><p>onclick, onchange, onload - pozwalają reagować na akcje użytkownika</p>',
    3
),
(
    'PHP',
    2,
    '<h2>Lekcja 4: PHP</h2><p>PHP to język skryptowy po stronie serwera. Rozszerzenie .php</p><h3>Podstawowa składnia:</h3><pre>&lt;?php\necho "Cześć świecie";\n$zmienna = 123;\n?&gt;</pre><h3>Instrukcje warunkowe:</h3><pre>if ($x > 5) {\n  echo "Większe niż 5";\n} else {\n  echo "Mniejsze";\n}</pre><h3>Pętle:</h3><pre>for ($i = 0; $i < 10; $i++) {\n  echo $i;\n}</pre>',
    4
),
(
    'PHP + MySQL',
    3,
    '<h2>Lekcja 5: PHP + MySQL</h2><p>Łączenie PHP z bazą danych MySQL.</p><h3>Połączenie PDO:</h3><pre>$pdo = new PDO("mysql:host=localhost;dbname=test", "user", "password");\n$sql = "SELECT * FROM users";\n$stmt = $pdo->prepare($sql);\n$stmt->execute();</pre><h3>Operacje:</h3><ul><li>SELECT - odczyt danych</li><li>INSERT - dodawanie</li><li>UPDATE - aktualizacja</li><li>DELETE - usuwanie</li></ul>',
    5
),
(
    'Logowanie użytkowników i sesje',
    3,
    '<h2>Lekcja 6: Logowanie i sesje</h2><p>Zarządzanie sesjami użytkowników w PHP.</p><h3>Sesje:</h3><pre>session_start();\n$_SESSION["user_id"] = 123;\necho $_SESSION["user_id"];</pre><h3>Haszowanie haseł:</h3><pre>$hash = password_hash($haslo, PASSWORD_DEFAULT);\nif (password_verify($haslo, $hash)) {\n  echo "Hasło poprawne";\n}</pre><h3>Logout:</h3><pre>session_destroy();</pre>',
    6
);

-- ===================================
-- 3. TABELA: Użytkownicy
-- ===================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Testowe konta (hasła zahaszowane)
-- admin@orzeszekstudies.pl / admin123
-- user@orzeszekstudies.pl / user123
INSERT INTO users (email, password_hash, role) VALUES
('admin@orzeszekstudies.pl', '$2y$10$Rao6G4EIhzQDFHlwqO23RuZs7HWV2NeE/GJzZHX9KzLwNkbHiPNpu', 'admin'),
('user@orzeszekstudies.pl', '$2y$10$g7w95KFdN4kpI7yos8rrIuAr3qrK5fRW/CgkdAhZFsH2ZT0.TGt5e', 'user');

-- ===================================
-- 4. TABELA: Quizy
-- ===================================
CREATE TABLE quizzes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lesson_id INT NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO quizzes (lesson_id) VALUES (1), (2), (3), (4), (5), (6);

-- ===================================
-- 5. TABELA: Pytania w quizach
-- ===================================
CREATE TABLE questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quiz_id INT NOT NULL,
    question_text TEXT NOT NULL,
    question_order INT NOT NULL,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO questions (quiz_id, question_text, question_order) VALUES
(1, 'Jaki tag HTML jest używany do utworzenia paragrafu?', 1),
(1, 'Jakie są trzy główne tagi strukturalne HTML5?', 2),
(1, 'Który tag służy do tworzenia listy nienumerowanej?', 3),
(1, 'Co oznacza DOCTYPE?', 4),
(1, 'Który atrybut HTML jest używany do unikania XSS?', 5),
(2, 'Jak zdefiniujesz klasę w CSS?', 1),
(2, 'Jaka jest specyficzność ID vs klasy?', 2),
(2, 'Do czego służy właściwość "box-model"?', 3),
(2, 'Jakie są jednostki względne w CSS?', 4),
(2, 'Jak ustawić element na środku za pomocą flexbox?', 5),
(3, 'Jaka jest różnica między let, const, a var?', 1),
(3, 'Czym jest callback function?', 2),
(3, 'Jak obsługujesz event click w JavaScript?', 3),
(3, 'Czym jest hoisting?', 4),
(3, 'Jaka jest różnica między == a ===?', 5),
(4, 'Jaki tag PHP należy użyć do rozpoczęcia kodu?', 1),
(4, 'Jak deklarujesz zmienną w PHP?', 2),
(4, 'Czym jest PDO w PHP?', 3),
(4, 'Jak łączyć stringi w PHP?', 4),
(4, 'Jaka jest różnica między include a require?', 5),
(5, 'Jaki jest cel przygotowanych wyrażeń (prepared statements)?', 1),
(5, 'Jak pobrać dane z bazy danych za pomocą PDO?', 2),
(5, 'Czym jest SQL injection?', 3),
(5, 'Jak wykonać INSERT do bazy?', 4),
(5, 'Jakie są typy złączeń w SQL?', 5),
(6, 'Do czego służy funkcja session_start()?', 1),
(6, 'Jak bezpiecznie przechowywać hasła?', 2),
(6, 'Czym jest CSRF attack?', 3),
(6, 'Jak zniszczyć sesję?', 4),
(6, 'Jaka jest różnica między $_SESSION a $_COOKIE?', 5);

-- ===================================
-- 6. TABELA: Odpowiedzi na pytania
-- ===================================
CREATE TABLE answers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    question_id INT NOT NULL,
    answer_text TEXT NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    letter CHAR(1) NOT NULL,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Odpowiedzi quiz 1 (HTML)
INSERT INTO answers (question_id, answer_text, is_correct, letter) VALUES
(1, '<p>', TRUE, 'A'),
(1, '<paragraph>', FALSE, 'B'),
(1, '<text>', FALSE, 'C'),
(1, '<para>', FALSE, 'D'),
(2, '<header>, <main>, <footer>', TRUE, 'A'),
(2, '<div>, <span>, <p>', FALSE, 'B'),
(2, '<html>, <head>, <body>', FALSE, 'C'),
(2, '<article>, <section>, <aside>', FALSE, 'D'),
(3, '<ul>', TRUE, 'A'),
(3, '<ol>', FALSE, 'B'),
(3, '<li>', FALSE, 'C'),
(3, '<list>', FALSE, 'D'),
(4, 'Document Type Definition', TRUE, 'A'),
(4, 'Data Type Documentation', FALSE, 'B'),
(4, 'Definition Of Text', FALSE, 'C'),
(4, 'Dynamic Type Document', FALSE, 'D'),
(5, 'htmlspecialchars()', TRUE, 'A'),
(5, 'htmlencode()', FALSE, 'B'),
(5, 'xss_protect()', FALSE, 'C'),
(5, 'sanitize()', FALSE, 'D'),
-- Odpowiedzi quiz 2 (CSS)
(6, '.klasa {}', TRUE, 'A'),
(6, '#klasa {}', FALSE, 'B'),
(6, ':klasa {}', FALSE, 'C'),
(6, '@klasa {}', FALSE, 'D'),
(7, 'ID: 100, Klasa: 10', TRUE, 'A'),
(7, 'ID: 10, Klasa: 100', FALSE, 'B'),
(7, 'Równa specyficzność', FALSE, 'C'),
(7, 'Zależy od przeglądarki', FALSE, 'D'),
(8, 'Opisuje marginesy, padding, border i content', TRUE, 'A'),
(8, 'Opisuje font i tekst', FALSE, 'B'),
(8, 'Opisuje kolory', FALSE, 'C'),
(8, 'Opisuje animacje', FALSE, 'D'),
(9, 'em, rem, %', TRUE, 'A'),
(9, 'px, pt, cm', FALSE, 'B'),
(9, 'mm, in, pc', FALSE, 'C'),
(9, 'ch, ex, vw', FALSE, 'D'),
(10, 'justify-content: center; align-items: center;', TRUE, 'A'),
(10, 'text-align: center;', FALSE, 'B'),
(10, 'margin: auto;', FALSE, 'C'),
(10, 'position: absolute;', FALSE, 'D'),
-- Odpowiedzi quiz 3 (JavaScript)
(11, 'let i const są block-scoped, var jest function-scoped', TRUE, 'A'),
(11, 'Nie ma żadnej różnicy', FALSE, 'B'),
(11, 'var jest lepszy', FALSE, 'C'),
(11, 'const nie może być reasignowany', FALSE, 'D'),
(12, 'Funkcja przekazana jako argument innej funkcji', TRUE, 'A'),
(12, 'Funkcja zwracająca inną funkcję', FALSE, 'B'),
(12, 'Funkcja bez parametrów', FALSE, 'C'),
(12, 'Funkcja asynchroniczna', FALSE, 'D'),
(13, 'element.addEventListener("click", function)', TRUE, 'A'),
(13, 'element.onclick = function', FALSE, 'B'),
(13, 'oba są prawidłowe', FALSE, 'C'),
(13, 'element.onclicked', FALSE, 'D'),
(14, 'Podnoszenie deklaracji na górę zakresu', TRUE, 'A'),
(14, 'Podnoszenie wartości zmiennych', FALSE, 'B'),
(14, 'Powtórzenie funkcji', FALSE, 'C'),
(14, 'Nic specjalnego', FALSE, 'D'),
(15, '== porównuje wartość, === porównuje wartość i typ', TRUE, 'A'),
(15, '=== porównuje wartość, == porównuje typ', FALSE, 'B'),
(15, 'Nie ma różnicy', FALSE, 'C'),
(15, '=== nie sprawdza typu', FALSE, 'D'),
-- Odpowiedzi quiz 4 (PHP)
(16, '<?php', TRUE, 'A'),
(16, '<php>', FALSE, 'B'),
(16, '<?', FALSE, 'C'),
(16, '<? php', FALSE, 'D'),
(17, '$zmienna = wartość;', TRUE, 'A'),
(17, 'let $zmienna = wartość;', FALSE, 'B'),
(17, 'var $zmienna = wartość;', FALSE, 'C'),
(17, 'zmienna = wartość;', FALSE, 'D'),
(18, 'Interfejs do bazy danych', TRUE, 'A'),
(18, 'Personal Data Organizer', FALSE, 'B'),
(18, 'PHP Data Processor', FALSE, 'C'),
(18, 'Pattern Design Object', FALSE, 'D'),
(19, '. (kropka)', TRUE, 'A'),
(19, '+ (plus)', FALSE, 'B'),
(19, '& (ampersand)', FALSE, 'C'),
(19, ', (przecinek)', FALSE, 'D'),
(20, 'include pozwala program ciągnąć, require zatrzymuje błąd', TRUE, 'A'),
(20, 'Nie ma różnicy', FALSE, 'B'),
(20, 'require jest lepszy', FALSE, 'C'),
(20, 'include jest szybszy', FALSE, 'D'),
-- Odpowiedzi quiz 5 (PHP + MySQL)
(21, 'Zapobieganie SQL injection', TRUE, 'A'),
(21, 'Przyspiesz zapytania', FALSE, 'B'),
(21, 'Zmniejsz rozmiar bazy', FALSE, 'C'),
(21, 'Nic specjalnego', FALSE, 'D'),
(22, '$stmt = $pdo->prepare(); $stmt->execute(); $result = $stmt->fetchAll();', TRUE, 'A'),
(22, '$result = $pdo->select("*", "users");', FALSE, 'B'),
(22, '$result = $pdo->query("SELECT *");', FALSE, 'C'),
(22, '$result = $pdo->fetch();', FALSE, 'D'),
(23, 'Wstrzyknięcie złośliwego kodu SQL', TRUE, 'A'),
(23, 'Atak na serwer', FALSE, 'B'),
(23, 'Usunięcie bazy danych', FALSE, 'C'),
(23, 'Zmiana hasła', FALSE, 'D'),
(24, 'INSERT INTO users (email) VALUES (?)', TRUE, 'A'),
(24, 'ADD INTO users (email)', FALSE, 'B'),
(24, 'PUSH INTO users (email)', FALSE, 'C'),
(24, 'CREATE INTO users', FALSE, 'D'),
(25, 'INNER, LEFT, RIGHT, FULL OUTER', TRUE, 'A'),
(25, 'PRIMARY, SECONDARY, TERTIARY', FALSE, 'B'),
(25, 'ONE, TWO, THREE, FOUR', FALSE, 'C'),
(25, 'SIMPLE, COMPLEX, ADVANCED', FALSE, 'D'),
-- Odpowiedzi quiz 6 (Logowanie)
(26, 'Inicjować dane sesji', TRUE, 'A'),
(26, 'Zniszczyć sesję', FALSE, 'B'),
(26, 'Sprawdzić logowanie', FALSE, 'C'),
(26, 'Wylogować użytkownika', FALSE, 'D'),
(27, 'password_hash() i password_verify()', TRUE, 'A'),
(27, 'md5(haslo)', FALSE, 'B'),
(27, 'sha1(haslo)', FALSE, 'C'),
(27, 'base64_encode(haslo)', FALSE, 'D'),
(28, 'Atak na formularze bez tokenu', TRUE, 'A'),
(28, 'Atak na hasła', FALSE, 'B'),
(28, 'SQL Injection', FALSE, 'C'),
(28, 'XSS Attack', FALSE, 'D'),
(29, 'session_destroy();', TRUE, 'A'),
(29, 'session_end();', FALSE, 'B'),
(29, 'session_close();', FALSE, 'C'),
(29, 'unset($_SESSION);', FALSE, 'D'),
(30, '$_SESSION persystuje na serwerze, $_COOKIE na kliencie', TRUE, 'A'),
(30, 'Nie ma różnicy', FALSE, 'B'),
(30, '$_COOKIE jest bardziej bezpieczny', FALSE, 'C'),
(30, '$_SESSION jest wysyłany do przeglądarki', FALSE, 'D');

-- ===================================
-- 7. TABELA: Postęp użytkownika
-- ===================================
CREATE TABLE user_progress (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    lesson_id INT NOT NULL,
    completed_at TIMESTAMP NULL,
    quiz_score INT NULL,
    last_accessed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, lesson_id),
    INDEX (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- 8. TABELA: Ulubione lekcje
-- ===================================
CREATE TABLE user_favorites (
    user_id INT NOT NULL,
    lesson_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, lesson_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- 9. TABELA: Definicje odznak
-- ===================================
CREATE TABLE badges (
    id INT PRIMARY KEY AUTO_INCREMENT,
    badge_type VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO badges (badge_type, name, description) VALUES
('first_lesson', 'Pierwsza Lekcja', 'Za ukończenie pierwszej lekcji'),
('basic_level_complete', 'Mistrz Podstaw', 'Za ukończenie wszystkich lekcji poziomu Podstawowego'),
('intermediate_level_complete', 'Średniozaawansowany', 'Za ukończenie wszystkich lekcji poziomu Średniozaawansowanego'),
('advanced_level_complete', 'Ekspert', 'Za ukończenie wszystkich lekcji poziomu Zaawansowanego'),
('all_courses_complete', 'Wszystkie Kursy Ukończone', 'Za ukończenie wszystkich 6 lekcji'),
('perfect_quiz', '100% Poprawnych Odpowiedzi', 'Za uzyskanie wyniku 100% w quizie');

-- ===================================
-- 10. TABELA: Zdobyte odznaki użytkownika
-- ===================================
CREATE TABLE user_badges (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    badge_id INT NOT NULL,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, badge_id),
    INDEX (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- WIDOKI (Views) do statystyk
-- ===================================

CREATE VIEW user_stats AS
SELECT 
    u.id,
    u.email,
    u.role,
    COUNT(DISTINCT up.lesson_id) as lessons_completed,
    ROUND(AVG(COALESCE(up.quiz_score, 0)), 2) as avg_quiz_score,
    COUNT(DISTINCT ub.badge_id) as badges_earned,
    ROUND(COUNT(DISTINCT up.lesson_id) / 6 * 100, 2) as completion_percentage
FROM users u
LEFT JOIN user_progress up ON u.id = up.user_id AND up.completed_at IS NOT NULL
LEFT JOIN user_badges ub ON u.id = ub.user_id
GROUP BY u.id, u.email, u.role;

CREATE VIEW global_stats AS
SELECT 
    COUNT(DISTINCT users.id) as total_users,
    COUNT(DISTINCT CASE WHEN last_login > DATE_SUB(NOW(), INTERVAL 7 DAY) THEN users.id END) as active_users,
    COUNT(DISTINCT up.user_id) as users_completed_courses,
    ROUND(AVG(COALESCE(up.quiz_score, 0)), 2) as avg_quiz_score
FROM users
LEFT JOIN user_progress up ON users.id = up.user_id AND up.completed_at IS NOT NULL;
