<?php
session_start();
require_once 'config.php';

// Ensure user is logged in and is Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

$errors = [];
$success = "";

// Handle adding a new user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $role_id = intval($_POST['role_id']);
    $password = $_POST['password'];

    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $errors[] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO Users (first_name, last_name, email, password_hash, role_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $first_name, $last_name, $email, $hash, $role_id);
        if ($stmt->execute()) {
            $success = "User added successfully!";
        } else {
            $errors[] = "Error adding user. Email may already exist.";
        }
    }
}

// Fetch all users
$users = $conn->query("SELECT u.user_id, u.first_name, u.last_name, u.email, r.role_name 
                        FROM Users u 
                        JOIN Roles r ON u.role_id = r.role_id 
                        ORDER BY u.user_id ASC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title data-en="Manage Users" data-lt="Vartotojų valdymas">Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="#">🎓 <span data-en="LMS" data-lt="LMS">LMS</span></a>
        <div class="d-flex align-items-center">
            <span class="text-white me-3" data-en="Hello" data-lt="Sveiki">Hello</span>
            <strong class="text-white"><?= htmlspecialchars($_SESSION['first_name']) ?></strong>
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
    <h2 class="mb-4" data-en="Manage Users" data-lt="Vartotojų valdymas">Manage Users</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error) echo "<p>$error</p>"; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <!-- Add New User Form -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white" data-en="Add New User" data-lt="Pridėti naują vartotoją">Add New User</div>
        <div class="card-body">
            <form method="POST" action="">
                <input type="hidden" name="add_user" value="1">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="first_name" placeholder="First Name" required>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="last_name" placeholder="Last Name" required>
                    </div>
                    <div class="col-md-4">
                        <input type="email" class="form-control" name="email" placeholder="Email" required>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="password" id="password" placeholder="Password" required>
                            <button class="btn btn-secondary" type="button" id="generatePasswordBtn" data-en="Generate" data-lt="Sugeneruoti">Generate</button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="role_id" required>
                            <option value="">Select Role</option>
                            <?php
                            $roles = $conn->query("SELECT role_id, role_name FROM Roles");
                            while($role = $roles->fetch_assoc()) {
                                echo "<option value='{$role['role_id']}'>{$role['role_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success w-100" data-en="Add User" data-lt="Pridėti vartotoją">Add User</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white" data-en="Existing Users" data-lt="Esami vartotojai">Existing Users</div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th data-en="ID" data-lt="ID">ID</th>
                        <th data-en="First Name" data-lt="Vardas">First Name</th>
                        <th data-en="Last Name" data-lt="Pavardė">Last Name</th>
                        <th data-en="Email" data-lt="El. paštas">Email</th>
                        <th data-en="Role" data-lt="Rolė">Role</th>
                        <th data-en="Actions" data-lt="Veiksmai">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?= $user['user_id'] ?></td>
                            <td><?= htmlspecialchars($user['first_name']) ?></td>
                            <td><?= htmlspecialchars($user['last_name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['role_name']) ?></td>
                            <td>
                                <a href="edit_user.php?id=<?= $user['user_id'] ?>" class="btn btn-sm btn-warning" data-en="Edit" data-lt="Redaguoti"><i class="fas fa-edit"></i></a>
                                <a href="delete_user.php?id=<?= $user['user_id'] ?>" class="btn btn-sm btn-danger" data-en="Delete" data-lt="Ištrinti" onclick="return confirm('Are you sure?')"><i class="fas fa-trash-alt"></i></a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
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
    document.querySelectorAll('[data-en]').forEach(element => {
        const translation = element.getAttribute(`data-${lang}`);
        if (translation) element.textContent = translation;
    });
    document.getElementById('currentLangDisplay').textContent = lang.toUpperCase();
    document.documentElement.setAttribute('lang', lang);
}
document.addEventListener('DOMContentLoaded', () => {
    const savedLang = localStorage.getItem(LANGUAGE_KEY) || 'en';
    applyLanguage(savedLang);
});

// Password generator
function generatePassword(length = 12) {
    const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+~`|}{[]:;?><,./-=";
    let password = "";
    for (let i = 0; i < length; i++) {
        password += charset[Math.floor(Math.random() * charset.length)];
    }
    return password;
}
document.getElementById('generatePasswordBtn').addEventListener('click', () => {
    document.getElementById('password').value = generatePassword();
});
</script>
</body>
</html>
