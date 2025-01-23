<?php
include("config.php");
include("Authenticate.php");
include("Navigation.php");

$UserID = $_SESSION['UserID'];

// Step 1: Check for properties expiring within 1 month and create notifications
$currentDate = new DateTime();
$oneMonthLater = (clone $currentDate)->modify('+1 month');

// Query to find bookings expiring within 1 month
$expiringPropertiesQuery = "
    SELECT b.BookingID, b.MoveOutDate, p.PropertyAddress
    FROM booking b
    INNER JOIN property p ON b.PropertyID = p.PropertyID
    WHERE b.UserID = ? AND b.is_approved = 1 AND b.MoveOutDate BETWEEN ? AND ?
";
$stmtExpiring = $conn->prepare($expiringPropertiesQuery);

if (!$stmtExpiring) {
    die("Error preparing statement for expiring properties: " . $conn->error);
}

$startDate = $currentDate->format('Y-m-d');
$endDate = $oneMonthLater->format('Y-m-d');
$stmtExpiring->bind_param("iss", $UserID, $startDate, $endDate);
$stmtExpiring->execute();
$expiringResult = $stmtExpiring->get_result();

while ($expiringProperty = $expiringResult->fetch_assoc()) {
    $moveOutDate = $expiringProperty['MoveOutDate'];
    $propertyAddress = $expiringProperty['PropertyAddress'];

    // Check if notification for this booking already exists
    $checkNotificationQuery = "
        SELECT COUNT(*) AS existing 
        FROM notification 
        WHERE UserID = ? AND NotificationType = 'Reminder' AND NotificationMessage LIKE ? AND IsRead = 0
    ";
    $stmtCheck = $conn->prepare($checkNotificationQuery);
    $messageCheck = "%$propertyAddress%";
    $stmtCheck->bind_param("is", $UserID, $messageCheck);
    $stmtCheck->execute();
    $stmtCheck->bind_result($existingCount);
    $stmtCheck->fetch();
    $stmtCheck->close();

    // If no existing notification, insert a new one
    if ($existingCount == 0) {
        $insertNotificationQuery = "
            INSERT INTO notification (UserID, NotificationType, NotificationMessage, Date, IsRead) 
            VALUES (?, 'Reminder', ?, NOW(), 0)
        ";
        $stmtInsert = $conn->prepare($insertNotificationQuery);

        if (!$stmtInsert) {
            die("Error preparing statement for inserting notification: " . $conn->error);
        }

        $notificationMessage = "Your booking at $propertyAddress will expire on $moveOutDate. Please take necessary action.";
        $stmtInsert->bind_param("is", $UserID, $notificationMessage);
        $stmtInsert->execute();
        $stmtInsert->close();
    }
}

// Step 2: Fetch unread notification count for the badge
$unreadCountQuery = "SELECT COUNT(*) AS unread_count FROM notification WHERE UserID = ? AND IsRead = 0";
$stmtUnread = $conn->prepare($unreadCountQuery);
$stmtUnread->bind_param("i", $UserID);
$stmtUnread->execute();
$stmtUnread->bind_result($unreadCount);
$stmtUnread->fetch();
$stmtUnread->close();

// Step 3: Fetch all notifications, including booking expiration
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
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="wrapper">
        <main class="content">
            <div class="container my-5">
                <!-- Notifications Header -->
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($notification = $result->fetch_assoc()): ?>
                        <div class="card notification-card mb-3 shadow-sm" id="notification-<?php echo $notification['NotificationID']; ?>">
                            <div class="card-header bg-primary text-white">
                                <strong><?php echo htmlspecialchars($notification['NotificationType']); ?></strong>
                            </div>
                            <div class="card-body">
                                <p class="mb-2">
                                    <?php echo nl2br(htmlspecialchars($notification['NotificationMessage'])); ?>
                                </p>
                                <small class="text-muted">
                                    Received on: <?php echo date('Y-m-d H:i', strtotime($notification['Date'])); ?>
                                </small>
                            </div>
                            <div class="card-footer d-flex justify-content-between align-items-center">
                                <?php if (!$notification['IsRead']): ?>
                                    <form class="form-mark-read" action="MarkAsRead.php" method="post">
                                        <input type="hidden" name="NotificationID" value="<?php echo htmlspecialchars($notification['NotificationID']); ?>">
                                        <button type="submit" class="btn btn-sm btn-success">Mark as Read</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted text-center">You have no notifications at the moment.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php include 'Footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const markAsReadForms = document.querySelectorAll('.form-mark-read');

            markAsReadForms.forEach(form => {
                form.addEventListener('submit', event => {
                    event.preventDefault();

                    const formData = new FormData(form);

                    fetch('MarkAsRead.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const notificationCard = form.closest('.notification-card');
                            if (notificationCard) {
                                notificationCard.remove();
                            }

                            const badge = document.getElementById('notificationBadge');
                            if (badge && data.unreadCount !== undefined) {
                                badge.textContent = data.unreadCount;
                                badge.style.display = data.unreadCount > 0 ? 'inline' : 'none';
                            }
                        } else {
                            console.error('Error marking notification as read:', data.message);
                        }
                    })
                    .catch(error => console.error('Error marking notification as read:', error));
                });
            });
        });

    </script>
</body>
</html>
