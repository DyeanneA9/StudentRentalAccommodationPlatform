<?php
session_start();
include("config.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (!empty($email) && !empty($password)) {
        $sql = "SELECT UserID, email, password, role, user_type, is_approved FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                // User exists, fetch data and verify password
                $stmt->bind_result($UserID, $db_email, $db_password, $role, $user_type, $is_approved);
                $stmt->fetch();

                // Check if account is approved
                if ($is_approved != 1) {
                    // Account not approved, redirect
                    header("Location: Login.php?error=account_not_approved");
                    exit();
                }

                // Verify password
                if (password_verify($password, $db_password)) {
                    // Regenerate session ID to prevent fixation
                    session_regenerate_id(true);

                    // Store user data in session
                    $_SESSION['UserID'] = $UserID;
                    $_SESSION['role'] = $role;
                    $_SESSION['email'] = $db_email;
                    $_SESSION['user_type'] = $user_type;

                    // Role-based redirection
                    if ($role === 'user') {
                        header("Location: Dashboard.php");
                    } elseif ($role === 'admin') {
                        header("Location: AdminDashboard.php");
                    } elseif ($role === 'super_admin') {
                        header("Location: SuperAdminDashboard.php");
                    } else {
                        header("Location: Login.php?error=invalid_role");
                    }
                    exit();
                } else {
                    // Incorrect password, redirect back with error
                    header("Location: Login.php?error=incorrect_credentials");
                    exit();
                }
            } else {
                // User not found, redirect with specific error
                header("Location: Login.php?error=account_does_not_exist");
                exit();
            }

            $stmt->close();
        } else {
            // Database connection error, redirect with error
            header("Location: Login.php?error=database_error");
            exit();
        }
    } else {
        // Fields are empty, redirect with error
        header("Location: Login.php?error=empty_fields");
        exit();
    }
}
?>
