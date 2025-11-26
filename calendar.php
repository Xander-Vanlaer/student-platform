<?php
session_start();
require_once 'config.php';

// Only allow Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

$errors = [];
$success = "";

// Handle adding a new event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
    $title = trim($_POST['title']);
    $event_date = $_POST['event_date'];
    $event_type = $_POST['event_type'];
    $description = trim($_POST['description']);

    if (empty($title) || empty($event_date) || empty($event_type)) {
        $errors[] = "Title, Event Date, and Event Type are required.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO Academic_Calendar (title, event_date, event_type, description) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("ssss", $title, $event_date, $event_type, $description);
        if ($stmt->execute()) {
            $success = "Event added successfully!";
        } else {
            $errors[] = "Error adding event.";
        }
    }
}

// Fetch all events
$events = $conn->query("SELECT * FROM Academic_Calendar ORDER BY event_date ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Academic Calendar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="#">🎓 LMS</a>
        <div class="d-flex align-items-center">
            <span class="text-white me-3">Hello</span>
            <strong class="text-white"><?= htmlspecialchars($_SESSION['first_name']) ?></strong>
            <a href="logout.php" class="btn btn-light btn-sm ms-3"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="mb-4">Academic Calendar</h2>

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
        <div class="card-header bg-primary text-white">Add New Event</div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="add_event" value="1">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="title" placeholder="Event Title" required>
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" name="event_date" required>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="event_type" required>
                            <option value="">Select Type</option>
                            <option value="Holiday">Holiday</option>
                            <option value="Exam">Exam</option>
                            <option value="Deadline">Deadline</option>
                            <option value="Lecture">Lecture</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success w-100">Add</button>
                    </div>
                </div>
                <div class="mt-3">
                    <textarea class="form-control" name="description" placeholder="Description"></textarea>
                </div>
            </form>
        </div>
    </div>

    <!-- Events Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white">Existing Events</div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Event Date</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($event = $events->fetch_assoc()): ?>
                        <tr>
                            <td><?= $event['event_id'] ?></td>
                            <td><?= htmlspecialchars($event['title']) ?></td>
                            <td><?= $event['event_date'] ?></td>
                            <td><?= $event['event_type'] ?></td>
                            <td><?= htmlspecialchars($event['description']) ?></td>
                            <td>
                                <a href="edit_event.php?id=<?= $event['event_id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                <a href="delete_event.php?id=<?= $event['event_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash-alt"></i> Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
