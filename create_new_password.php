<?php 
include("config.php");
include("Navigation.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['new_password'])) {
    $email = trim($_POST['email']);
    $new_password = trim($_POST['new_password']);

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

    // Update the user's password in the database
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashed_password, $email);

    if ($stmt->execute()) {
        // Fetch the user's ID for logging purposes
        $stmt = $conn->prepare("SELECT UserID, name FROM users WHERE email = ?");
        if(!$stmt) {
            die("Error preparing statement for fetching user: ". $conn->error);
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $user_id = $user['UserID'];
            $user_name = $user['name'];

            // Insert activity into recent_activities table
            $action = "The user $user_name successfully changed their password.";
            $stmt = $conn->prepare("INSERT INTO activities (UserID, action) VALUES (?, ?)");
            if (!$stmt) {
                die("Error preparing statement for activity log: " . $conn->error);
            }
            $stmt->bind_param("is", $user_id, $action);
            $stmt->execute();
        }

        // Redirect to login page after password reset
        header("Location: Login.php?reset=success");
        exit;
    } else {
        $error = "An error occurred. Please try again.";
    }

    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Password</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">

</head>
<body>
    <div class="wrapper">
        <main class="content">
            <div class="reset-container">
                <h2 class="text-center">Create New Password</h2>
                <br>
                <!-- Success Message -->
                <?php if (isset($_GET['reset']) && $_GET['reset'] === 'success'): ?>
                    <p class="success-message">Your password has been successfully reset!</p>
                <?php endif; ?>

                <?php if (isset($_GET['reset']) && $_GET['reset'] === 'success'): ?>
                    <script>
                        setTimeout(function() {
                            window.location.href = 'login.php';
                        }, 5000); // Redirect after 5 seconds
                    </script>
                <?php endif; ?>

                <!-- Form -->
                <form method="POST" action="">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Enter your new password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                </form>
                
                <?php if (isset($error)): ?>
                    <p class="text-danger text-center mt-3"><?php echo $error; ?></p>
                <?php endif; ?>
            </div>
        </main>

        <!-- Footer Section-->
        <?php include 'Footer.php'; ?>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>