<?php
include("Auth.php");
include("config.php");
include("NavBar.php");
include("getUserProfile.php");

$UserID = $_SESSION['UserID'];

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
$waitingListQuery = "SELECT wl.*, p.PropertyType, p.PropertyAddress, p.PropertyPrice, p.Photo 
                     FROM waiting_list wl
                     INNER JOIN property p ON wl.PropertyID = p.PropertyID
                     WHERE wl.UserID = ?";
$stmt = $conn->prepare($waitingListQuery);
$stmt->bind_param("i", $_SESSION['UserID']);
$stmt->execute();
$waitingListResult = $stmt->get_result();

// Fetch pending booking properties
$pendingBookingQuery = "SELECT b.*, p.PropertyType, p.PropertyAddress, p.PropertyPrice, p.Photo 
                        FROM booking b
                        INNER JOIN property p ON b.PropertyID = p.PropertyID
                        WHERE b.UserID = ? AND b.is_approved = 0";
$stmtPending = $conn->prepare($pendingBookingQuery);

if ($stmtPending === false) {
    die("Error preparing statement for pending bookings: " . $conn->error);
}

$stmtPending->bind_param("i", $_SESSION['UserID']);
$stmtPending->execute();
$pendingBookingResult = $stmtPending->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
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
                            <!-- User Information -->
                            <h3 class="fw-bold mb-2"> <?php echo $name; ?></h3>
                            <p class="text-muted mb-0"><i class="bi bi-person-fill"></i> <?php echo ucfirst($userType); ?></p><br>
                        </div>
                    </div>
                    <button class="btn btn-primary" id="editProfileBtn">Edit Profile</button>
                </div>

                <!-- Hidden Edit Profile Form -->
                <div class="edit-profile-form mt-4 p-4 bg-light rounded shadow-sm" id="editProfileForm" style="display: none;">
                    <h5 class="fw-bold mb-3">Edit Profile Picture</h5>
                    <form action="update_profile.php" method="POST" enctype="multipart/form-data">
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
                        <a class="nav-link active" href="#active-booking" data-bs-toggle="tab">Active Booking</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#pending-booking" data-bs-toggle="tab">Pending Booking</a>
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
                        <p>Active bookings content goes here...</p>
                    </div>

                    <!-- Pending Booking Tab -->
                    <div class="tab-pane fade" id="pending-booking">
                        <?php if ($pendingBookingResult->num_rows > 0): ?>
                            <div class="row">
                                <?php while ($pendingProperty = $pendingBookingResult->fetch_assoc()): ?>
                                    <?php
                                    $photos = json_decode($pendingProperty['Photo'], true);
                                    $firstPhoto = is_array($photos) && count($photos) > 0 ? $photos[0] : 'uploads/';
                                    ?>
                                    <div class="col-md-4 mb-4">
                                        <div class="card">
                                            <!-- Display Property Image -->
                                            <img src="<?php echo htmlspecialchars($firstPhoto); ?>" class="card-img-top" alt="Property Image" style="height: 200px; object-fit: cover;">
                                            <div class="card-body">
                                                <h5 class="card-title fw-bold"><?php echo htmlspecialchars($pendingProperty['PropertyType']); ?></h5>
                                                <p class="card-text">
                                                    <strong>Address:</strong> <?php echo htmlspecialchars($pendingProperty['PropertyAddress']); ?><br>
                                                    <strong>Monthly Rent:</strong> RM<?php echo number_format($pendingProperty['PropertyPrice'], 2); ?><br>
                                                    <strong>Booking Status:</strong> Pending
                                                </p>
                                                <a href="PropertyDetails.php?id=<?php echo $pendingProperty['PropertyID']; ?>" class="btn btn-primary">View Details</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p>No pending bookings at the moment.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Expired Booking Tab -->
                    <div class="tab-pane fade" id="expired-booking">
                        <p>Expired bookings content goes here...</p>
                    </div>

                    <!-- Waiting List Tab -->
                    <div class="tab-pane fade" id="waiting-list">
                        <?php if ($waitingListResult->num_rows > 0): ?>
                            <div class="row">
                                <?php while ($waitingProperty = $waitingListResult->fetch_assoc()): ?>
                                    <?php
                                    $photos = json_decode($waitingProperty['Photo'], true);
                                    $firstPhoto = is_array($photos) && count($photos) > 0 ? $photos[0] : 'uploads/';
                                    ?>
                                    <div class="col-md-4 mb-4">
                                        <div class="card">
                                            <!-- Display Property Image -->
                                            <img src="<?php echo htmlspecialchars($firstPhoto); ?>" class="card-img-top" alt="Property Image" style="height: 200px; object-fit: cover;">
                                            <div class="card-body">
                                                <h3 class="card-title fw-bold"><?php echo htmlspecialchars($waitingProperty['PropertyType']); ?></h3>
                                                <p class="card-text">
                                                    <strong>Address:</strong> <?php echo htmlspecialchars($waitingProperty['PropertyAddress']); ?><br>
                                                    <strong>Monthly Rent:</strong> RM<?php echo number_format($waitingProperty['PropertyPrice'], 2); ?><br>
                                                    <strong>Move-In Date:</strong> <?php echo htmlspecialchars($waitingProperty['MoveInDate']); ?><br>
                                                    <strong>Move-Out Date:</strong> <?php echo htmlspecialchars($waitingProperty['MoveOutDate']); ?>
                                                </p>
                                                <a href="PropertyDetails.php?id=<?php echo $waitingProperty['PropertyID']; ?>" class="btn btn-primary">View Details</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
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
