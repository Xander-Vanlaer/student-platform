<?php
session_start();
require_once 'config.php';

// Only Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

$errors = [];
$success = "";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid course ID");
}

$course_id = intval($_GET['id']);

// Fetch course
$stmt = $conn->prepare("SELECT * FROM Courses WHERE course_id=?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) die("Course not found");
$course = $result->fetch_assoc();

// Fetch programs
$programs = $conn->query("SELECT program_id, program_name FROM Programs ORDER BY program_name ASC");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_name = trim($_POST['course_name']);
    $description = trim($_POST['description']);
    $program_id = intval($_POST['program_id']);

    if (empty($course_name) || empty($program_id)) {
        $errors[] = "Course name and program are required.";
    } else {
        $stmt = $conn->prepare("UPDATE Courses SET course_name=?, description=?, program_id=? WHERE course_id=?");
        $stmt->bind_param("ssii", $course_name, $description, $program_id, $course_id);
        if ($stmt->execute()) {
            $success = "Course updated successfully!";
            // Refresh course info
            $course['course_name'] = $course_name;
            $course['description'] = $description;
            $course['program_id'] = $program_id;
        } else {
            $errors[] = "Error updating course.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title data-en="Edit Course" data-lt="Redaguoti kursą">Edit Course</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 data-en="Edit Course" data-lt="Redaguoti kursą">Edit Course</h2>

    <?php if(!empty($errors)): ?>
        <div class="alert alert-danger"><?php foreach($errors as $error) echo "<p>$error</p>"; ?></div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label" data-en="Course Name" data-lt="Kurso pavadinimas">Course Name</label>
            <input type="text" class="form-control" name="course_name" value="<?= htmlspecialchars($course['course_name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label" data-en="Description" data-lt="Aprašymas">Description</label>
            <input type="text" class="form-control" name="description" value="<?= htmlspecialchars($course['description']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label" data-en="Program" data-lt="Programa">Program</label>
            <select class="form-select" name="program_id" required>
                <?php while($prog = $programs->fetch_assoc()): ?>
                    <option value="<?= $prog['program_id'] ?>" <?= $prog['program_id'] == $course['program_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($prog['program_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-success" data-en="Update" data-lt="Atnaujinti">Update</button>
        <a href="courses.php" class="btn btn-secondary" data-en="Back" data-lt="Atgal">Back</a>
    </form>
</div>

<script>
const LANGUAGE_KEY = 'lms_language';
function setLanguage(lang) { localStorage.setItem(LANGUAGE_KEY, lang); applyLanguage(lang); }
function applyLanguage(lang) { document.querySelectorAll('[data-en]').forEach(e => { const t = e.getAttribute(`data-${lang}`); if(t) e.textContent = t; }); document.documentElement.setAttribute('lang', lang); }
document.addEventListener('DOMContentLoaded', () => { const savedLang = localStorage.getItem(LANGUAGE_KEY) || 'en'; applyLanguage(savedLang); });
</script>
</body>
</html>
