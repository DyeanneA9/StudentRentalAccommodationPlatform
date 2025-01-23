<?php
include("config.php");
include("Authenticate.php");
include("Navigation.php");

// Get user type and ID from session
$userType = $_SESSION['user_type'] ?? null;
$userID = $_SESSION['UserID'] ?? 0;

// Fetch user information from the database
$sql = "SELECT UserID, name, user_type FROM users WHERE UserID = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['name'] = $row['name'];
        $_SESSION['user_type'] = $row['user_type'];
    } else {
        die("Error: User not found.");
    }
    $stmt->close();
} else {
    die("Error preparing statement: " . $conn->error);
}

// Query to fetch available properties (excluding user bookings)
$availablePropertiesQuery = "
    SELECT p.*
    FROM property p
    WHERE p.is_approved = 1 AND NOT EXISTS (
        SELECT 1 
        FROM booking b 
        WHERE p.PropertyID = b.PropertyID 
        AND b.UserID = ? 
        AND b.is_approved = 1
    )
";

// Capture the filter values from the GET request
$search = isset($_GET['search']) ? htmlspecialchars(trim($_GET['search'])) : '';
$filterType = isset($_GET['type']) ? htmlspecialchars(trim($_GET['type'])) : '';

// Query to fetch available properties (excluding user bookings)
$availablePropertiesQuery = "
    SELECT p.*
    FROM property p
    WHERE p.is_approved = 1 AND NOT EXISTS (
        SELECT 1 
        FROM booking b 
        WHERE p.PropertyID = b.PropertyID 
        AND b.UserID = ? 
        AND b.is_approved = 1
    )
";

// Apply search filter
if (!empty($search)) {
    $availablePropertiesQuery .= " AND LOWER(p.PropertyAddress) LIKE CONCAT('%', LOWER(?), '%')";
}

// Apply property type filter
if (!empty($filterType)) {
    $availablePropertiesQuery .= " AND LOWER(p.PropertyType) = LOWER(?)";
}

// Prepare the query and bind parameters
$stmt = $conn->prepare($availablePropertiesQuery);

// Bind parameters based on the presence of filters
if (!empty($search) && !empty($filterType)) {
    $stmt->bind_param("sss", $_SESSION['UserID'], $search, $filterType); 
} elseif (!empty($search)) {
    $stmt->bind_param("ss", $_SESSION['UserID'], $search); 
} elseif (!empty($filterType)) {
    $stmt->bind_param("ss", $_SESSION['UserID'], $filterType);
} else {
    $stmt->bind_param("s", $_SESSION['UserID']); 
}

$stmt->execute();
$availablePropertiesResult = $stmt->get_result();

// Count the total number of available properties
$countAvailableQuery = "
    SELECT COUNT(*) AS totalProperties
    FROM property p
    WHERE p.is_approved = 1 AND NOT EXISTS (
        SELECT 1
        FROM booking b
        WHERE p.PropertyID = b.PropertyID
        AND b.UserID = ? 
        AND b.is_approved = 1
    )
";
$stmtCountAvailable = $conn->prepare($countAvailableQuery);
$stmtCountAvailable->bind_param("i", $_SESSION['UserID']);
$stmtCountAvailable->execute();
$countResult = $stmtCountAvailable->get_result();
$totalAvailableProperties = 0;

if ($countResult->num_rows > 0) {
    $row = $countResult->fetch_assoc();
    $totalAvailableProperties = $row['totalProperties'];
}

