<?php
session_start();
require_once 'config.php';

// Only allow logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$first_name = $_SESSION['first_name'];

// Fetch courses based on role
if ($user_role === 'Teacher') {
    // Courses where this teacher is assigned
    $stmt = $conn->prepare("
        SELECT c.course_id, c.course_name, c.description, p.program_name
        FROM Courses c
        LEFT JOIN Programs p ON c.program_id = p.program_id
        INNER JOIN Teacher_Courses tc ON c.course_id = tc.course_id
        WHERE tc.teacher_id = ?
        ORDER BY c.course_id ASC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $courses = $stmt->get_result();
} elseif ($user_role === 'Student') {
    // Courses where this student is enrolled
    $stmt = $conn->prepare("
        SELECT c.course_id, c.course_name, c.description, p.program_name
        FROM Courses c
        LEFT JOIN Programs p ON c.program_id = p.program_id
        INNER JOIN Student_Courses sc ON c.course_id = sc.course_id
        WHERE sc.student_id = ?
        ORDER BY c.course_id ASC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $courses = $stmt->get_result();
} else {
    // Admin: show all courses
    $courses = $conn->query("
        SELECT c.course_id, c.course_name, c.description, p.program_name
        FROM Courses c
        LEFT JOIN Programs p ON c.program_id = p.program_id
        ORDER BY c.course_id ASC
    ");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title data-en="Courses" data-lt="Kursai">Courses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="#">🎓 <span data-en="LMS" data-lt="LMS">LMS</span></a>
        <div class="d-flex align-items-center">
            <span class="text-white me-3" data-en="Hello" data-lt="Sveiki">Hello</span>
            <strong class="text-white"><?= htmlspecialchars($first_name) ?></strong>
            <a href="logout.php" class="btn btn-light btn-sm ms-3" data-en="Logout" data-lt="Atsijungti"><i class="fas fa-sign-out-alt"></i></a>
            <div class="dropdown ms-3">
                <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
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
    <h2 class="mb-4" data-en="Courses" data-lt="Kursai">Courses</h2>

    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th data-en="ID" data-lt="ID">ID</th>
                        <th data-en="Course Name" data-lt="Kurso pavadinimas">Course Name</th>
                        <th data-en="Description" data-lt="Aprašymas">Description</th>
                        <th data-en="Program" data-lt="Programa">Program</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($courses->num_rows > 0): ?>
                        <?php while($course = $courses->fetch_assoc()): ?>
                            <tr>
                                <td><?= $course['course_id'] ?></td>
                                <td><?= htmlspecialchars($course['course_name']) ?></td>
                                <td><?= htmlspecialchars($course['description']) ?></td>
                                <td><?= htmlspecialchars($course['program_name']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center" data-en="No courses found." data-lt="Kursų nerasta.">No courses found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const LANGUAGE_KEY = 'lms_language';
function setLanguage(lang) {
    localStorage.setItem(LANGUAGE_KEY, lang);
    applyLanguage(lang);
}
function applyLanguage(lang) {
    document.querySelectorAll('[data-en]').forEach(e => {
        const t = e.getAttribute(`data-${lang}`);
        if(t) e.textContent = t;
    });
    document.getElementById('currentLangDisplay').textContent = localStorage.getItem(LANGUAGE_KEY).toUpperCase();
    document.documentElement.setAttribute('lang', lang);
}
document.addEventListener('DOMContentLoaded', () => {
    const savedLang = localStorage.getItem(LANGUAGE_KEY) || 'en';
    applyLanguage(savedLang);
});
</script>
</body>
</html>
