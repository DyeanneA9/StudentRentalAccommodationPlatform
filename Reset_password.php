<?php
include("config.php");
include("Navigation.php");

// Fetch token from the URL
$token = $_GET['token'] ?? null;

// Check if the token is provided
if (!$token) {
    die("Error: Token is missing or invalid.");
}

// Hash the token for secure lookup
$token_hash = hash("sha256", $token);

// Query to find the user by the token hash
$sql = "SELECT * FROM users WHERE reset_token_hash = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token_hash);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Validate if the token exists and is valid
if ($user === null) {
    die("Error: Token not found or invalid.");
}

// Check if the token has expired
if (strtotime($user["reset_token_expired"]) <= time()) {
    die("Error: Token has expired.");
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="wrapper">
        <main class="content">
            <div class="reset-container">
                <h3 class="text-center mb-4">Reset Password</h3>
                <form method="post" action="reset_password_action.php">
                    <input type="hidden" name="token" value="<?= $token ?>">

                    <div class="mb-4 text-start">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password" required>
                    </div>

                    <div class="mb-4 text-start">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm your password" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                </form>
            </div>
        </main>

        <?php include 'Footer.php' ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>