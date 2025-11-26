<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-en="LMS Home" data-lt="LMS Pradžia">School Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        /* Custom styles for better look */
        .header-bg {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://picsum.photos/1200/600/?blur=2');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 8rem 0;
            margin-bottom: 2rem;
        }
        .feature-icon-box {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 2rem;
            text-align: center;
            transition: transform 0.3s;
        }
        .feature-icon-box:hover {
            transform: translateY(-5px);
        }
        .btn-custom-login {
            background-color: #007bff; /* Primary color */
            border-color: #007bff;
            transition: background-color 0.3s;
        }
        .btn-custom-login:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">🎓 <span data-en="LMS" data-lt="LMS">LMS</span></a>
        
        <div class="d-flex align-items-center">
            
            <div class="dropdown me-3">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-globe"></i> <span id="currentLangDisplay">EN</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                    <li><a class="dropdown-item" href="#" onclick="setLanguage('en')">English (EN)</a></li>
                    <li><a class="dropdown-item" href="#" onclick="setLanguage('lt')">Lietuvių (LT)</a></li>
                </ul>
            </div>
            
            <a href="login.php" class="btn btn-custom-login text-white" data-en="Login" data-lt="Prisijungti">Login</a>
        </div>
    </div>
</nav>

<header class="header-bg text-center">
    <div class="container">
        <h1 class="display-3 fw-bold" data-en="Welcome to the School Management System" data-lt="Sveiki atvykę į Mokyklos Valdymo Sistemą">
            Welcome to the School Management System
        </h1>
        <p class="lead mt-4" data-en="Your centralized portal for courses, schedules, and communication." data-lt="Jūsų centralizuotas portalas kursams, tvarkaraščiams ir bendravimui.">
            Your centralized portal for courses, schedules, and communication.
        </p>
        <a href="login.php" class="btn btn-primary btn-lg mt-3" data-en="Get Started" data-lt="Pradėti">
            Get Started
        </a>
    </div>
</header>

<main class="container">
    <h2 class="text-center mb-5" data-en="Key Features" data-lt="Pagrindinės Funkcijos">Key Features</h2>
    
    <div class="row row-cols-1 row-cols-md-3 g-4">
        
        <div class="col">
            <div class="feature-icon-box">
                <i class="fas fa-calendar-alt fa-3x text-primary mb-3"></i>
                <h3 data-en="Dynamic Agenda" data-lt="Dinamiškas Tvarkaraštis">Dynamic Agenda</h3>
                <p data-en="View course schedules, classes, and academic events in one place." data-lt="Peržiūrėkite kursų tvarkaraščius, pamokas ir akademinius renginius vienoje vietoje.">View course schedules, classes, and academic events in one place.</p>
            </div>
        </div>

        <div class="col">
            <div class="feature-icon-box">
                <i class="fas fa-comments fa-3x text-success mb-3"></i>
                <h3 data-en="Integrated Chat" data-lt="Integruotas Pokalbis">Integrated Chat</h3>
                <p data-en="Communicate directly with teachers, students, and get AI support." data-lt="Bendraukite tiesiogiai su mokytojais, mokiniais ir gaukite AI pagalbą.">Communicate directly with teachers, students, and get AI support.</p>
            </div>
        </div>
        
        <div class="col">
            <div class="feature-icon-box">
                <i class="fas fa-file-alt fa-3x text-danger mb-3"></i>
                <h3 data-en="Document Access" data-lt="Prieiga prie Dokumentų">Document Access</h3>
                <p data-en="Access all course materials, documents, and assignments easily." data-lt="Lengvai pasiekite visą kursų medžiagą, dokumentus ir užduotis.">Access all course materials, documents, and assignments easily.</p>
            </div>
        </div>
    </div>
</main>

<br>

<footer class="bg-dark text-white text-center py-3 mt-5">
    <p class="mb-0" data-en="© 2025 School Management System" data-lt="© 2025 Mokyklos Valdymo Sistema">© 2025 School Management System</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const LANGUAGE_KEY = 'lms_language';
    
    // Function to set the language preference
    function setLanguage(lang) {
        localStorage.setItem(LANGUAGE_KEY, lang);
        applyLanguage(lang);
    }

    // Function to apply the selected language across all elements
    function applyLanguage(lang) {
        document.querySelectorAll('[data-en]').forEach(element => {
            const translation = element.getAttribute(`data-${lang}`);
            if (translation) {
                // Check if it's a title (to update the non-data-attribute text)
                if (element.tagName === 'TITLE' || element.tagName.startsWith('H') || element.tagName === 'P' || element.tagName === 'A' || element.tagName === 'BUTTON') {
                    element.textContent = translation;
                }
                // For other text-bearing elements
                else {
                    element.textContent = translation;
                }
            }
        });
        
        // Update the display for the current language in the dropdown
        document.getElementById('currentLangDisplay').textContent = lang.toUpperCase();
        
        // Update the HTML language attribute
        document.documentElement.setAttribute('lang', lang);
    }

    // Initialize the language on page load
    document.addEventListener('DOMContentLoaded', () => {
        const savedLang = localStorage.getItem(LANGUAGE_KEY) || 'en'; // Default to English
        applyLanguage(savedLang);
    });
</script>

</body>
</html>