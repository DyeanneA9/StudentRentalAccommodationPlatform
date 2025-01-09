<?php
include("config.php");
include("NavBar.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['security_answer'])) {
    $email = trim($_POST['email']);
    $security_answer = trim($_POST['security_answer']);

    // Fetch the stored security answer hash, alternative question, and alternative answer from the database
    $stmt = $conn->prepare("SELECT security_answer, alternative_question, alternative_answer FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $stored_hash = $user['security_answer'];
        $alternative_question = $user['alternative_question'];
        $stored_alt_answer = $user['alternative_answer']; // The stored answer for the alternative question

        // Verify the entered answer with the stored hash for the primary question
        if (password_verify($security_answer, $stored_hash)) {
            // If the answer is correct, redirect to the create new password page with the email as a parameter
            header("Location: create_new_password.php?email=" . urlencode($email));
            exit;
        } else {
            // If the answer is incorrect, show alternative question
            $error = "Incorrect answer. Please answer the alternative question.";
            $show_alt = true;
        }
    } else {
        $error = "No account found with that email.";
        $show_alt = false;
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
                <!-- Display Error Message if Incorrect Answer -->
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <!-- Display Alternative Security Question if Answer is Incorrect -->
                <?php if (isset($show_alt) && $show_alt): ?>
                    <!-- Alternative Security Question Form -->
                    <form method="POST" action="validate_security.php">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                        <div class="mb-3">
                            <label for="alt_security_answer" class="form-label"><?php echo htmlspecialchars($alternative_question); ?></label>
                            <input type="text" name="alt_security_answer" id="alt_security_answer" class="form-control" placeholder="Enter your answer" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Submit Answer</button>
                    </form>
                <?php else: ?>
                    <!-- Original Security Question Form -->
                    <form method="POST" action="validate_security.php">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                        <div class="mb-3">
                            <label for="security_answer" class="form-label">What is the name of your first pet?</label>
                            <input type="text" name="security_answer" id="security_answer" class="form-control" placeholder="Enter your answer" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Submit Answer</button>
                    </form>
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
