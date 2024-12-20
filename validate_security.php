<?php
include("config.php");
include ("NavBar.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['security_answer'])) {
    $email = trim($_POST['email']);
    $security_answer = trim($_POST['security_answer']);

    // Fetch the stored security answer hash for the email
    $stmt = $conn->prepare("SELECT security_answer FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $stored_hash = $user['security_answer'];

        // Verify the entered answer with the stored hash
        if (password_verify($security_answer, $stored_hash)) {
            // Redirect to the create new password page with the email as a parameter
            header("Location: create_new_password.php?email=" . urlencode($email));
            exit;
        } else {
            $error = "Incorrect answer. Please try again.";
        }
    } else {
        $error = "No account found with that email.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Security Question</title>

    <!-- Bootstrap CSS-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="wrapper">
        <main class="content">
            <div class="container mt-5">
                <h3>Validate Security Answer</h3>

                <?php if (isset($show_alt) && $show_alt): ?>
                    <!-- Alternative Security Question -->
                    <form method="POST" action="validate_security.php">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                        <div class="mb-3">
                            <label for="alt_security_answer" class="form-label"><?php echo htmlspecialchars($alt_question); ?></label>
                            <input type="text" name="alt_security_answer" id="alt_security_answer" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Answer</button>
                    </form>
                <?php else: ?>
                    <p class="text-danger"><?php echo $error; ?></p>
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
