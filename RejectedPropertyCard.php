<?php
include("config.php");

$userType = $_SESSION['user_type'] ?? null;
$userID = $_SESSION['UserID'] ?? 0;

// Condition to fetch rejected properties (is_approved = -1)
$sql = "SELECT PropertyID, PropertyType, PropertyAddress, PropertyPrice, PropertyAmenities, Photo, Furnishing, is_approved, rejection_reason 
        FROM property 
        WHERE UserID = ? AND is_approved = -1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

// Function to get furnishing icons
function getFurnishingIcon($furnishing) {
    $icons = [
        'Fully Furnished' => '<i class="bi bi-house-fill"></i>',
        'Partially Furnished' => '<i class="bi bi-house-door"></i>',
        'Not Furnished' => '<i class="bi bi-house"></i>',
    ];
    return $icons[$furnishing] ?? '';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejected Property Card</title>
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
                    // Decode photos and get the first image
                    $imageArray = json_decode($row['Photo'], true);
                    $firstImage = $imageArray[0] ?? 'uploads/default-image.jpg';
                    $furnishingIcon = getFurnishingIcon($row['Furnishing']);
                    ?>

                    <div class="col-md-4 col-12 mb-4">
                        <div class="card property-card shadow-sm">
                            <img src="<?php echo htmlspecialchars($firstImage); ?>" alt="Property Image" class="property-image">
                            <div class="property-info">
                                <h5 class="property-title"><?php echo htmlspecialchars($row['PropertyType']); ?></h5>
                                <p class="property-address">
                                    <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($row['PropertyAddress']); ?>
                                </p>
                                <p class="property-price">RM <?php echo number_format($row['PropertyPrice'], 2); ?></p>
                                <div class="furnishing-item">
                                    <?php echo $furnishingIcon; ?> <?php echo htmlspecialchars($row['Furnishing']); ?>
                                </div>

                                <p class="property-amenities">
                                    <?php
                                    // Display amenities as icons
                                    $amenities = explode(",", $row['PropertyAmenities']);
                                    foreach ($amenities as $amenity) {
                                        echo "<span class='amenity-item'><i class='bi bi-check-circle'></i> " . htmlspecialchars(trim($amenity)) . "</span>";
                                    }
                                    ?>
                                </p>

                                <?php if (isset($row['rejection_reason'])): ?>
                                    <div class="alert alert-danger mt-3">
                                        <strong>Rejection Reason:</strong> <?php echo htmlspecialchars($row['rejection_reason']); ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Property Actions -->
                                <div class="property-actions d-flex flex-column flex-md-row gap-2">
                                    <a href="PropertyDetails.php?id=<?php echo $row['PropertyID']; ?>" class="btn btn-primary btn-sm mb-2 mb-md-0">View Property</a>
                                    <?php if ($userType == 'homeowner'): ?>
                                        <a href="EditProperty.php?id=<?php echo $row['PropertyID']; ?>" class="btn btn-secondary btn-sm mb-2 mb-md-0">Edit</a>
                                        <a href="DeleteProperty.php?id=<?php echo $row['PropertyID']; ?>" class="btn btn-danger btn-sm mb-2 mb-md-0" onclick="return confirm('Are you sure you want to delete this property?');">Delete</a>
                                    <?php endif; ?>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center">No rejected properties found.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
