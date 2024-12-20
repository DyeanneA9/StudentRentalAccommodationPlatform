<?php
include ("config.php");
include ("NavBar.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    // Check if email exists in the database
    $stmt = $conn->prepare("SELECT security_question FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $security_question = $user['security_question'];
    } else {
        $error = "No account found with that email.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forget Password</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="wrapper">
        <main class="content">
            <div class="reset-container">
                <h2 class="text-center">Forgot Password</h2><br>
             
                <!-- Email Input -->
                <?php if (!isset($security_question)): ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label">Enter your email</label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Submit</button>
                    </form>
                    <?php if (isset($error)): ?>
                        <p class="text-danger text-center mt-3"><?php echo $error; ?></p>
                    <?php endif; ?>
                <?php else: ?>
                
                <!-- Security Question Display -->
                <form method="POST" action="validate_security.php">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    <div class="mb-3">
                        <label for="security_answer" class="form-label"><?php echo htmlspecialchars($security_question); ?></label>
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