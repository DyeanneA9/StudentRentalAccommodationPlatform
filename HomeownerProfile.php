<?php
include("config.php");
include("Authenticate.php");
include("Navigation.php");
include("getUserProfile.php");

$homeownerID = $_SESSION['UserID'];

// Function to get the count of rows based on a condition
function getCount($table, $condition) {
    global $conn;
    $sql = "SELECT COUNT(*) AS total FROM $table WHERE $condition";
    $result = $conn->query($sql);
    return ($result && $row = $result->fetch_assoc()) ? $row['total'] : 0;
}

// Fetching counts
$homeownerID = isset($_GET['id']) ? intval($_GET['id']) : $_SESSION['UserID']; 
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'available'; 

// Fetch homeowner details
$sql = "SELECT name, profile_picture, user_type FROM users WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $homeownerID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $homeownerDetails = $result->fetch_assoc();
    $name = htmlspecialchars($homeownerDetails['name']);
    $profile_picture = $homeownerDetails['profile_picture'] ? $homeownerDetails['profile_picture'] : 'uploads/default-profile.png';
    $userType = htmlspecialchars($homeownerDetails['user_type']);
} else {
    die("Error: Homeowner profile not found.");
}

$stmt->close();

// Fetch counts of available, pending, and ongoing properties
$availableCount = getCount('property p','LEFT JOIN booking b ON p.PropertyID = b.PropertyID', "p.UserID = $homeownerID AND p.is_approved = 1 AND (b.PropertyID IS NULL OR b.is_approved = 0)");
$pendingCount = getCount('property', "UserID = $homeownerID AND is_approved = 0");
$ongoingCount = getCount('booking', "UserID = $homeownerID AND is_approved = 1 AND CURRENT_DATE BETWEEN MoveInDate AND MoveOutDate");
$pendingConfirmations = getCount('booking', "UserID = $homeownerID AND is_approved = 0");

// Fetch available properties
$sql = "SELECT COUNT(*) AS availableCount 
        FROM property p
        LEFT JOIN booking b ON p.PropertyID = b.PropertyID
        WHERE p.UserID = ? 
          AND p.is_approved = 1 
          AND (b.PropertyID IS NULL OR b.is_approved = 0)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $homeownerID);
$stmt->execute();
$activeProperties = [];
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $activeProperties[] = $row;
}
$stmt->close();

// Fetch recent activities
function getRecentActivities() {
    global $conn;
    $sql = "SELECT action, created_at FROM activities ORDER BY created_at DESC LIMIT 10";
    $result = $conn->query($sql);
    $activities = [];
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    return $activities;
}
$recentActivities = getRecentActivities();

//display ongoing rental 
$sql = "SELECT property.PropertyID, property.PropertyType, property.PropertyAddress, property.Photo,
               booking.MoveInDate, booking.MoveOutDate, users.name AS TenantName, users.phone_number AS TenantPhone
        FROM property
        INNER JOIN booking ON property.PropertyID = booking.PropertyID
        INNER JOIN users ON booking.UserID = users.UserID
        WHERE property.UserID = ? AND booking.is_approved = 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $homeownerID);
$stmt->execute();
$ongoingRentals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Count ongoing rentals
$sql = "SELECT COUNT(*) AS ongoingCount
        FROM booking b
        INNER JOIN property p ON b.PropertyID = p.PropertyID
        WHERE p.UserID = ? 
          AND b.is_approved = 1 
          AND CURDATE() BETWEEN b.MoveInDate AND b.MoveOutDate";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing the SQL statement: " . $conn->error);
}

$stmt->bind_param("i", $homeownerID);
$stmt->execute();
$stmt->bind_result($ongoingCount);
$stmt->fetch();
$stmt->close();


// Fetch pending bookings for the current homeowner
$sql = "SELECT b.BookingID, b.PropertyID, b.UserID, p.PropertyType, p.PropertyAddress, p.PropertyPrice,
               p.PropertyPrice, p.Photo, p.TotalTenants, b.BookingType, 
               b.MoveInDate, b.MoveOutDate, 
               u.name AS TenantName, u.phone_number AS TenantPhone 
        FROM booking b
        INNER JOIN property p ON b.PropertyID = p.PropertyID
        INNER JOIN users u ON b.UserID = u.UserID
        WHERE p.UserID = ? AND b.is_approved = 0";


