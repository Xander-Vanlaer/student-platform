<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    } else {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE Users SET password_hash=?, is_first_login=FALSE WHERE user_id=?");
        $stmt->bind_param("si", $hash, $_SESSION['user_id']);
        if ($stmt->execute()) {
            $success = "<span data-en='Password changed successfully.' data-lt='Slaptažodis sėkmingai pakeistas.'>Password changed successfully.</span> <a href='dashboard.php' data-en='Go to dashboard' data-lt='Eiti į prietaisų skydelį'>Go to dashboard</a>";
        } else {
            $errors[] = "Error updating password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title data-en="Change Password" data-lt="Pakeisti Slaptažodį">Change Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header text-center bg-warning text-dark">
                    <h4 data-en="Change Your Password" data-lt="Pakeiskite savo slaptažodį">Change Your Password</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error) echo "<p>$error</p>"; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php else: ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="new_password" class="form-label" data-en="New Password" data-lt="Naujas slaptažodis">New Password</label>
                                <input type="password" class="form-control" name="new_password" id="new_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label" data-en="Confirm Password" data-lt="Pakartokite slaptažodį">Confirm Password</label>
                                <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-warning w-100" data-en="Change Password" data-lt="Pakeisti slaptažodį">Change Password</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
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
            if (translation) {
                element.textContent = translation;
            }
        });

        const currentLangDisplay = document.getElementById('currentLangDisplay');
        if(currentLangDisplay) currentLangDisplay.textContent = lang.toUpperCase();

        document.documentElement.setAttribute('lang', lang);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const savedLang = localStorage.getItem(LANGUAGE_KEY) || 'en';
        applyLanguage(savedLang);
    });
</script>
</body>
</html>
