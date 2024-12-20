<?php
include("Auth.php");
include("config.php");
include("NavBar.php");

$UserID = $_SESSION['UserID']; // Logged-in user's ID

// Fetch notifications for the logged-in user
$query = "SELECT NotificationID, NotificationType, NotificationMessage, Date, IsRead 
          FROM notification
          WHERE UserID = ? 
          ORDER BY Date DESC";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("i", $UserID);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="wrapper">
        <main class="content">
            <div class="container my-5">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($notification = $result->fetch_assoc()): ?>
                        <div class="card notification-card">
                            <!-- Header Section -->
                            <div class="notification-header">
                                <span>
                                    <?php echo htmlspecialchars($notification['NotificationType']); ?>
                                </span>
                            </div>

                            <!-- Body Section -->
                            <div class="notification-body">
                                <p class="mb-1">
                                    <?php echo nl2br(htmlspecialchars($notification['NotificationMessage'])); ?>
                                </p>
                                <small class="text-muted">
                                    Received on: <?php echo date('Y-m-d H:i', strtotime($notification['Date'])); ?>
                                </small>
                            </div>

                            <!-- Footer Section -->
                            <div class="notification-footer">
                                <?php if (!$notification['IsRead']): ?>
                                    <form action="MarkAsRead.php" method="post" style="margin: 0;">
                                        <input type="hidden" name="NotificationID" value="<?php echo $notification['NotificationID']; ?>">
                                        <button type="submit" class="btn btn-mark-read">Mark as Read</button>
                                    </form>
                                <?php endif; ?>
                                <form action="DeleteNotification.php" method="post" style="margin: 0;">
                                    <input type="hidden" name="NotificationID" value="<?php echo $notification['NotificationID']; ?>">
                                    <button type="submit" class="btn btn-delete">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted text-center">You have no notifications at this time.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Footer Section -->
    <?php include 'Footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