$stmt = $conn->prepare($sql);

// Check if prepare() succeeded
if (!$stmt) {
    die("SQL Error: " . $conn->error); // Output the database error
}

$stmt->bind_param("i", $homeownerID);
$stmt->execute();
$result = $stmt->get_result();

$pendingBookings = [];
while ($row = $result->fetch_assoc()) {
    $pendingBookings[] = $row;
}

$stmt->close();

// Fetch count of pending bookings for confirmation
$sql = "SELECT COUNT(*) AS pendingCount 
        FROM booking b
        INNER JOIN property p ON b.PropertyID = p.PropertyID
        WHERE p.UserID = ? AND b.is_approved = 0";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("i", $homeownerID);
$stmt->execute();
$stmt->bind_result($pendingConfirmations);
$stmt->fetch();
$stmt->close();


// Fetch count of rejected properties
$rejectedCountQuery = "SELECT COUNT(*) AS total FROM property WHERE UserID = ? AND is_approved = -1";
$stmtRejected = $conn->prepare($rejectedCountQuery);
$stmtRejected->bind_param("i", $homeownerID);
$stmtRejected->execute();
$resultRejected = $stmtRejected->get_result();
$rejectedCount = 0;

if ($resultRejected->num_rows > 0) {
    $row = $resultRejected->fetch_assoc();
    $rejectedCount = $row['total'];
}

