<?php
session_start();
require_once 'config.php';

// Only Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid course ID");
}

$course_id = intval($_GET['id']);

// Delete the course
$stmt = $conn->prepare("DELETE FROM Courses WHERE course_id=?");
$stmt->bind_param("i", $course_id);
if ($stmt->execute()) {
    header("Location: courses.php");
    exit;
} else {
    die("Error deleting course.");
}
?>
