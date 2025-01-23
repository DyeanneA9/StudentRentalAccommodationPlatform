<?php
session_start();
include("config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['UserID']) || $_SESSION['user_type'] !== 'student') {
        header("Location: Login.php?error=Unauthorized access.");
        exit();
    }

    $userID = intval($_SESSION['UserID']);

    $target_dir = "uploads/" . $userID . "/";
    if (!is_dir($target_dir)) {
        if (!mkdir($target_dir, 0777, true)) {
            header("Location: StudentProfile.php?error=Error creating directory for uploads.");
            exit();
        }
    }

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        $fileName = uniqid() . "-" . basename($_FILES['profile_picture']['name']);
        $target_file = $target_dir . $fileName;
        $normalized_path = str_replace("\\", "/", $target_file);

        $allowedfileExtensions = ['jpg', 'jpeg', 'png'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileExtension, $allowedfileExtensions)) {
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $normalized_path)) {
                $relativePath = str_replace("./", "", $normalized_path);

                $sql = "UPDATE users SET profile_picture = ? WHERE UserID = ?";
                $stmt = $conn->prepare($sql);

                if ($stmt) {
                    $stmt->bind_param("si", $relativePath, $userID);
                    if ($stmt->execute()) {
                        header("Location: StudentProfile.php?success=Profile picture updated successfully.");
                        exit();
                    } else {
                        header("Location: StudentProfile.php?error=Error updating profile picture.");
                        exit();
                    }
                } else {
                    header("Location: StudentProfile.php?error=Error preparing SQL statement.");
                    exit();
                }
            } else {
                header("Location: StudentProfile.php?error=Error moving the uploaded file.");
                exit();
            }
        } else {
            header("Location: StudentProfile.php?error=Invalid file extension. Only jpg, jpeg, and png are allowed.");
            exit();
        }
    } else {
        header("Location: StudentProfile.php?error=No file uploaded or an error occurred.");
        exit();
    }
}
?>
