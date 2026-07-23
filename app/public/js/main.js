/**
 * Główny plik JavaScript
 * orzeszekstudies - interakcje frontendu
 */

// Przełączanie ulubionych
function toggleFavorite(lessonId, element) {
    const starIcon = element.querySelector('.lesson-star');
    
    fetch('/api/toggle-favorite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ lesson_id: lessonId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            starIcon.classList.toggle('active');
        }
    })
    .catch(error => console.error('Błąd:', error));
}

// Przełączanie lekcji
function selectLesson(lessonId) {
    window.location.href = '/learn.php?lesson=' + lessonId;
}

// Obliczanie wyniku quizu
function submitQuiz() {
    const form = document.getElementById('quiz-form');
    const formData = new FormData(form);
    
    fetch('/api/submit-quiz.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayResults(data.results);
        } else {
            alert('Błąd: ' + data.message);
        }
    })
    .catch(error => console.error('Błąd:', error));
}

// Wyświetlanie wyników quizu
function displayResults(results) {
    const resultsHtml = `
        <div class="quiz-results">
            <div class="result-score">${results.percentage}%</div>
            <div class="result-status ${results.passed ? 'pass' : 'fail'}">
                ${results.passed ? 'Zaliczone ✓' : 'Niezaliczone ✗'}
            </div>
            <p>${results.correct} / ${results.total} prawidłowych odpowiedzi</p>
            <button class="btn btn-primary" onclick="location.reload()">Spróbuj jeszcze raz</button>
        </div>
    `;
    
    document.getElementById('quiz-container').innerHTML = resultsHtml;
}

// Aktualizacja paska postępu
function updateProgressBar(percentage) {
    const progressBar = document.querySelector('.progress-bar');
    if (progressBar) {
        progressBar.style.width = percentage + '%';
    }
}

// Filtry poziomów kursu
function filterLevel(levelId) {
    const courses = document.querySelectorAll('.course-card');
    courses.forEach(course => {
        if (course.dataset.level == levelId || levelId == 'all') {
            course.style.display = 'block';
        } else {
            course.style.display = 'none';
        }
    });
}

// Walidacja formularza
function validateForm(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('input[required], textarea[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = '#f44336';
            isValid = false;
        } else {
            input.style.borderColor = '#e0e0e0';
        }
    });

    return isValid;
}

// Efekt hover na kartach
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.card, .course-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