$stmtRejected->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homeowner Profile</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="wrapper">
        <main class="content">
            <div class="container my-4">
                <!-- Profile Header -->
                <div class="profile-header p-5 bg-light rounded shadow-sm d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <!-- User Profile Picture-->
                        <img src=" <?php echo $profile_picture; ?>" alt="Profile Picture" class="rounded-circle object-fit-cover me-4" width="120" height="120">
                        <div>
                            <!-- User Info -->
                            <h3 class="fw-bold mb-2"> <?php echo $name; ?></h3>
                            <p class="text-muted mb-0"><i class="bi bi-person-fill"></i> <?php echo ucfirst($userType); ?></p><br>
                        </div>
                    </div>
                    <button class="btn btn-primary" id="editProfileBtn">Edit Profile</button>
                </div>

                <!-- Hidden Edit Profile Form -->
                <div class="edit-profile-form mt-4 p-4 bg-light rounded shadow-sm" id="editProfileForm" style="display: none;">
                    <h5 class="fw-bold mb-3">Edit Profile Picture</h5>
                    <form action="update_ownerprofile.php" method="POST" enctype="multipart/form-data">
                        <!-- Profile Picture Upload -->
                        <div class="mb-3">
                            <label for="profilePicture" class="form-label">Profile Picture</label>
                            <input type="file" class="form-control" id="profilePicture" name="profile_picture" accept="image/*">
                        </div>
                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-success">Save Changes</button>
                        <button type="button" class="btn btn-secondary" id="cancelEditBtn">Cancel</button>
                    </form>
                </div>

                <!-- Tabs -->
                <?php if ($_SESSION['user_type'] !== 'student'): ?>
                    <ul class="nav nav-tabs my-4">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $tab === 'available' ? 'active' : ''; ?>" href="HomeownerProfile.php?id=<?php echo $homeownerID; ?>&tab=available">
                                Available Properties <?php if ($availableCount > 0): ?><span class="badge bg-primary"><?php echo $availableCount; ?></span><?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $tab === 'pending' ? 'active' : ''; ?>" href="HomeownerProfile.php?id=<?php echo $homeownerID; ?>&tab=pending">
                                Pending Properties <?php if ($pendingCount > 0): ?><span class="badge badge-danger"><?php echo $pendingCount; ?></span><?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $tab === 'ongoing' ? 'active' : ''; ?>" href="HomeownerProfile.php?id=<?php echo $homeownerID; ?>&tab=ongoing">
                                Ongoing Rentals <?php if ($ongoingCount > 0): ?><span class="badge bg-primary"><?php echo $ongoingCount; ?></span><?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $tab === 'confirm' ? 'active' : ''; ?>" href="?tab=confirm">
                                Confirm Booking <?php if ($pendingConfirmations > 0): ?><span class="badge bg-danger"><?php echo $pendingConfirmations; ?></span><?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $tab === 'expired' ? 'active' : ''; ?>" href="HomeownerProfile.php?id=<?php echo $homeownerID; ?>&tab=expired">Expired Properties</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $tab === 'rejected' ? 'active' : ''; ?>" href="HomeownerProfile.php?id=<?php echo $homeownerID; ?>&tab=rejected">
                                Rejected Properties <?php if ($rejectedCount > 0): ?><span class="badge badge-danger"><?php echo $rejectedCount; ?></span><?php endif; ?>
                            </a>
                        </li>
                    </ul>
                <?php else: ?>
                    <ul class="nav nav-tabs my-4">
                        <li class="nav-item">
                            <a class="nav-link active" href="HomeownerProfile.php?id=<?php echo $homeownerID; ?>&tab=available">
                                Available Properties <span class="badge badge-pill badge-info"><?php echo $availableCount; ?></span>
                            </a>
                        </li>
                    </ul>
                <?php endif; ?>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Available Properties Tab -->
                    <?php if ($tab === 'available' || $_SESSION['user_type'] === 'student'): ?>
                        <div class="tab-pane fade show active">
                            <div class="row">
                                <?php if (count($activeProperties) > 0): ?>
                                    <?php foreach ($activeProperties as $property): ?>
                                        <?php
                                            include_once("AvailablePropertyCard.php");
                                        ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>No available properties for this homeowner.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Pending Properties Tab -->
                    <?php if ($tab === 'pending' && $_SESSION['user_type'] !== 'student'): ?>
                        <div class="tab-pane fade show active">
                            <div class="row">
                                <?php
                                include("PendingPropertyCard.php");
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Ongoing Rental Tab -->
                    <?php if ($tab === 'ongoing' && $_SESSION['user_type'] !== 'student'): ?>
                        <div class="tab-pane fade show active">
                            <div class="row">
                                <?php if (!empty($ongoingRentals)): ?>
                                    <?php foreach ($ongoingRentals as $rental): ?>
                                        <?php
                                            $booking = $rental; 
                                            include("OngoingRentalCard.php");
                                        ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>No ongoing rentals found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>


                    <!-- Confirm Booking Tab -->
                    <?php if ($tab === 'confirm' && $_SESSION['user_type'] === 'homeowner'): ?>
                        <div class="tab-pane fade show active">
                            <div class="row">
                                <?php if (!empty($pendingBookings)): ?>
                                    <?php foreach ($pendingBookings as $booking): ?>
                                        <?php
                                        // Include the ConfirmBookingCard.php with proper variable scoping
                                        include("ConfirmBookingCard.php");
                                        ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>No bookings to be confirmed.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Rejected Tab -->
                    <?php if ($tab === 'rejected' && $_SESSION['user_type'] !== 'student'): ?>
                        <div class="tab-pane fade show active">
                            <div class="row">
                                <?php
                                include("RejectedPropertyCard.php");
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>  
        </main>

        <?php include 'Footer.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
    <script>
    const editProfileBtn = document.getElementById('editProfileBtn');
    const editProfileForm = document.getElementById('editProfileForm');
    const cancelEditBtn = document.getElementById('cancelEditBtn');

    // Show the form when Edit Profile button is clicked
    editProfileBtn.addEventListener('click', () => {
        editProfileForm.style.display = 'block';
        editProfileBtn.style.display = 'none'; // Hide the button while editing
    });

    // Hide the form and show the Edit Profile button when Cancel is clicked
    cancelEditBtn.addEventListener('click', () => {
        editProfileForm.style.display = 'none';
        editProfileBtn.style.display = 'block';
    });
    </script>
</body>
</html>
