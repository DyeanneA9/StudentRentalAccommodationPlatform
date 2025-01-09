<?php
/*if (!isset($_SESSION['UserID']) || !isset($_SESSION['role'])) {
    var_dump($_SESSION);
    // Redirect only if the current page is not login.php or another public page
    $publicPages = ['Login.php', 'Register.php']; // Add other public pages here if needed
    if (!in_array(basename($_SERVER['PHP_SELF']), $publicPages)) {
        header("Location: Login.php");
        exit();
    }
}*/

session_start();

if (!isset($_SESSION['UserID'])) {
    header("Location: Login.php?error=not_authorized");
    exit();
}

?>
