<?php
session_start();

if (!isset($_SESSION['UserID'])) {
    header("Location: Login.php");
    exit();
}

?>
