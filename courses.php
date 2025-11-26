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

// Handle Add/Edit/Delete Course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    $action = $_POST['action'];
    $course_name = trim($_POST['course_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $program_id  = intval($_POST['program_id'] ?? 0);
    $teacher_id  = intval($_POST['teacher_id'] ?? 0);
    $course_id   = intval($_POST['course_id'] ?? 0);

    if ($action === 'add') {

        if (empty($course_name) || empty($program_id)) {
            $errors[] = "Course name and program are required.";
        } else {
            $stmt = $conn->prepare("
                INSERT INTO Courses (course_name, description, program_id, teacher_id)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("ssii", $course_name, $description, $program_id, $teacher_id);

            if ($stmt->execute()) {
                $success = "Course added successfully!";
            } else {
                $errors[] = "Error adding course. It may already exist.";
            }
        }

    } elseif ($action === 'edit') {

        if (empty($course_name) || empty($program_id) || empty($course_id)) {
            $errors[] = "All fields are required for editing.";
        } else {
            $stmt = $conn->prepare("
                UPDATE Courses 
                SET course_name=?, description=?, program_id=?, teacher_id=? 
                WHERE course_id=?
            ");
            $stmt->bind_param("ssiii", $course_name, $description, $program_id, $teacher_id, $course_id);

            if ($stmt->execute()) {
                $success = "Course updated successfully!";
            } else {
                $errors[] = "Error updating course.";
            }
        }

    } elseif ($action === 'delete') {

        if (!empty($course_id)) {
            $stmt = $conn->prepare("DELETE FROM Courses WHERE course_id=?");
            $stmt->bind_param("i", $course_id);

            if ($stmt->execute()) {
                $success = "Course deleted successfully!";
            } else {
                $errors[] = "Error deleting course.";
            }
        }
    }
}

// Fetch all courses with teacher + program
$courses = $conn->query("
    SELECT 
        c.course_id, 
        c.course_name, 
        c.description, 
        c.program_id,
        p.program_name,
        c.teacher_id,
        CONCAT(u.first_name, ' ', u.last_name) AS teacher_name
    FROM Courses c
    LEFT JOIN Programs p ON c.program_id = p.program_id
    LEFT JOIN Users u ON u.user_id = c.teacher_id
    ORDER BY c.course_id ASC
");

// Fetch programs
$programs = $conn->query("SELECT program_id, program_name FROM Programs ORDER BY program_name ASC");

// Fetch teachers (role_id = 2)
$teachers = $conn->query("
    SELECT user_id, first_name, last_name 
    FROM Users 
    WHERE role_id = 2
    ORDER BY first_name ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Courses</title>
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
            <a href="logout.php" class="btn btn-light btn-sm ms-3">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="mb-4">Manage Courses</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $e) echo "<p>$e</p>"; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <!-- Add/Edit Form -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">Add / Edit Course</div>
        <div class="card-body">

            <form method="POST" id="courseForm">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="course_id" id="course_id" value="">

                <div class="row g-3">

                    <div class="col-md-3">
                        <input type="text" class="form-control" name="course_name" id="course_name" placeholder="Course Name" required>
                    </div>

                    <div class="col-md-3">
                        <input type="text" class="form-control" name="description" id="description" placeholder="Description">
                    </div>

                    <div class="col-md-3">
                        <select class="form-select" name="program_id" id="program_id" required>
                            <option value="">Select Program</option>
                            <?php while ($p = $programs->fetch_assoc()): ?>
                                <option value="<?= $p['program_id'] ?>">
                                    <?= htmlspecialchars($p['program_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <select class="form-select" name="teacher_id" id="teacher_id">
                            <option value="">Select Teacher</option>
                            <?php while ($t = $teachers->fetch_assoc()): ?>
                                <option value="<?= $t['user_id'] ?>">
                                    <?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-1 d-grid">
                        <button type="submit" class="btn btn-success" id="submitBtn">Add</button>
                    </div>

                </div>
            </form>

        </div>
    </div>

    <!-- Courses Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white">Existing Courses</div>
        <div class="card-body table-responsive">

            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Course Name</th>
                        <th>Description</th>
                        <th>Program</th>
                        <th>Teacher</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php while ($c = $courses->fetch_assoc()): ?>
                    <tr>
                        <td><?= $c['course_id'] ?></td>
                        <td><?= htmlspecialchars($c['course_name']) ?></td>
                        <td><?= htmlspecialchars($c['description']) ?></td>
                        <td><?= htmlspecialchars($c['program_name']) ?></td>
                        <td><?= htmlspecialchars($c['teacher_name'] ?? '—') ?></td>

                        <td>

                            <!-- Edit -->
                            <button 
                                class="btn btn-warning btn-sm editBtn"
                                data-id="<?= $c['course_id'] ?>"
                                data-name="<?= htmlspecialchars($c['course_name']) ?>"
                                data-desc="<?= htmlspecialchars($c['description']) ?>"
                                data-program="<?= $c['program_id'] ?>"
                                data-teacher="<?= $c['teacher_id'] ?>"
                            >
                                <i class="fas fa-edit"></i>
                            </button>

                            <!-- Delete -->
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="course_id" value="<?= $c['course_id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>

                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>

            </table>

        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.querySelectorAll('.editBtn').forEach(btn => {
    btn.addEventListener('click', () => {

        document.getElementById('formAction').value = 'edit';
        document.getElementById('course_id').value = btn.dataset.id;

        document.getElementById('course_name').value = btn.dataset.name;
        document.getElementById('description').value = btn.dataset.desc;

        document.getElementById('program_id').value = btn.dataset.program;
        document.getElementById('teacher_id').value = btn.dataset.teacher;

        document.getElementById('submitBtn').textContent = 'Update';
    });
});
</script>

</body>
</html>
