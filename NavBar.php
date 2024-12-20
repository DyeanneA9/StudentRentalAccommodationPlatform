<?php
$role = $_SESSION['role'] ?? null;
$userType = $_SESSION['user_type'] ?? null;

if ($role === 'admin') {
    include 'NavigationAdmin.php';
} elseif ($role === 'user') {
    if ($userType === 'student') {
        include 'NavigationStudent.php';
    } elseif ($userType === 'homeowner') {
        include 'NavigationHomeowner.php';
    } else {
        echo "<script>alert('Invalid user type. Please contact the administrator.');</script>";
    }
} elseif ($role === 'super_admin') {
    include 'NavigationSuperAdmin.php';
} else {
    include 'NavigationBar.php';
}

?>
