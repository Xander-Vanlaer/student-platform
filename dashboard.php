<?php
session_start();
require_once 'config.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_role = $_SESSION['role'];
$first_name = $_SESSION['first_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title data-en="Dashboard" data-lt="Prietaisų skydelis">Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="#">🎓 <span data-en="LMS" data-lt="LMS">LMS</span></a>
        <div class="d-flex align-items-center">
            <span class="text-white me-3" data-en="Hello" data-lt="Sveiki">Hello</span> <strong class="text-white"><?= htmlspecialchars($first_name) ?></strong>

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
    <h2 class="mb-4" data-en="Dashboard" data-lt="Prietaisų skydelis">Dashboard</h2>

    <?php if($user_role === 'Admin'): ?>
    <!-- Admin Panels -->
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <div class="col">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <h5 class="card-title" data-en="Manage Users" data-lt="Vartotojų valdymas">Manage Users</h5>
                    <p class="card-text" data-en="Create and assign students and teachers." data-lt="Kurti ir priskirti studentus bei mokytojus."></p>
                    <a href="users.php" class="btn btn-primary" data-en="Go" data-lt="Eiti">Go</a>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <i class="fas fa-book fa-2x mb-2"></i>
                    <h5 class="card-title" data-en="Courses" data-lt="Kursai">Courses</h5>
                    <p class="card-text" data-en="View and manage courses and programs." data-lt="Peržiūrėti ir valdyti kursus bei programas."></p>
                    <a href="courses.php" class="btn btn-primary" data-en="Go" data-lt="Eiti">Go</a>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                    <h5 class="card-title" data-en="Academic Calendar" data-lt="Akademinis kalendorius">Academic Calendar</h5>
                    <p class="card-text" data-en="Manage holidays, exams, and deadlines." data-lt="Valdyti šventes, egzaminus ir terminus."></p>
                    <a href="calendar.php" class="btn btn-primary" data-en="Go" data-lt="Eiti">Go</a>
                </div>
            </div>
        </div>

        <!-- New Admin Panel: School Overview -->
        <div class="col">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <i class="fas fa-school fa-2x mb-2"></i>
                    <h5 class="card-title" data-en="School Overview" data-lt="Mokyklos apžvalga">School Overview</h5>
                    <p class="card-text" data-en="View all classes, programs, courses, and student counts." data-lt="Peržiūrėti visas klases, programas, kursus ir studentų skaičių."></p>
                    <a href="school_overview.php" class="btn btn-primary" data-en="Go" data-lt="Eiti">Go</a>
                </div>
            </div>
        </div>
    </div>


    <?php elseif($user_role === 'Teacher'): ?>
    
    <div class="row row-cols-1 row-cols-md-3 g-4">

        <!-- My Courses -->
        <div class="col">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <i class="fas fa-book fa-2x mb-2"></i>
                    <h5 class="card-title" data-en="My Courses" data-lt="Mano kursai">My Courses</h5>
                    <p class="card-text" data-en="View and manage your classes, documents and attendance."
                       data-lt="Peržiūrėkite ir valdykite savo pamokas, dokumentus ir lankomumą."></p>
                    <a href="my_courses.php" class="btn btn-success" data-en="Go" data-lt="Eiti">Go</a>
                </div>
            </div>
        </div>

        <!-- Teacher Agenda -->
        <div class="col">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                    <h5 class="card-title" data-en="My Agenda" data-lt="Mano darbotvarkė">My Agenda</h5>
                    <p class="card-text"
                       data-en="View your upcoming lessons, exams, deadlines and events."
                       data-lt="Peržiūrėkite savo pamokų grafiką, egzaminus, terminus ir įvykius."></p>
                    <a href="calendar_teacher.php" class="btn btn-success" data-en="Open" data-lt="Atidaryti">Open</a>
                </div>
            </div>
        </div>

        <!-- Plan Exams & Lessons -->
       <div class="col">
    <div class="card text-center shadow-sm">
        <div class="card-body">
            <i class="fas fa-clipboard-check fa-2x mb-2"></i>
            <h5 class="card-title" data-en=" Lessons" data-lt="Planuoti egzaminus ir pamokas">
               Lessons
            </h5>
            <p class="card-text"
               data-en="pdf fils."
               data-lt="Kurti egzaminus, planuoti pamokas ir valdyti tvarkaraštį."></p>
            <a href="course_materials.php" class="btn btn-success" data-en="Manage" data-lt="Tvarkyti">Manage</a>
        </div>
    </div>
</div>
<!-- Take Attendance -->
<div class="col">
    <div class="card text-center shadow-sm">
        <div class="card-body">
            <i class="fas fa-user-check fa-2x mb-2"></i>
            <h5 class="card-title" data-en="Take Attendance" data-lt="Fiksuoti lankomumą">Take Attendance</h5>
            <p class="card-text" data-en="Mark student attendance for your courses." 
               data-lt="Pažymėti studentų lankomumą savo kursuose."></p>
            <a href="attendance.php" class="btn btn-success" data-en="Go" data-lt="Eiti">Go</a>
        </div>
    </div>
</div>


        <!-- Chat -->
        <div class="col">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <i class="fas fa-comments fa-2x mb-2"></i>
                    <h5 class="card-title" data-en="Chat" data-lt="Pokalbis">Chat</h5>
                    <p class="card-text" data-en="Communicate with your students."
                       data-lt="Bendraukite su savo studentais."></p>
                    <a href="chat.php" class="btn btn-success" data-en="Go" data-lt="Eiti">Go</a>
                </div>
            </div>
        </div>

    </div>

    <?php elseif($user_role === 'Student'): ?>
        <!-- Student Panels -->
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <div class="col">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-book fa-2x mb-2"></i>
                        <h5 class="card-title" data-en="My Courses" data-lt="Mano kursai">My Courses</h5>
                        <p class="card-text" data-en="View your enrolled courses and documents." data-lt="Peržiūrėkite savo užsiregistruotus kursus ir dokumentus."></p>
                        <a href="my_courses.php" class="btn btn-info" data-en="Go" data-lt="Eiti">Go</a>
                    </div>
                </div>
            </div>
            <!-- Chat -->
<div class="col">
    <div class="card text-center shadow-sm">
        <div class="card-body">
            <i class="fas fa-comments fa-2x mb-2"></i>
            <?php
// Count unread messages for this student
$student_id = $_SESSION['user_id'];
$unread_count = 0;

$stmt = $conn->prepare("
    SELECT COUNT(*) as unread 
    FROM Chat_Messages cm
    JOIN Chat_Sessions cs ON cm.session_id = cs.session_id
    WHERE cm.sender_id != ? 
      AND cm.is_read = 0
      AND (cs.user1_id = ? OR cs.user2_id = ?)
");
$stmt->bind_param("iii", $student_id, $student_id, $student_id);
$stmt->execute();
$res = $stmt->get_result();
if($row = $res->fetch_assoc()){
    $unread_count = intval($row['unread']);
}
?>

            <h5 class="card-title" data-en="Chat" data-lt="Pokalbis">Chat
                <?php if($unread_count > 0): ?>
                    <span class="badge bg-danger">+<?= $unread_count ?></span>
                <?php endif; ?>
            </h5>
            <p class="card-text" data-en="Communicate with your teachers."
               data-lt="Bendraukite su savo mokytojais."></p>
            <a href="chat.php" class="btn btn-info" data-en="Go" data-lt="Eiti">Go</a>
        </div>
    </div>
</div>


            <div class="col">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                        <h5 class="card-title" data-en="Academic Calendar" data-lt="Akademinis kalendorius">Academic Calendar</h5>
                        <p class="card-text" data-en="View academic events and deadlines." data-lt="Peržiūrėkite akademinius renginius ir terminus."></p>
                        <a href="calendar.php" class="btn btn-info" data-en="Go" data-lt="Eiti">Go</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Dual-language script -->
<script>
const LANGUAGE_KEY = 'lms_language';

// Set language and save preference
function setLanguage(lang) {
    localStorage.setItem(LANGUAGE_KEY, lang);
    applyLanguage(lang);
}

// Apply the language to all elements
function applyLanguage(lang) {
    document.querySelectorAll('[data-en]').forEach(element => {
        const translation = element.getAttribute(`data-${lang}`);
        if (translation) element.textContent = translation;
    });

    // Update the dropdown display
    document.getElementById('currentLangDisplay').textContent = lang.toUpperCase();
    document.documentElement.setAttribute('lang', lang);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    const savedLang = localStorage.getItem(LANGUAGE_KEY) || 'en';
    applyLanguage(savedLang);
});
</script>

</body>
</html>
