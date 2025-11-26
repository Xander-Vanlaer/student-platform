<?php
session_start();
require_once 'config.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $errors[] = "Please enter both email and password.";
    } else {
        $stmt = $conn->prepare("SELECT u.user_id, u.first_name, u.last_name, u.password_hash, u.role_id, u.is_first_login, r.role_name
                                FROM Users u
                                INNER JOIN Roles r ON u.role_id = r.role_id
                                WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password_hash'])) {
                // Login successful
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['role'] = $user['role_name'];

                // First-time login: redirect to password change
                if ($user['is_first_login']) {
                    header("Location: change_password.php");
                    exit;
                }

                header("Location: dashboard.php");
                exit;

            } else {
                $errors[] = "Invalid password.";
            }
        } else {
            $errors[] = "Email not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title data-en="Login - School Management System" data-lt="Prisijungimas - Mokyklos Sistema">Login - School Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-light">

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
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header text-center bg-primary text-white">
                    <h4 data-en="Login" data-lt="Prisijungimas">Login</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error) echo "<p>$error</p>"; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label" data-en="Email" data-lt="El. paštas">Email</label>
                            <input type="email" class="form-control" name="email" id="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label" data-en="Password" data-lt="Slaptažodis">Password</label>
                            <input type="password" class="form-control" name="password" id="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" data-en="Login" data-lt="Prisijungti">Login</button>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <small data-en="© 2025 School Management System" data-lt="© 2025 Mokyklos Valdymo Sistema">© 2025 School Management System</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Dual-language script -->
<script>
    const LANGUAGE_KEY = 'lms_language';
    
    function setLanguage(lang) {
        localStorage.setItem(LANGUAGE_KEY, lang);
        applyLanguage(lang);
    }

    function applyLanguage(lang) {
        document.querySelectorAll('[data-en]').forEach(element => {
            const translation = element.getAttribute(`data-${lang}`);
            if (translation) {
                element.textContent = translation;
            }
        });

        document.getElementById('currentLangDisplay').textContent = lang.toUpperCase();
        document.documentElement.setAttribute('lang', lang);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const savedLang = localStorage.getItem(LANGUAGE_KEY) || 'en';
        applyLanguage(savedLang);
    });
</script>

</body>
</html>
