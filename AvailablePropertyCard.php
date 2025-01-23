<?php
include("config.php");

$userType = $_SESSION['user_type'] ?? null;
$userID = $_SESSION['UserID'] ?? 0;

// Fetch approved properties
$sql = "SELECT p.PropertyID, p.PropertyType, p.PropertyAddress, p.PropertyPrice, 
       p.PropertyAmenities, p.Photo, p.Furnishing, p.is_approved
FROM property p
LEFT JOIN booking b ON p.PropertyID = b.PropertyID
WHERE p.is_approved = 1
  AND p.UserID = ? 
  AND (b.PropertyID IS NULL OR b.is_approved = 0)";

// Prepare the query and check if it succeeded
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing query: " . $conn->error);
}

$stmt->bind_param("i", $userID);  // Bind the logged-in user's ID
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

                                <?php
                                // Check if the property is saved
                                $checkSavedQuery = "SELECT * FROM saved_property WHERE UserID = ? AND PropertyID = ?";
                                $checkStmt = $conn->prepare($checkSavedQuery);
                                $checkStmt->bind_param("ii", $userID, $row['PropertyID']);
                                $checkStmt->execute();
                                $checkResult = $checkStmt->get_result();
                                $isSaved = ($checkResult && $checkResult->num_rows > 0); 
                                $checkStmt->close();
                                ?>

                                <p class="property-amenities">
                                    <?php
                                    // Display amenities as icons
                                    $amenities = explode(",", $row['PropertyAmenities']);
                                    foreach ($amenities as $amenity) {
                                        echo "<span class='amenity-item'><i class='bi bi-check-circle'></i> " . htmlspecialchars(trim($amenity)) . "</span>";
                                    }
                                    ?>
                                </p>

                                <?php if ($row['is_approved'] == -1): ?>
                                    <div class="alert alert-danger mt-3">
                                        <strong>Rejection Reason:</strong> <?php echo htmlspecialchars($row['rejection_reason']); ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Property Actions -->
                                <div class="property-actions d-flex flex-column flex-md-row gap-2">
                                    <a href="PropertyDetails.php?id=<?php echo $row['PropertyID']; ?>" class="btn btn-primary btn-sm mb-2 mb-md-0">View Property</a>

                                    <?php if (!$isSaved && $userType == 'student'): ?>
                                        <a href="SaveButton_action.php?id=<?php echo $row['PropertyID']; ?>" class="btn btn-success btn-sm mb-2 mb-md-0">Save</a>
                                    <?php elseif ($isSaved && $userType == 'student'): ?>
                                        <a href="UnsaveProperty.php?id=<?php echo $row['PropertyID']; ?>" class="btn btn-danger btn-sm mb-2 mb-md-0">Unsave</a>
                                    <?php endif; ?>

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
                <p>No properties found.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
