<?php
require_once("Navigation.php");

// Check for error query parameter and prepare error message
$error_message = 'An unknown error occurred.';

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalid_role':
            $error_message = 'Invalid user role. Please contact the administrator.';
            break;
        case 'invalid_user_type':
            $error_message = 'Invalid user type. Please contact the administrator.';
            break;
        case 'not_logged_in':
            $error_message = 'Please log in to access this page.';
            break;
        case 'incorrect_credentials':
            $error_message = 'Incorrect email or password. Please try again.';
            break;
        case 'account_does_not_exist':
            $error_message = 'Account does not exist. Please check your email.';
            break;
        case 'account_not_approved':
            $error_message = 'Your account has not been approved by the admin. Please wait.';
            break;
        case 'empty_fields':
            $error_message = 'Please fill in all the fields.';
            break;
        default:
            $error_message = 'An unknown error occurred. Please try again.';
            break;
    }
    echo "<script>
        document.addEventListener('DOMContentLoaded', () => {
            const errorModal = new bootstrap.Modal('#errorModal', {
                backdrop: 'static', // Ensures modal cannot be dismissed by clicking outside
                keyboard: false // Disables closing modal with the Esc key
            });
            errorModal.show();
        });
    </script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="css/style.css"> 
</head>

<body>
    <div class="wrapper">
        <main class="content">
            <div class="login-container">
                <h2>USER LOGIN</h2>
                <p>Welcome back! Please login to your account</p>
                
                <form action="Login_action.php" method="POST" autocomplete="on">
                    <input type="email" name="email" class="form-control" placeholder="Enter your email" autocomplete="email" required>
                    <div class="position-relative">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" autocomplete="current-password" required>
                        <button type="button" class="btn position-absolute end-0 top-0 mt-2 me-2" id="togglePassword" onclick="togglePasswordVisibility()">
                            <i class="bi bi-eye" id="passwordIcon"></i>
                        </button>
                    </div>
                    <div class="d-flex justify-content-end">
                        <a href="Forgot_password.php" class="forgot-password">Forgot Password?</a>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Continue</button>
                </form>
            </div>
        </main>

        <!-- Modal for Error Messages -->
        <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="errorModalLabel">Error</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Section -->
        <?php include 'Footer.php'; ?>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
