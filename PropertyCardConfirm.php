<?php
include("config.php");

$userType = $_SESSION['user_type'] ?? null;
$userID = $_SESSION['UserID'] ?? 0;
$isDashboardPage = $isDashboardPage ?? false;

$sql = $isDashboardPage
    ? "SELECT property.PropertyID, property.PropertyType, property.PropertyAddress, property.PropertyPrice, property.PropertyAmenities, 
              property.Photo, property.Furnishing, property.is_approved 
       FROM property 
       WHERE property.is_approved = 1 
       LIMIT 10"
    : "SELECT property.PropertyID, property.PropertyType, property.PropertyAddress, property.PropertyPrice, property.PropertyAmenities,property.TotalTenants, 
              property.Photo, property.Furnishing, property.is_approved, property.rejection_reason, 
              booking.BookingID, booking.UserID AS TenantID, booking.BookingType, booking.NumOfPeople, 
              booking.MoveInDate, booking.MoveOutDate, booking.MonthlyRent, booking.TotalRent, booking.is_approved AS BookingApproved,
              users.name AS StudentName, users.phone_number AS StudentPhone, users.university AS University
       FROM property 
       LEFT JOIN booking ON property.PropertyID = booking.PropertyID
       LEFT JOIN users ON booking.UserID = users.UserID
       WHERE property.UserID = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing the SQL statement: " . $conn->error); // Handle SQL preparation error
}

if (!$isDashboardPage) {
    if (!$stmt->bind_param("i", $userID)) {
        die("Error binding parameters: " . $stmt->error); // Handle parameter binding error
    }
}

if (!$stmt->execute()) {
    die("Error executing the SQL query: " . $stmt->error); // Handle query execution error
}

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Card</title>

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
                    ?>
                    <div class="col-md-4 col-12 mb-4">
                        <div class="card property-card shadow-sm">
                            <img src="<?php echo htmlspecialchars($firstImage); ?>" alt="Property Image" class="property-image">
                            <div class="property-info">
                                <h5 class="property-title"><?php echo htmlspecialchars($row['PropertyType']); ?></h5>
                                <p class="property-address"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($row['PropertyAddress']); ?></p>
                                <p class="property-price">RM <?php echo number_format($row['PropertyPrice'], 2); ?></p>

                                <!-- Additional Information -->
                                <?php if (!empty($row['BookingID'])): ?>
                                    <p class="compact-text"><strong>Name:</strong> <?php echo htmlspecialchars($row['StudentName']); ?></p>
                                    <p class="compact-text"><strong>Phone Number:</strong> <?php echo htmlspecialchars($row['StudentPhone']); ?></p>
                                    <p class="compact-text"><strong>University:</strong> <?php echo htmlspecialchars($row['University']); ?></p>
                                    <p class="compact-text"><strong>Total Tenant:</strong><?php echo htmlspecialchars($row['TotalTenants']); ?></span></p>
                                    <p class="compact-text"><strong>Booking Type: </strong><?php echo htmlspecialchars($row['BookingType']); ?></p>
                                    <p class="compact-text"><strong>Move-In Date:</strong> <?php echo htmlspecialchars(date('d M Y', strtotime($row['MoveInDate']))); ?></p>
                                    <p class="compact-text"><strong>Move-Out Date:</strong> <?php echo htmlspecialchars(date('d M Y', strtotime($row['MoveOutDate']))); ?></p><br>
                                <?php endif; ?>

                                <?php if ($row['is_approved'] == -1): ?>
                                    <div class="alert alert-danger mt-3">
                                        <strong>Rejection Reason:</strong> <?php echo htmlspecialchars($row['rejection_reason']); ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Action Buttons -->
                                <div class="property-actions d-flex flex-column flex-md-row gap-2">
                                    <?php if (!empty($row['BookingID'])): ?>
                                        <a href="ApproveBooking.php?id=<?php echo $row['BookingID']; ?>" class="btn btn-success">Approve</a>
                                        <a href="RejectBooking.php?id=<?php echo $row['BookingID']; ?>" class="btn btn-danger">Reject</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No properties found.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
