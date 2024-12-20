<?php
// Include the necessary files
include("Auth.php");
include("config.php");
include("NavBar.php");

// Fetch user details based on the logged-in UserID
if (isset($_SESSION['UserID'])) {
    $userID = $_SESSION['UserID'];

    // Prepare the query to fetch user details
    $sql = "SELECT UserID, name, user_type FROM users WHERE UserID = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // Set session variables if not already set
            $_SESSION['name'] = $row['name'];
            $_SESSION['user_type'] = $row['user_type'];
        } else {
            // Handle error if no user found
            die("Error: User not found.");
        }
        $stmt->close();
    } else {
        die("Error preparing statement: " . $conn->error);
    }
} else {
    die("Error: UserID not set in session.");
}

$showPropertyStatus = false;  // Set to false to hide "APPROVED" in Dashboard
$isDashboardPage = true;  // Flag to indicate we're on the Dashboard page

// Fetch available properties (exclude booked properties)
$availablePropertiesQuery = "
    SELECT p.* 
    FROM property p
    LEFT JOIN booking b ON p.PropertyID = b.PropertyID
    WHERE p.is_approved = 1 AND b.PropertyID IS NULL";
$availablePropertiesResult = $conn->query($availablePropertiesQuery);

// Fetch waiting list properties
$waitingListQuery = "SELECT wl.*, p.PropertyType, p.PropertyAddress, p.PropertyPrice, p.Photo, p.TotalTenants 
                     FROM waiting_list wl
                     INNER JOIN property p ON wl.PropertyID = p.PropertyID
                     WHERE wl.NumOfPeople < p.TotalTenants";
