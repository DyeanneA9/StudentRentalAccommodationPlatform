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
            $result = $stmt->get_result();

            if ($result && $result->num_rows == 1) {
                $row = $result->fetch_assoc();

                // Check if account is approved
                if ($row['is_approved'] == 0) {
                    // Account is not approved
                    header("Location: Login.php?error=account_not_approved");
                    exit();
                }

                if (password_verify($password, $row['password'])) {
                    // Regenerate session ID to prevent fixation
                    session_regenerate_id(true);

                    // Store user data in session
                    $_SESSION['UserID'] = $row['UserID'];
                    $_SESSION['role'] = $row['role'];
                    $_SESSION['email'] = $row['email'];
                    $_SESSION['user_type'] = $row['user_type'];

                    // Role-based redirection
                    if ($row['role'] === 'user') {
                        header("Location: Dashboard.php");
                    } elseif ($row['role'] === 'admin') {
                        header("Location: AdminDashboard.php");
                    } elseif ($row['role'] === 'super_admin') {
                        header("Location: SuperAdminDashboard.php");
                    } else {
                        header("Location: Login.php?error=invalid_role");
                    }
                    exit();
                    
                } else {
                    // Redirect back to login with error for incorrect password
                    header("Location: Login.php?error=incorrect_credentials");
                    exit();
                }
            } else {
                // Redirect back to login if user is not found
                header("Location: Login.php?error=user_not_found");
                exit();
            }
            $stmt->close();
        } else {
            // Handle database connection errors
            header("Location: Login.php?error=database_error");
            exit();
        }
    } else {
        // Redirect to login if fields are empty
        header("Location: Login.php?error=empty_fields");
        exit();
    }
}
?>
