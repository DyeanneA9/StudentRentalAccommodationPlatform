<?php
include("config.php");

$userType = $_SESSION['user_type'] ?? null;
$userID = $_SESSION['UserID'] ?? 0;

$sql = "SELECT b.BookingID, b.PropertyID, p.PropertyType, p.PropertyAddress, p.PropertyPrice, p.bankNumber, p.bankName,
               p.Photo, b.BookingType, b.MoveInDate, b.MoveOutDate, b.is_approved, 
               p.MonthlyRentPerPerson
        FROM booking b
        INNER JOIN property p ON b.PropertyID = p.PropertyID
        INNER JOIN users u ON b.UserID = u.UserID
        WHERE b.UserID = ? AND b.is_approved = 0";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing the SQL statement: " . $conn->error);
}

if (!$stmt->bind_param("i", $userID)) {
    die("Error binding parameters: " . $stmt->error);
}

if (!$stmt->execute()) {
    die("Error executing the SQL query: " . $stmt->error);
}

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ongoing Rentals</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                    // Get the first image or fallback to a default
                    $imageArray = json_decode($row['Photo'], true);
                    $firstImage = $imageArray[0] ?? 'uploads/default-image.jpg';
                    // Check if the Move-Out Date matches today's date
                    $currentDate = date('Y-m-d');
                    $isMoveOutDateToday = ($row['MoveOutDate'] === $currentDate);
                    ?>
                    <div class="col-md-4 col-12 mb-4">
                        <div class="card property-card shadow-sm">
                            <img src="<?php echo htmlspecialchars($firstImage); ?>" alt="Property Image" class="property-image" style="height: 200px; object-fit: cover;">
                            <div class="property-info p-3">
                                <h5 class="property-title"><?php echo htmlspecialchars($row['PropertyType']); ?></h5>
                                <p class="property-address"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($row['PropertyAddress']); ?></p>
                                <p class="property-price">RM <?php echo number_format($row['PropertyPrice'], 2); ?></p>

                                <!-- Additional Booking Details -->
                                <p class="compact-text"><strong>Booking Type:</strong> <?php echo htmlspecialchars($row['BookingType']); ?></p>
                                <p class="compact-text"><strong>Move-In Date:</strong> <?php echo htmlspecialchars(date('d M Y', strtotime($row['MoveInDate']))); ?></p>
                                <p class="compact-text"><strong>Move-Out Date:</strong> <?php echo htmlspecialchars(date('d M Y', strtotime($row['MoveOutDate']))); ?></p>
                                <p class="compact-text"><strong>Monthly Rent Per Person:</strong> RM <?php echo number_format($row['MonthlyRentPerPerson'], 2); ?></p>
                                <br>
                                <p class="compact-text"><strong>Homeowner Bank Account: </strong><?php echo htmlspecialchars($row['bankNumber']); ?></p>
                                <p class="compact-text"><strong>Homeowner Bank Name: </strong><?php echo htmlspecialchars($row['bankName']); ?></p>
                                
                                <!-- Rate Button -->
                                <?php if ($isMoveOutDateToday): ?>
                                    <div class="mt-3">
                                        <a href="rate_property.php?BookingID=<?php echo $row['BookingID']; ?>" class="btn btn-success">
                                            Rate Property
                                        </a>
                                    </div>
                                <?php endif; ?>
                                <div class="property-actions d-flex flex-column flex-md-row gap-2">
                        <a href="PropertyDetails.php?id=<?php echo $row['PropertyID']; ?>" class="btn btn-primary btn-sm mb-2 mb-md-0">View Property</a>
                                </div>
                        </div>
                            </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No pending booking found.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>