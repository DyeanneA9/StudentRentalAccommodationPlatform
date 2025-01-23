<?php
include("config.php");
include("Navigation.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">

</head>

<body>
    <div class="wrapper">
        <main class="content">
            <div class="reset-container">
                <h2>Forgot Password</h2><br>
                <p>Please enter your email to get the reset link</p>

                <form method="POST" action="send_password_reset.php">
                    <div class="mb-3">
                        <input type="email" name="email" class="form-control" placeholder="Enter your email" autocomplete="email" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Submit</button>
                </form>
            </div>
        </main>

        <?php include 'Footer.php' ?>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageModalLabel">
                        <?php echo isset($_SESSION["modal_type"]) && $_SESSION["modal_type"] === "success" ? "Success" : "Error"; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php echo isset($_SESSION["modal_message"]) ? $_SESSION["modal_message"] : ""; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php if (isset($_SESSION["modal_message"])): ?>
            var messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
            messageModal.show();
            <?php 
            // Clear session messages after displaying the modal
            unset($_SESSION["modal_message"]);
            unset($_SESSION["modal_type"]);
            ?>
        <?php endif; ?>
    </script>
</body>
</html>