$waitingListResult = $conn->query($waitingListQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
   
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="wrapper">
        <main class="content">
            <!-- success message for save button -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-info">
                    <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                </div>
            <?php endif; ?>

            <!-- Display User Type -->
            <div class="welcome">
                 <h5>Welcome <?php echo ucfirst($_SESSION['user_type']); ?> <?php echo htmlspecialchars($_SESSION['name']); ?>!</h5>
            </div>

            <!-- Banner Section -->
            <?php include 'Banner.php'; ?>

            <!-- Search Bar -->
            <div class="d-flex justify-content-center mt-4 mb-4">
                <form action="Dashboard.php" method="GET" class="d-flex">
                    <input type="text" class="form-control me-3" name="search" style="width: 500px;" placeholder="Search users by name or email" id="searchUser" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button class="btn search-btn" type="submit">Search</button>
                </form>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs justify-content-center my-4">
                <li class="nav-item">
                    <a class="nav-link active" href="#available-properties" data-bs-toggle="tab">Available Properties</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#remaining-spot-properties" data-bs-toggle="tab">Join Waiting List</a>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Available Properties Tab -->
                <div class="tab-pane fade show active" id="available-properties">
                    <div class="container mt-4">
                        <?php if ($availablePropertiesResult->num_rows > 0): ?>
                            <div class="row">
                                <?php while ($property = $availablePropertiesResult->fetch_assoc()): ?>
                                    <?php
                                    // Decode the photo JSON field for multiple photos
                                    $photos = json_decode($property['Photo'], true);
                                    $firstPhoto = is_array($photos) && count($photos) > 0 ? $photos[0] : 'uploads/default-property.jpg';

                                    // Determine the icon based on the furnishing type
                                    $furnishingIcon = '';
                                    if ($property['Furnishing'] === 'Fully Furnished') {
                                        $furnishingIcon = '<i class="bi bi-house-fill"></i>';
                                    } elseif ($property['Furnishing'] === 'Partially Furnished') {
                                        $furnishingIcon = '<i class="bi bi-house-door"></i>';
                                    } elseif ($property['Furnishing'] === 'Not Furnished') {
                                        $furnishingIcon = '<i class="bi bi-house"></i>';
                                    }
                                    ?>
                                    <div class="col-md-4 mb-4">
                                        <div class="card property-card shadow-sm">
                                            <!-- Property Image -->
                                            <img src="<?php echo htmlspecialchars($firstPhoto); ?>" alt="Property Image" class="property-image" style="height: 200px; object-fit: cover;">

                                            <!-- Property Information -->
                                            <div class="property-info">
                                                <h5 class="property-title"><?php echo htmlspecialchars($property['PropertyType']); ?></h5>
                                                <p class="property-address"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($property['PropertyAddress']); ?></p>
                                                <p class="property-price">RM <?php echo number_format($property['PropertyPrice'], 2); ?></p>
                                                <p class="property-amenities">
                                                    <?php
                                                    // Assuming PropertyAmenities is a comma-separated string, split it into individual amenities
                                                    $amenities = explode(",", $property['PropertyAmenities']);
                                                    foreach ($amenities as $amenity) {
                                                        echo "<span class='amenity-item'><i class='bi bi-check-circle'></i> " . htmlspecialchars(trim($amenity)) . "</span>";
                                                    }
                                                    ?>
                                                </p>

                                                <!-- Furnishing -->
                                                <div class="furnishing-item">
                                                    <?php echo $furnishingIcon; ?> <?php echo htmlspecialchars($property['Furnishing']); ?>
                                                </div>

                                                <!-- Rejection Reason (if rejected) -->
                                                <?php if (isset($property['is_approved']) && $property['is_approved'] == -1): ?>
                                                    <div class="alert alert-danger mt-3">
                                                        <strong>Rejection Reason:</strong> <?php echo htmlspecialchars($property['rejection_reason']); ?>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Buttons -->
                                                <div class="property-actions d-flex flex-column flex-md-row gap-2">
                                                    <a href="PropertyDetails.php?id=<?php echo $property['PropertyID']; ?>" class="btn btn-primary mb-2 mb-md-0">View Property</a>

                                                    <?php if ($userType == 'student'): ?>
                                                        <a href="SaveButton_action.php?id=<?php echo $property['PropertyID']; ?>" class="btn btn-success mb-2 mb-md-0">Save</a>
                                                    <?php elseif ($userType == 'homeowner'): ?>
                                                        <a href="EditProperty.php?id=<?php echo $property['PropertyID']; ?>" class="btn btn-secondary mb-2 mb-md-0">Edit</a>
                                                        <a href="DeleteProperty.php?id=<?php echo $property['PropertyID']; ?>" class="btn btn-danger mb-2 mb-md-0" onclick="return confirm('Are you sure you want to delete this property?');">Delete</a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p>No available properties at the moment.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Properties with remaining spots Tab --> 
                <div class="tab-pane fade" id="remaining-spot-properties">
                    <div class="container mt-4">
                        <div class="description mb-5">
                            <p>This section displays properties where some tenants have already booked, but there are still open spots available.</p>
                            <p>You can join the remaining spots if they fit your requirements.</p>
                        </div>

                        <div class="row">
                            <?php if ($waitingListResult->num_rows > 0): ?>
                                <?php while ($property = $waitingListResult->fetch_assoc()): ?>
                                    <?php
                                    $photos = json_decode($property['Photo'], true);
                                    $firstPhoto = is_array($photos) && count($photos) > 0 ? $photos[0] : 'uploads/default-property.jpg';
                                    $remainingSpots = $property['TotalTenants'] - $property['NumOfPeople'];
                                    ?>
                                    <div class="col-md-4 mb-4">
                                        <div class="card shadow-sm">
                                            <img src="<?php echo htmlspecialchars($firstPhoto); ?>" alt="Property Image" class="card-img-top" style="height: 200px; object-fit: cover;">
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo htmlspecialchars($property['PropertyType']); ?></h5>
                                                <p class="card-text">Remaining Spots: <?php echo $remainingSpots; ?></p>
                                                <a href="JoinWaitingList.php?id=<?php echo $property['WaitingListID']; ?>" class="btn btn-success">Join Waiting List</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p>No properties with remaining spots.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <!-- Footer Section -->
        <?php include 'Footer.php'; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
