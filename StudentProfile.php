<?php
include("config.php");
include("Authenticate.php");
include("Navigation.php");
include("getUserProfile.php");

$userID = $_SESSION['UserID'] ?? 0;


if (isset($_SESSION['UserID'])) {
    $userProfile = getUserProfile($_SESSION['UserID']);

    if ($userProfile) {
        $name = htmlspecialchars($userProfile['name']);
        $profile_picture = $userProfile['profile_picture'] ? $userProfile['profile_picture'] : 'uploads/';
        $userType = htmlspecialchars($userProfile['user_type']);
        $propertyCount = $userProfile['property_count'];
    } else {
        header("Location: Login.php");
        exit();
    }
} else {
    header("Location: Login.php");
    exit();
}

// Fetch waiting list properties
$waitingListQuery = "SELECT wl.*, p.PropertyType, p.PropertyAddress, p.PropertyPrice, p.Photo, p.MonthlyRentPerPerson
                     FROM waiting_list wl
                     INNER JOIN property p ON wl.PropertyID = p.PropertyID
                     WHERE wl.UserID = ?";


$stmt = $conn->prepare($waitingListQuery);
if ($stmt === false) {
    die("Error preparing waiting list query: " . $conn->error);
}
$stmt->bind_param("i", $_SESSION['UserID']);
$stmt->execute();
$waitingListResult = $stmt->get_result();


// Fetch active bookings
$activeBookingQuery = "SELECT b.*, p.PropertyType, p.PropertyAddress, p.PropertyPrice, p.Photo, p.TotalTenants, p.PropertyPrice, p.bankNumber, p.bankName
                       FROM booking b
                       INNER JOIN property p ON b.PropertyID = p.PropertyID
                       WHERE b.UserID = ? AND b.is_approved = 1";

$stmtActive = $conn->prepare($activeBookingQuery);

if ($stmtActive === false) {
    die("Error preparing statement for active bookings: " . $conn->error);
}

$stmtActive->bind_param("i", $_SESSION['UserID']);
$stmtActive->execute();
$activeBookingResult = $stmtActive->get_result();

// Fetch count of active bookings 
$countActiveBookingQuery = "SELECT COUNT(*) AS activeBookings
                            FROM booking b
                            INNER JOIN property p ON b.PropertyID = p.PropertyID
                            WHERE b.UserID = ? AND b.is_approved = 1";

$stmtCountActive = $conn->prepare($countActiveBookingQuery);

if ($stmtCountActive === false) {
    die("Error preparing statement for active bookings count: " . $conn->error);
}

$stmtCountActive->bind_param("i", $_SESSION['UserID']);
$stmtCountActive->execute();
$countActiveBookingResult = $stmtCountActive->get_result();

// Fetch the count
$countActiveBookings = 0;
if ($countActiveBookingResult->num_rows > 0) {
    $row = $countActiveBookingResult->fetch_assoc();
    $countActiveBookings = $row['activeBookings'];
}

// count pending booking 
$pendingBookingCount = 0;
$sql = "SELECT COUNT(*) AS count FROM booking WHERE UserID = ? AND is_approved = 0";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error preparing statement for pending booking count: " . $conn->error);
}

$stmt->bind_param("i", $_SESSION['UserID']);
$stmt->execute();
$stmt->bind_result($pendingBookingCount);
$stmt->fetch();
$stmt->close();

// Fetch pending bookings and pass the result to the included file
$sql = "SELECT b.BookingID, b.PropertyID, p.PropertyType, p.PropertyAddress, p.PropertyPrice, p.bankNumber, p.bankName,
               p.Photo, b.BookingType, b.MoveInDate, b.MoveOutDate, b.is_approved, 
               p.MonthlyRentPerPerson
        FROM booking b
        INNER JOIN property p ON b.PropertyID = p.PropertyID
        INNER JOIN users u ON b.UserID = u.UserID
        WHERE b.UserID = ? AND b.is_approved = 0";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $_SESSION['UserID']);
    $stmt->execute();
    $pendingBookingResult = $stmt->get_result();
} else {
    die("Error preparing the SQL statement: " . $conn->error);
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>

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
                    <form action="update_studentprofile.php" method="POST" enctype="multipart/form-data">
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
                <ul class="nav nav-tabs my-4">
                    <li class="nav-item">
                        <a class="nav-link active" href="#active-booking" data-bs-toggle="tab">Active Booking
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#pending-booking" data-bs-toggle="tab">
                            Pending Booking
                            <?php if (isset($pendingBookingCount) && $pendingBookingCount > 0): ?>
                                <span class="badge badge-danger"><?php echo $pendingBookingCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#expired-booking" data-bs-toggle="tab">Expired Booking</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#waiting-list" data-bs-toggle="tab">Waiting List</a>
                    </li>

                </ul>

                <div class="tab-content">
                    <!-- Active Booking Tab -->
                    <div class="tab-pane fade show active" id="active-booking">
                        <?php
                        $currentDate = date('Y-m-d'); // Get today's date
                        $expiredBookings = []; // Initialize an array to store expired bookings
                        ?>
                        <?php if ($activeBookingResult->num_rows > 0): ?>
                            <div class="row">
                                <?php while ($activeBooking = $activeBookingResult->fetch_assoc()): ?>
                                    <?php
                                    // Normalize MoveOutDate to 'Y-m-d' format for comparison
                                    if (date('Y-m-d', strtotime($activeBooking['MoveOutDate'])) == $currentDate) {
                                        $expiredBookings[] = $activeBooking; // Store this booking in expired bookings
                                        continue; // Skip adding it to active bookings
                                    }
                                    ?>
                                    <?php include 'ActiveBookingCard.php'; ?>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p>No active bookings at the moment.</p>
                        <?php endif; ?>
                    </div>


                    <!-- Pending Booking Tab -->
                    <div class="tab-pane fade <?php echo $tab === 'pending-booking' ? 'show active' : ''; ?>" id="pending-booking">
                        <?php if ($pendingBookingResult && $pendingBookingResult->num_rows > 0): ?>
                            <div class="row">
                                <?php 
                                include("StudentPendingBooking.php");
                                ?>
                            </div>
                        <?php else: ?>
                            <p>No pending bookings available.</p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Expired Booking Tab -->
                    <div class="tab-pane fade" id="expired-booking">
                        <?php if (!empty($expiredBookings)): ?>
                            <div class="row">
                                <?php foreach ($expiredBookings as $expiredBooking): ?>
                                    <?php include 'ExpiredBookingCard.php'; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>No expired bookings available.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Waiting List Tab -->
                    <div class="tab-pane fade" id="waiting-list">
                        <?php if ($waitingListResult->num_rows > 0): ?>
                            <div class="row">
                                <?php include("WaitingListCard.php"); ?>
                            </div>
                        <?php else: ?>
                            <p>No properties in the waiting list.</p>
                        <?php endif; ?>
                    </div>

                </div>
            </div>  
        </main>

        <!-- Footer Section -->
        <?php include 'Footer.php'; ?>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

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
