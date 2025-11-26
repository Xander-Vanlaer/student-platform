<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config.php';

// Only allow Teachers
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Teacher') {
    header("Location: login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'];
$errors = [];
$success = "";

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_material'])) {
    $course_instance_id = intval($_POST['course_instance']);
    if (isset($_FILES['material']) && $_FILES['material']['error'] === 0) {
        $file_name = basename($_FILES['material']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if ($file_ext !== 'pdf') {
            $errors[] = "Only PDF files are allowed!";
        } else {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
            $target_file = $target_dir . uniqid() . '_' . $file_name;

            if (move_uploaded_file($_FILES['material']['tmp_name'], $target_file)) {
                $stmt = $conn->prepare("INSERT INTO Course_Materials (course_instance_id, file_path, uploaded_by) VALUES (?, ?, ?)");
                $stmt->bind_param("isi", $course_instance_id, $target_file, $teacher_id);
                if ($stmt->execute()) {
                    $success = "Material uploaded successfully!";
                } else {
                    $errors[] = "Database error: " . $stmt->error;
                }
            } else {
                $errors[] = "Failed to move uploaded file.";
            }
        }
    } else {
        $errors[] = "Please select a file to upload.";
    }
}

// Fetch courses assigned to this teacher
$query = "SELECT ci.instance_id, c.course_name 
          FROM Course_Instances ci
          JOIN Courses c ON ci.course_id = c.course_id
          WHERE ci.teacher_id = ?
          ORDER BY c.course_name ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$courses = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title data-en="Course Materials" data-lt="Kurso medžiaga">Course Materials</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="#">🎓 <span data-en="LMS" data-lt="LMS">LMS</span></a>
        <div class="d-flex align-items-center">
            <span class="text-white me-3" data-en="Hello" data-lt="Sveiki">Hello</span>
            <strong class="text-white"><?= htmlspecialchars($first_name) ?></strong>

            <div class="dropdown ms-3">
                <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown">
                    <i class="fas fa-globe"></i> <span id="currentLangDisplay">EN</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#" onclick="setLanguage('en')">English (EN)</a></li>
                    <li><a class="dropdown-item" href="#" onclick="setLanguage('lt')">Lietuvių (LT)</a></li>
                </ul>
            </div>

            <a href="logout.php" class="btn btn-light btn-sm ms-3" data-en="Logout" data-lt="Atsijungti"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="mb-4" data-en="Course Materials" data-lt="Kurso medžiaga">Course Materials</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error) echo "<p>$error</p>"; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if ($courses->num_rows > 0): ?>
        <?php while($course = $courses->fetch_assoc()): ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-success text-white"><?= htmlspecialchars($course['course_name']) ?></div>
                <div class="card-body">
                    <!-- Upload Form -->
                    <form method="POST" enctype="multipart/form-data" class="mb-3">
                        <input type="hidden" name="course_instance" value="<?= $course['instance_id'] ?>">
                        <div class="input-group">
                            <input type="file" name="material" class="form-control" required>
                            <button type="submit" name="upload_material" class="btn btn-primary" data-en="Upload" data-lt="Įkelti">Upload</button>
                        </div>
                        <small class="text-muted" data-en="Only PDF files are allowed" data-lt="Tik PDF failai leidžiami"></small>
                    </form>

                    <!-- Existing Materials -->
                    <h5 data-en="Uploaded Materials" data-lt="Įkelta medžiaga">Uploaded Materials</h5>
                    <ul class="list-group">
                        <?php
                        $stmt2 = $conn->prepare("SELECT * FROM Course_Materials WHERE course_instance_id=? ORDER BY uploaded_at DESC");
                        $stmt2->bind_param("i", $course['instance_id']);
                        $stmt2->execute();
                        $res = $stmt2->get_result();
                        if ($res->num_rows > 0):
                            while($material = $res->fetch_assoc()): ?>
                                <li class="list-group-item">
                                    <a href="<?= htmlspecialchars($material['file_path']) ?>" target="_blank"><?= basename($material['file_path']) ?></a>
                                    <span class="text-muted"> (<?= $material['uploaded_at'] ?>)</span>
                                </li>
                            <?php endwhile;
                        else: ?>
                            <li class="list-group-item" data-en="No materials uploaded yet." data-lt="Medžiaga dar neįkelta">No materials uploaded yet.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info" data-en="You are not assigned to any courses yet." data-lt="Jums dar nepriskirtos jokios klasės."></div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Language toggle
const LANGUAGE_KEY = 'lms_language';
function setLanguage(lang) {
    localStorage.setItem(LANGUAGE_KEY, lang);
    applyLanguage(lang);
}
function applyLanguage(lang) {
    document.querySelectorAll('[data-en]').forEach(el => {
        const t = el.getAttribute('data-' + lang);
        if(t) el.textContent = t;
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
