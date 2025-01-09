<?php
require_once("Session.php");
require_once("config.php");

// Only include Authenticate.php for non-public pages
$publicPages = ['Login.php', 'Register.php'];
if (!in_array(basename($_SERVER['PHP_SELF']), $publicPages)) {
    require_once("Authenticate.php");
}

$currentPage = basename($_SERVER['PHP_SELF']);

$profilePicture = 'image/default_pro_pic.png'; 

if (isset($_SESSION['UserID'])) {
    $UserID = $_SESSION['UserID'];

    $query = "SELECT profile_picture FROM users WHERE UserID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $UserID);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && !empty($user['profile_picture'])) {
        $profilePicture = $user['profile_picture'];
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Rental Accommodation</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand">
                <img src="logo/Logo.png" alt="SRAP Logo" class="navbar-logo">
            </a>

            <!-- Toggle button (for mobile view) -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <?php
                    // Navigation logic based on login status
                    if (!isset($_SESSION['UserID'])) {
                        // User is not logged in, show default navigation
                        echo '<li class="nav-item"><a class="nav-link' . ($currentPage === 'Register.php' ? ' active' : '') . '" href="Register.php">Register</a></li>';
                        echo '<li class="nav-item"><a class="nav-link' . ($currentPage === 'Login.php' ? ' active' : '') . '" href="Login.php">Login</a></li>';
                    } elseif ($_SESSION['role'] === 'user' && $_SESSION['user_type'] === 'student') {
                        // Logged-in student
                        echo '<li class="nav-item"><a class="nav-link" href="Dashboard.php">Home</a></li>';
                        echo '<li class="nav-item"><a class="nav-link" href="SaveProperty.php">Saved Properties</a></li>';
                        echo '<li class="nav-item"><a class="nav-link" href="Notification.php">Notifications</a></li>';
                        echo '<li class="nav-item"><a class="nav-link" href="Logout.php">Logout</a></li>';
                        echo '<li class="nav-item">
                                <a href="StudentProfile.php">
                                    <img src="' . htmlspecialchars($profilePicture, ENT_QUOTES, 'UTF-8') . '" 
                                        alt="Profile Picture" 
                                        class="profile-picture">
                                </a>
                            </li>';
                    } elseif ($_SESSION['role'] === 'user' && $_SESSION['user_type'] === 'homeowner') {
                        // Logged-in homeowner
                        echo '<li class="nav-item"><a class="nav-link" href="Dashboard.php">Home</a></li>';
                        echo '<li class="nav-item"><a class="nav-link" href="AddProperty.php">Add Property</a></li>';
                        echo '<li class="nav-item"><a class="nav-link" href="Notification.php">Notifications</a></li>';
                        echo '<li class="nav-item"><a class="nav-link" href="Logout.php">Logout</a></li>';
                        echo '<li class="nav-item">
                                <a href="HomeownerProfile.php">
                                    <img src="' . htmlspecialchars($profilePicture, ENT_QUOTES, 'UTF-8') . '" 
                                        alt="Profile Picture" 
                                        class="profile-picture">
                                </a>
                            </li>';                    
                        } elseif ($_SESSION['role'] === 'super_admin') {
                        // Logged-in super admin
                        echo '<li class="nav-item"><a class="nav-link" href="SuperAdminDashboard.php">Home</a></li>';
                        echo '<li class="nav-item"><a class="nav-link" href="ManageUsers.php">Manage Users</a></li>';
                        echo '<li class="nav-item"><a class="nav-link" href="PropertyApproval.php">Manage Property</a></li>';
                        echo '<li class="nav-item"><a class="nav-link" href="Logout.php">Logout</a></li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
