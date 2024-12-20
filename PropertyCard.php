<?php
include("config.php");

$userType = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;
$userID = isset($_SESSION['UserID']) ? $_SESSION['UserID'] : null; 

$isDashboardPage = isset($isDashboardPage) ? $isDashboardPage : false;

if ($isDashboardPage) {
    $sql = "SELECT PropertyID, PropertyType, PropertyAddress, PropertyPrice, PropertyAmenities, Photo, Furnishing, is_approved 
            FROM property 
            WHERE is_approved = 1 
            LIMIT 10";
} else {
    $sql = "SELECT PropertyID, PropertyType, PropertyAddress, PropertyPrice, PropertyAmenities, Photo, Furnishing, is_approved, rejection_reason
            FROM property 
            WHERE UserID = $userID";
}

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Card</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <?php
            if ($result->num_rows > 0) {
                // Loop through all the properties
                while ($row = $result->fetch_assoc()) {
                    // Decode the photo JSON field for multiple photos
                    $imageArray = json_decode($row['Photo'], true); // Decode JSON into an array
                    $firstImage = isset($imageArray[0]) ? $imageArray[0] : 'uploads/'; // Use the first image or a default image

                    // Determine the icon based on the furnishing type
                    $furnishingIcon = '';
                    if ($row['Furnishing'] === 'Fully Furnished') {
                        $furnishingIcon = '<i class="bi bi-house-fill"></i>';
                    } elseif ($row['Furnishing'] === 'Partially Furnished') {
                        $furnishingIcon = '<i class="bi bi-house-door"></i>';
                    } elseif ($row['Furnishing'] === 'Not Furnished') {
                        $furnishingIcon = '<i class="bi bi-house"></i>';
                    }

                    // check if the property is saved
                    $checkSavedQuery = "SELECT * FROM saved_property WHERE UserID = ? AND PropertyID = ?";
                    $checkStmt = $conn->prepare($checkSavedQuery);
                    $checkStmt->bind_param("ii", $userID, $row['PropertyID']);
                    $checkStmt->execute();
                    $checkResult = $checkStmt->get_result();
                    $isSaved = ($checkResult->num_rows > 0); 
                    $checkStmt->close();
                    ?>
                    <div class="col-md-4 mb-4">
                        <div class="card property-card shadow-sm">
                            <!-- Property Image -->
                            <img src="<?php echo htmlspecialchars($firstImage); ?>" alt="Property Image" class="property-image">
                            
                            <!-- Property Information -->
                            <div class="property-info">
                                <h5 class="property-title"><?php echo htmlspecialchars($row['PropertyType']); ?></h5>
                                <p class="property-address"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($row['PropertyAddress']); ?></p>
                                <p class="property-price">RM <?php echo number_format($row['PropertyPrice'], 2); ?></p>
                                <p class="property-amenities">
                                    <?php
                                    // Assuming PropertyAmenities is a comma-separated string, split it into individual amenities
                                    $amenities = explode(",", $row['PropertyAmenities']);
                                    foreach ($amenities as $amenity) {
                                        echo "<span class='amenity-item'><i class='bi bi-check-circle'></i> " . htmlspecialchars(trim($amenity)) . "</span>";
                                    }
                                    ?>
                                </p>

                                <!-- Furnishing -->
                                <div class="furnishing-item">
                                    <?php echo $furnishingIcon; ?> <?php echo htmlspecialchars($row['Furnishing']); ?>
                                </div>

                                <!-- Rejection Reason (if rejected) -->
                                <?php if ($row['is_approved'] == -1): ?>
                                    <div class="alert alert-danger mt-3">
                                        <strong>Rejection Reason:</strong> <?php echo htmlspecialchars($row['rejection_reason']); ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Buttons -->
                                <div class="property-actions d-flex flex-column flex-md-row gap-2">
                                    <a href="PropertyDetails.php?id=<?php echo $row['PropertyID']; ?>" class="btn btn-primary mb-2 mb-md-0">View Property</a>

                                    <?php if (!$isSaved && $userType == 'student'): ?>
                                        <a href="SaveButton_action.php?id=<?php echo $row['PropertyID']; ?>" class="btn btn-success mb-2 mb-md-0">Save</a>
                                    <?php elseif ($isSaved && $userType == 'student'): ?>
                                        <a href="UnsaveProperty.php?id=<?php echo $row['PropertyID']; ?>" class="btn btn-danger mb-2 mb-md-0">Unsave</a>
                                    <?php endif; ?>

                                    <?php if (!$isDashboardPage && $userType == 'homeowner') { ?>
                                    <a href="EditProperty.php?id=<?php echo $row['PropertyID']; ?>" class="btn btn-secondary mb-2 mb-md-0">Edit</a>

                                    <a href="DeleteProperty.php?id=<?php echo $row['PropertyID']; ?>" class="btn btn-danger mb-2 mb-md-0" onclick="return confirm('Are you sure you want to delete this property?');">Delete</a>
                                <?php } ?>

                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p>No properties found.</p>";
            }
            ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
