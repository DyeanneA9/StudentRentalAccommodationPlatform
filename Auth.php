<?php
session_start();
if (!isset($_SESSION['UserID'])) {
    // Redirect to login page if the user is not logged in
    header("Location: Login.php?error=user_not_logged_in");
    exit();
}
?>
