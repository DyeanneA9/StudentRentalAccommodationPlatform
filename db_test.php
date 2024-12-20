<?php
include('config.php');  // If you store the DB connection in config.php

// Test connection (using mysqli)
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
} else {
    echo "Database connected successfully!";
}
?>
