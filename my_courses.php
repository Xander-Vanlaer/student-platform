<?php
session_start();
require_once 'config.php';

// Only allow Teachers
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Teacher') {
    header("Location: login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'];

// Handle AJAX request to generate temp password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_temp_account'])) {
    $instance_id = intval($_POST['instance_id']);
    $temp_password = bin2hex(random_bytes(5)); // 10-character hex password

    // Generate random email for student
    $email = 'student' . rand(1000,9999) . '@example.com';
    $first_name_student = 'Student';
    $last_name_student = rand(1000,9999);
    $role_id = 3; // Student

    $hash = password_hash($temp_password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO Users (first_name, last_name, email, password_hash, role_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $first_name_student, $last_name_student, $email, $hash, $role_id);

    if($stmt->execute()) {
        $user_id = $stmt->insert_id;
        // Enroll the student to the course
        $enroll_stmt = $conn->prepare("INSERT INTO Enrollments (user_id, instance_id, status) VALUES (?, ?, 'Enrolled')");
        $enroll_stmt->bind_param("ii", $user_id, $instance_id);
        $enroll_stmt->execute();

        echo json_encode(['success' => true, 'email' => $email, 'password' => $temp_password]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Could not create student.']);
    }
    exit;
}

// Fetch courses for this teacher using prepared statement
$stmt = $conn->prepare("SELECT ci.instance_id, c.course_name 
                        FROM Course_Instances ci
                        JOIN Courses c ON ci.course_id = c.course_id
                        WHERE ci.teacher_id = ?
                        ORDER BY c.course_name ASC");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$courses = $stmt->get_result();

// Fetch enrolled students per course
$enrolled_students = [];
while($course = $courses->fetch_assoc()) {
    $stmt2 = $conn->prepare("SELECT u.first_name, u.last_name, u.email 
                             FROM Enrollments e
                             JOIN Users u ON e.user_id = u.user_id
                             WHERE e.instance_id = ?");
    $stmt2->bind_param("i", $course['instance_id']);
    $stmt2->execute();
    $result = $stmt2->get_result();
    $students = [];
    while($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $enrolled_students[$course['instance_id']] = $students;
}

// Reset pointer to display courses
$courses->data_seek(0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title data-en="My Courses" data-lt="Mano kursai">My Courses</title>
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
    <h2 class="mb-4" data-en="My Courses" data-lt="Mano kursai">My Courses</h2>

    <?php if ($courses->num_rows === 0): ?>
        <div class="alert alert-warning">No courses found for your account.</div>
    <?php endif; ?>

    <?php while($course = $courses->fetch_assoc()): ?>
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-success text-white">
                <?= htmlspecialchars($course['course_name']) ?>
            </div>
            <div class="card-body">
                <h5 data-en="Enrolled Students" data-lt="Užsiregistravę studentai">Enrolled Students</h5>
                <ul class="list-group mb-3">
                    <?php if(!empty($enrolled_students[$course['instance_id']])): ?>
                        <?php foreach($enrolled_students[$course['instance_id']] as $student): ?>
                            <li class="list-group-item"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?> (<?= htmlspecialchars($student['email']) ?>)</li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item" data-en="No students enrolled" data-lt="Studentų nėra">No students enrolled</li>
                    <?php endif; ?>
                </ul>

                <button class="btn btn-warning btn-sm" onclick="generateTempAccount(<?= $course['instance_id'] ?>)" data-en="Generate Temporary Student Account" data-lt="Sugeneruoti laikiną studento paskyrą">
                    Generate Temp Account
                </button>
                <span id="tempAccount-<?= $course['instance_id'] ?>" class="ms-2 fw-bold"></span>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Dual-language
const LANGUAGE_KEY = 'lms_language';
function setLanguage(lang) {
    localStorage.setItem(LANGUAGE_KEY, lang);
    applyLanguage(lang);
}
function applyLanguage(lang) {
    document.querySelectorAll('[data-en]').forEach(element => {
        const translation = element.getAttribute(`data-${lang}`);
        if(translation) element.textContent = translation;
    });
    document.getElementById('currentLangDisplay').textContent = lang.toUpperCase();
    document.documentElement.setAttribute('lang', lang);
}
document.addEventListener('DOMContentLoaded', () => {
    const savedLang = localStorage.getItem(LANGUAGE_KEY) || 'en';
    applyLanguage(savedLang);
});

// Generate temp account
function generateTempAccount(instanceId) {
    fetch('my_courses.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'generate_temp_account=1&instance_id=' + instanceId
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            document.getElementById('tempAccount-' + instanceId).textContent = data.email + ' / ' + data.password;
        } else {
            alert(data.message || 'Error generating temporary account.');
        }
    })
    .catch(err => console.error(err));
}
</script>
</body>
</html>
