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

// Handle adding a new event
$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
    $event_title = trim($_POST['event_title']);
    $event_type = $_POST['event_type'];
    $event_date = $_POST['event_date'];
    $description = trim($_POST['description']);

    if (empty($event_title) || empty($event_type) || empty($event_date)) {
        $errors[] = "Title, type, and date are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO Academic_Calendar (event_title, event_type, event_date, description, teacher_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $event_title, $event_type, $event_date, $description, $teacher_id);
        if ($stmt->execute()) {
            $success = "Event added successfully!";
        } else {
            $errors[] = "Failed to add event.";
        }
    }
}

// Fetch all events for this teacher
$events = $conn->query("SELECT * FROM Academic_Calendar WHERE teacher_id = $teacher_id ORDER BY event_date ASC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title data-en="Teacher Agenda" data-lt="Mokytojo tvarkaraštis">Teacher Agenda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="#">🎓 <span data-en="LMS" data-lt="LMS">LMS</span></a>
        <div class="d-flex align-items-center">
            <span class="text-white me-3" data-en="Hello" data-lt="Sveiki">Hello</span> <strong class="text-white"><?= htmlspecialchars($first_name) ?></strong>
            <a href="logout.php" class="btn btn-light btn-sm ms-3" data-en="Logout" data-lt="Atsijungti"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="mb-4" data-en="My Agenda" data-lt="Mano tvarkaraštis">My Agenda</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error) echo "<p>$error</p>"; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <!-- Add Event Form -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-success text-white" data-en="Add New Event" data-lt="Pridėti naują renginį">Add New Event</div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="add_event" value="1">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="event_title" placeholder="Event Title" required>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="event_type" required>
                            <option value="" data-en="Select Type" data-lt="Pasirinkti tipą">Select Type</option>
                            <option value="Lecture" data-en="Lecture" data-lt="Paskaita">Lecture</option>
                            <option value="Exam" data-en="Exam" data-lt="Egzaminas">Exam</option>
                            <option value="Deadline" data-en="Deadline" data-lt="Terminas">Deadline</option>
                            <option value="Holiday" data-en="Holiday" data-lt="Šventė">Holiday</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="event_date" required>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="description" placeholder="Description">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-success w-100" data-en="Add" data-lt="Pridėti">Add</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Events Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white" data-en="Upcoming Events" data-lt="Artėjantys renginiai">Upcoming Events</div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th data-en="Date" data-lt="Data">Date</th>
                        <th data-en="Title" data-lt="Pavadinimas">Title</th>
                        <th data-en="Type" data-lt="Tipas">Type</th>
                        <th data-en="Description" data-lt="Aprašymas">Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($event = $events->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($event['event_date']) ?></td>
                            <td><?= htmlspecialchars($event['event_title']) ?></td>
                            <td><?= htmlspecialchars($event['event_type']) ?></td>
                            <td><?= htmlspecialchars($event['description']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if($events->num_rows === 0): ?>
                        <tr>
                            <td colspan="4" class="text-center" data-en="No events yet" data-lt="Renginių dar nėra">No events yet</td>
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
        if (translation) el.textContent = translation;
    });
}
document.addEventListener('DOMContentLoaded', () => {
    const savedLang = localStorage.getItem(LANGUAGE_KEY) || 'en';
    applyLanguage(savedLang);
});
</script>

</body>
</html>
