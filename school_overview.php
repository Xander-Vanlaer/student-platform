<?php
session_start();
require_once 'config.php';

// Only allow Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

$first_name = $_SESSION['first_name'];

// Fetch programs with class groups, courses, and student counts
$query = "
SELECT 
    p.program_id,
    p.program_name,
    g.group_id,
    g.group_name,
    g.year_of_study,
    c.course_name,
    c.course_id,
    COUNT(s.user_id) AS student_count
FROM Programs p
LEFT JOIN Class_Groups g ON g.program_id = p.program_id
LEFT JOIN Courses c ON c.program_id = p.program_id
LEFT JOIN Student_Groups s ON s.group_id = g.group_id
GROUP BY p.program_id, g.group_id, c.course_id
ORDER BY p.program_name, g.year_of_study, c.course_name
";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title data-en="School Overview" data-lt="Mokyklos apžvalga">School Overview</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">🎓 <span data-en="LMS" data-lt="LMS">LMS</span></a>
        <div class="d-flex align-items-center">
            <span class="text-white me-3" data-en="Hello" data-lt="Sveiki">Hello</span>
            <strong class="text-white"><?= htmlspecialchars($first_name) ?></strong>

            <!-- Language Dropdown -->
            <div class="dropdown ms-3">
                <button class="btn btn-sm btn-light dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-globe"></i> <span id="currentLangDisplay">EN</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                    <li><a class="dropdown-item" href="#" onclick="setLanguage('en')">English (EN)</a></li>
                    <li><a class="dropdown-item" href="#" onclick="setLanguage('lt')">Lietuvių (LT)</a></li>
                </ul>
            </div>

            <a href="logout.php" class="btn btn-light btn-sm ms-3" data-en="Logout" data-lt="Atsijungti"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="mb-4" data-en="School Overview" data-lt="Mokyklos apžvalga">School Overview</h2>

    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th data-en="Program" data-lt="Programa">Program</th>
                        <th data-en="Class / Group" data-lt="Klasė / Grupė">Class / Group</th>
                        <th data-en="Year" data-lt="Metai">Year</th>
                        <th data-en="Course" data-lt="Kursas">Course</th>
                        <th data-en="Enrolled Students" data-lt="Studentai">Enrolled Students</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['program_name']) ?></td>
                                <td><?= htmlspecialchars($row['group_name']) ?: '-' ?></td>
                                <td><?= $row['year_of_study'] ?: '-' ?></td>
                                <td><?= htmlspecialchars($row['course_name']) ?: '-' ?></td>
                                <td><?= $row['student_count'] ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center" data-en="No data available" data-lt="Duomenų nėra">No data available</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
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
    document.querySelectorAll('[data-en]').forEach(el => {
        const translation = el.getAttribute(`data-${lang}`);
        if(translation) el.textContent = translation;
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
