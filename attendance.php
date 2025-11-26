<?php
session_start();
require_once 'config.php';

// Only teachers
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Teacher') {
    header("Location: login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'];
$errors = [];
$success = "";

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_attendance'])) {
    $instance_id = intval($_POST['course_instance']);
    $date = $_POST['attendance_date'];
    foreach($_POST['attendance'] as $student_id => $status){
        $student_id = intval($student_id);
        $stmt = $conn->prepare("INSERT INTO Attendance (instance_id, student_id, attendance_date, status, recorded_by) 
                                VALUES (?, ?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE status=?");
        $stmt->bind_param("iisssi", $instance_id, $student_id, $date, $status, $teacher_id, $status);
        $stmt->execute();
    }
    $success = "Attendance saved successfully!";
}

// Fetch teacher courses
$courses = $conn->query("SELECT ci.instance_id, c.course_name 
                         FROM Course_Instances ci
                         JOIN Courses c ON ci.course_id = c.course_id
                         WHERE ci.teacher_id = $teacher_id
                         ORDER BY c.course_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title data-en="Attendance" data-lt="Lankomumas">Attendance</title>
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
    <h2 class="mb-4" data-en="Attendance" data-lt="Lankomumas">Attendance</h2>

    <?php if ($errors): ?>
        <div class="alert alert-danger"><?php foreach ($errors as $e) echo "<p>$e</p>"; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if ($courses && $courses->num_rows > 0): ?>
        <?php while($course = $courses->fetch_assoc()): ?>
            <?php
                $stmt = $conn->prepare("SELECT u.user_id, u.first_name, u.last_name
                                        FROM Enrollments e
                                        JOIN Users u ON e.user_id = u.user_id
                                        WHERE e.instance_id=?");
                $stmt->bind_param("i", $course['instance_id']);
                $stmt->execute();
                $res = $stmt->get_result();
                $students = $res->fetch_all(MYSQLI_ASSOC);
            ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-success text-white"><?= htmlspecialchars($course['course_name']) ?></div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="course_instance" value="<?= $course['instance_id'] ?>">
                        <div class="mb-3">
                            <label data-en="Date" data-lt="Data" class="form-label">Date</label>
                            <input type="date" name="attendance_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th data-en="Student" data-lt="Studentas">Student</th>
                                    <th data-en="Present" data-lt="Dalyvavo">Present</th>
                                    <th data-en="Absent" data-lt="Nepasirodė">Absent</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($students as $student): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                                        <td><input type="radio" name="attendance[<?= $student['user_id'] ?>]" value="Present" required></td>
                                        <td><input type="radio" name="attendance[<?= $student['user_id'] ?>]" value="Absent"></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <button type="submit" name="submit_attendance" class="btn btn-primary" data-en="Save Attendance" data-lt="Išsaugoti lankomumą">Save Attendance</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info" data-en="You are not assigned to any courses yet." data-lt="Jums dar nepriskirtos jokios klasės."></div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const LANGUAGE_KEY = 'lms_language';
function setLanguage(lang) {
    localStorage.setItem(LANGUAGE_KEY, lang);
    applyLanguage(lang);
}
function applyLanguage(lang) {
    document.querySelectorAll('[data-en]').forEach(el => {
        const text = el.getAttribute('data-'+lang);
        if(text) el.textContent = text;
    });
    document.getElementById('currentLangDisplay').textContent = lang.toUpperCase();
    document.documentElement.setAttribute('lang', lang);
}
document.addEventListener('DOMContentLoaded', () => {
    const saved = localStorage.getItem(LANGUAGE_KEY) || 'en';
    applyLanguage(saved);
});
</script>

</body>
</html>
