<?php
$host = 'localhost';      // Laragon default
$db   = 'school_app';      // Your database name
$user = 'root';           // Default Laragon MySQL user
$pass = '';               // Default password
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
define('GEMINI_API_KEY', 'AIzaSyByjgQWnd8YDmPOepqKEQqZOD1JMCMnvYc');
?>