// Query for waiting list properties
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
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="wrapper">
        <main class="content">
            <!-- Success message for save button -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-info">
                    <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                </div>
            <?php endif; ?>

            <!-- Display User Type -->
            <div class="welcome">
                <h5>Welcome <?php echo ucfirst($_SESSION['user_type']); ?> <?php echo htmlspecialchars($_SESSION['name']); ?>!</h5>
            </div>

            <?php include 'Banner.php'; ?>

            <!-- Search Bar -->
            <div class="d-flex justify-content-center mt-4 mb-4" style="padding-top: 20px;">
                <form action="Dashboard.php" method="GET" class="d-flex align-items-center">
                    <input type="text" class="form-control me-3" name="search" style="width: 400px;" placeholder="Search by location" value="<?php echo htmlspecialchars($search); ?>">
                    <select name="type" class="form-select me-3 custom-height" style="width: 200px;">
                        <option value="">All Property Types</option>
                        <option value="A Room" <?php echo $filterType === 'A Room' ? 'selected' : ''; ?>>A Room</option>
                        <option value="Shared Room" <?php echo $filterType === 'Shared Room' ? 'selected' : ''; ?>>Shared Room</option>
                        <option value="Shared Apartment" <?php echo $filterType === 'Shared Apartment' ? 'selected' : ''; ?>>Shared Apartment</option>
                        <option value="Shared House" <?php echo $filterType === 'Shared House' ? 'selected' : ''; ?>>Shared House</option>
                    </select>
                    <button class="btn btn-primary search-btn" type="submit">Search</button>
                </form>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs justify-content-center my-4">
                <li class="nav-item">
                    <a class="nav-link active" href="#available-properties" data-bs-toggle="tab">Available Properties</a>
                </li>
                <?php if ($userType !== 'homeowner'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="#remaining-spot-properties" data-bs-toggle="tab">Join Waiting List</a>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="tab-content">
                <!-- Available Properties Tab -->
                <div class="tab-pane fade show active" id="available-properties">
                    <div class="container mt-4">

                        <?php if ($availablePropertiesResult->num_rows > 0): ?>
                            <div class="row">
                                <?php while ($property = $availablePropertiesResult->fetch_assoc()): ?>
                                    <?php
                                    include_once("DashboardProperty.php");
                                    ?>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center">No properties match your search and filter criteria.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Join Waiting List Tab -->
                <?php if ($userType !== 'homeowner'): ?>
                    <div class="tab-pane fade" id="remaining-spot-properties">
                        <div class="container mt-4">
                            <div class="caption-text">
                                <small>This section displays properties where some tenants have already booked, but there are still open spots available. You can join the remaining spots if they fit your requirements.<br></small>
                            </div><br>

                            <div class="row">
                                <?php if ($waitingListResult->num_rows > 0): ?>
                                    <?php 
                                    // Fetch the list of properties the user has already joined
                                    $joinedWaitingListQuery = "SELECT PropertyID FROM waiting_list WHERE UserID = ?";
                                    $stmtJoinedWaitingList = $conn->prepare($joinedWaitingListQuery);
                                    $stmtJoinedWaitingList->bind_param("i", $_SESSION['UserID']);
                                    $stmtJoinedWaitingList->execute();
                                    $joinedWaitingListResult = $stmtJoinedWaitingList->get_result();

                                    // Store joined PropertyIDs in an array
                                    $joinedProperties = [];
                                    while ($row = $joinedWaitingListResult->fetch_assoc()) {
                                        $joinedProperties[] = $row['PropertyID'];
                                    }
                                    ?>

                                    <?php while ($property = $waitingListResult->fetch_assoc()): ?>
                                        <?php
                                        $photos = json_decode($property['Photo'], true);
                                        $firstPhoto = is_array($photos) && count($photos) > 0 ? $photos[0] : 'uploads/default-property.jpg';
                                        $remainingSpots = $property['TotalTenants'] - $property['NumOfPeople'];
                                        $isAlreadyJoined = in_array($property['PropertyID'], $joinedProperties); // Check if the user has already joined
                                        ?>
                                        <div class="col-md-4 mb-4">
                                            <div class="card shadow-sm">
                                                <img src="<?php echo htmlspecialchars($firstPhoto); ?>" alt="Property Image" class="card-img-top" style="height: 200px; object-fit: cover;">
                                                <div class="card-body">
                                                <h3 class="card-title" style="font-weight: bold;"><?php echo htmlspecialchars($property['PropertyType']); ?></h3>
                                                <p class="card-text"><i class="bi bi-geo-alt"></i><?php echo htmlspecialchars($property['PropertyAddress']); ?></p>                                                <p class="card-text">Remaining Spots: <?php echo $remainingSpots; ?></p>
                                                    <?php if ($isAlreadyJoined): ?>
                                                        <button class="btn btn-secondary" disabled>Already Joined</button>
                                                    <?php else: ?>
                                                        <a href="JoinWaitingList.php?id=<?php echo $property['WaitingListID']; ?>" class="btn btn-success">Join Waiting List</a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-muted text-center mt-4">No properties with remaining spots at the moment.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <?php include 'Footer.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
