<?php
session_start();
include("config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['UserID'])) {
        header("Location: Login.php?error=User not logged in.");
        exit();
    }

    $userID = intval($_SESSION['UserID']);

    // Fetch user type
    $query = "SELECT user_type FROM users WHERE UserID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        header("Location: Login.php?error=User not found.");
        exit();
    }

    $userType = $user['user_type'];

    $target_dir = "uploads/" . $userID . "/";
    if (!is_dir($target_dir)) {
        if (!mkdir($target_dir, 0777, true)) {
            $error_message = urlencode("Error creating directory for uploads.");
            redirectToProfile($userType, $error_message);
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

                if (!$stmt) {
                    $error_message = urlencode("Error preparing SQL statement: " . $conn->error);
                    redirectToProfile($userType, $error_message);
                }

                $stmt->bind_param("si", $relativePath, $userID);

                if ($stmt->execute()) {
                    $success_message = urlencode("Profile picture updated successfully.");
                    redirectToProfile($userType, null, $success_message);
                } else {
                    $error_message = urlencode("Error updating profile picture: " . $stmt->error);
                    redirectToProfile($userType, $error_message);
                }
            } else {
                $error_message = urlencode("Error moving the uploaded file.");
                redirectToProfile($userType, $error_message);
            }
        } else {
            $error_message = urlencode("Invalid file extension. Only jpg, jpeg, and png are allowed.");
            redirectToProfile($userType, $error_message);
        }
    } else {
        $error_message = urlencode("No file uploaded or an error occurred.");
        redirectToProfile($userType, $error_message);
    }
}

function redirectToProfile($userType, $error_message = null, $success_message = null) {
    $redirectUrl = $userType === 'student' ? "StudentProfile.php" : "HomeownerProfile.php";
    $redirectUrl .= "?";
    if ($error_message) {
        $redirectUrl .= "error=" . $error_message;
    } elseif ($success_message) {
        $redirectUrl .= "success=" . $success_message;
    }
    header("Location: " . $redirectUrl);
    exit();
}
?>
