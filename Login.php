<?php
require_once("Navigation.php");

$error_message = 'An unknown error occurred.';

if (isset($_GET['error'])) {
    // Set error message based on error type
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
            $error_message = 'Account does not exist. Please check your email or register.';
            break;
        case 'account_not_approved':
            $error_message = 'Your account has not been approved by the admin. Please try again later.';
            break;
        case 'empty_fields':
            $error_message = 'Please fill in all the fields.';
            break;
        default:
            $error_message = 'An unknown error occurred. Please try again.';
            break;
    }
    
    // Display error modal
    echo "
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const errorModal = new bootstrap.Modal(document.getElementById('errorModal'), {
                backdrop: 'static', // Ensures modal cannot be dismissed by clicking outside
                keyboard: false // Disables closing modal with the Esc key
            });
            errorModal.show();

            // Handle close button functionality
            document.querySelector('.btn-close').addEventListener('click', () => errorModal.hide());
            document.querySelector('.btn-secondary').addEventListener('click', () => errorModal.hide());
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="wrapper">
        <main class="content">
            <div class="login-container">
                <h2>USER LOGIN</h2>
                <p>Welcome back! Please login to your account</p>
                
                <form action="Login_action.php" method="POST" autocomplete="on">
                    <!-- Email input -->
                    <input type="email" name="email" class="form-control" placeholder="Enter your email" autocomplete="email" required>

                    <!-- Password input with toggle visibility -->
                    <div class="col-md-14">
                        <div class="input-group">
                            <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" autocomplete="current-password" required>
                            <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Forgot password link -->
                    <div class="d-flex justify-content-end">
                        <a href="Forgot_password.php" class="forgot-password">Forgot Password?</a>
                    </div>

                    <!-- Submit button -->
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
        <?php include 'Footer.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
    <script>
        document.getElementById("togglePassword").addEventListener("click", function () {
            const passwordField = document.getElementById("password");
            const passwordFieldType = passwordField.getAttribute("type");
            if (passwordFieldType === "password") {
                passwordField.setAttribute("type", "text");
                this.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                passwordField.setAttribute("type", "password");
                this.innerHTML = '<i class="fas fa-eye"></i>';
            }
        });
    </script>
</body>
</html>
