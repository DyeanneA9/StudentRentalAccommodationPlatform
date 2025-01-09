<?php
include("config.php");
include("Authenticate.php");
include("Navigation.php");

// Ensure user is logged in
$userID = $_SESSION['UserID'];

// Fetch the saved properties for the logged-in user
$sql = "SELECT p.PropertyID, p.PropertyType, p.PropertyAddress, p.PropertyPrice, p.PropertyAmenities, p.Photo, p.Furnishing
        FROM saved_property sp
        JOIN property p ON sp.PropertyID = p.PropertyID
        WHERE sp.UserID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID); // Bind the UserID parameter
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Properties</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="wrapper">
        <main class="content">
            <h4 class="mb-4 p-3">Your Saved Properties</h4>
            
            <?php if ($result->num_rows > 0): ?>
                <div class="container mt-4">
                    <div class="row">
                        <?php if (isset($_SESSION['message'])): ?>
                            <div class="alert alert-info">
                                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                            </div>
                        <?php endif; ?>

                        <?php
                        // Loop through each saved property
                        while ($row = $result->fetch_assoc()) {
                            // Decode the photo JSON field for multiple photos
                            $imageArray = json_decode($row['Photo'], true); // Decode JSON into an array
                            $firstImage = isset($imageArray[0]) ? $imageArray[0] : 'uploads/'; // Use the first image or a default image
                        ?>
                        <div class="col-md-4 mb-4">
                            <div class="card property-card shadow-sm">
                                <img src="<?php echo htmlspecialchars($firstImage); ?>" alt="Property Image" class="property-image">
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

                                    <!-- Furnishing Status -->
                                    <div class="furnishing-item">
                                        <?php 
                                        $furnishingIcon = '';
                                        if ($row['Furnishing'] === 'Fully Furnished') {
                                            $furnishingIcon = '<i class="bi bi-house-fill"></i>';
                                        } elseif ($row['Furnishing'] === 'Partially Furnished') {
                                            $furnishingIcon = '<i class="bi bi-house-door"></i>';
                                        } elseif ($row['Furnishing'] === 'Not Furnished') {
                                            $furnishingIcon = '<i class="bi bi-house"></i>';
                                        }
                                        echo $furnishingIcon . " " . htmlspecialchars($row['Furnishing']);
                                        ?>
                                    </div>

                                    <div class="property-actions">
                                        <a href="PropertyDetails.php?id=<?php echo $row['PropertyID']; ?>" class="btn btn-primary">View Details</a>
                                        <a href="UnsaveProperty.php?id=<?php echo $row['PropertyID']; ?>" class="btn btn-danger">Unsave</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            <?php else: ?>
                <p>You have no saved properties.</p>
            <?php endif; ?>

        </main>

        <!-- Footer Section -->
        <?php include 'Footer.php'; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="script.js"></script>
</body>
</html>
