<?php
include("Auth.php");
include("config.php");
include("NavBar.php");

if (isset($_GET['id'])) {
    $homeownerID = intval($_GET['id']); 
} else {
    $homeownerID = $_SESSION['UserID'];
}

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'active'; 

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

// Check logged-in user's role
$isStudent = ($_SESSION['user_type'] === 'student');
$isOwner = ($_SESSION['UserID'] === $homeownerID);

// Fetch active properties count for the selected homeowner
$sql = "SELECT COUNT(*) AS active_count FROM property WHERE UserID = ? AND is_approved = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $homeownerID);
$stmt->execute();
$result = $stmt->get_result();
$activeProperties = $result->fetch_assoc()['active_count'] ?? 0;

/// Fetch active properties (excluding those with bookings) for the "Active Listings" tab
$sql = "SELECT p.PropertyID, p.PropertyType, p.PropertyAddress, p.PropertyPrice, p.Photo 
FROM property p
LEFT JOIN booking b ON p.PropertyID = b.PropertyID
WHERE p.UserID = ? AND p.is_approved = 1 AND b.PropertyID IS NULL";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $homeownerID);
$stmt->execute();
$result = $stmt->get_result();
$activeProperties = [];
if ($result->num_rows > 0) {
while ($row = $result->fetch_assoc()) {
$activeProperties[] = $row;
}
}
$stmt->close();

// Fetch properties with bookings for the "Confirm Booking" tab
$sql = "SELECT p.PropertyID, p.PropertyType, p.PropertyAddress, p.PropertyPrice, p.Photo, b.BookingID, b.UserID AS StudentID
FROM property p
INNER JOIN booking b ON p.PropertyID = b.PropertyID
WHERE p.UserID = ? AND p.is_approved = 1 AND b.is_approved = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $homeownerID);
$stmt->execute();
$result = $stmt->get_result();
$propertiesToConfirm = [];
if ($result->num_rows > 0) {
while ($row = $result->fetch_assoc()) {
$propertiesToConfirm[] = $row;
}
}
$stmt->close();


// Fetch homeowner's properties and calculate property count
$sql = "SELECT PropertyID, PropertyType, PropertyAddress, PropertyPrice, PropertyAmenities, Photo, is_approved 
        FROM property 
        WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $homeownerID);
$stmt->execute();
$result = $stmt->get_result();

$properties = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $properties[] = $row;
    }
}
$propertyCount = count($properties); 
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homeowner Profile</title>

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
                        
                        <!-- User Information -->
                        <div class="d-flex flex-column">
                            <h3 class="fw-bold mb-2"> <?php echo $name; ?></h3>
                            <p class="text-muted mb-2"><i class="bi bi-person-fill"></i> <?php echo ucfirst($userType); ?></p><br>
                            
                            <div class="count-container">
                                <p class="count-number"><?php echo $propertyCount; ?></p>
                                <p class="count-label">Properties Listed</p>
                            </div>
                        </div>
                    </div>
                   
                    <!-- Edit Profile Button (Only for Homeowner) -->
                    <?php if ($isOwner): ?>
                        <button class="btn btn-primary" id="editProfileBtn">Edit Profile</button>
                    <?php endif; ?>
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
                <?php if (!$isStudent): // Show all tabs if user is NOT a student ?>
                    <ul class="nav nav-tabs my-4">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $tab === 'active' ? 'active' : ''; ?>" href="HomeownerProfile.php?id=<?php echo $homeownerID; ?>&tab=active">Active Listings</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $tab === 'pending' ? 'active' : ''; ?>" href="HomeownerProfile.php?id=<?php echo $homeownerID; ?>&tab=pending">Pending Listings</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $tab === 'rented' ? 'active' : ''; ?>" href="HomeownerProfile.php?id=<?php echo $homeownerID; ?>&tab=rented">Rented Properties</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $tab === 'confirm booking' ? 'active' : ''; ?>" href="HomeownerProfile.php?id=<?php echo $homeownerID; ?>&tab=confirm">Confirm Booking</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $tab === 'rejected' ? 'active' : ''; ?>" href="HomeownerProfile.php?id=<?php echo $homeownerID; ?>&tab=rejected">Rejected Properties</a>
                        </li>
                    </ul>
                <?php else: // Show ONLY Active Listings tab for students ?>
                    <ul class="nav nav-tabs my-4">
                        <li class="nav-item">
                            <a class="nav-link active" href="HomeownerProfile.php?id=<?php echo $homeownerID; ?>&tab=active">Active Listings</a>
                        </li>
                    </ul>
                <?php endif; ?>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Active Listings Tab -->
                    <?php if ($tab === 'active' || $isStudent): ?>
                        <div class="tab-pane fade show active">
                            <div class="row">
                                <?php if (!empty($activeProperties)): ?>
                                    <?php foreach ($activeProperties as $property): ?>
                                        <div class="col-md-4 mb-4">
                                            <div class="card">
                                                <img src="<?php echo htmlspecialchars($property['Photo']); ?>" alt="Property Image" class="card-img-top" style="height: 200px; object-fit: cover;">
                                                <div class="card-body">
                                                    <h5 class="card-title"><?php echo htmlspecialchars($property['PropertyType']); ?></h5>
                                                    <p class="card-text"><?php echo htmlspecialchars($property['PropertyAddress']); ?></p>
                                                    <p class="card-text fw-bold">RM <?php echo number_format($property['PropertyPrice'], 2); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>No active properties listed.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!$isStudent): ?>
                        <?php if ($tab === 'pending'): ?>
                            <div class="tab-pane fade show active">
                                <div class="row">
                                    <?php
                                    $sql = "SELECT PropertyID, PropertyType, PropertyAddress, PropertyPrice, Photo 
                                            FROM property 
                                            WHERE UserID = ? AND is_approved = 0";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("i", $homeownerID);
                                    $stmt->execute();
                                    $result = $stmt->get_result();

                                    if ($result->num_rows > 0):
                                        while ($row = $result->fetch_assoc()):
                                            include("PropertyCard.php");
                                        endwhile;
                                    else:
                                        echo "<p>No pending properties.</p>";
                                    endif;

                                    $stmt->close();
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Confirm Booking Tab -->
                        <?php if ($tab === 'confirm'): ?>
                            <div class="tab-pane fade show active">
                                <div class="row">
                                    <?php if (!empty($propertiesToConfirm)): ?>
                                        <?php foreach ($propertiesToConfirm as $property): ?>
                                            <div class="col-md-4 mb-4">
                                                <div class="card">
                                                    <img src="<?php echo htmlspecialchars($property['Photo']); ?>" alt="Property Image" class="card-img-top" style="height: 200px; object-fit: cover;">
                                                    <div class="card-body">
                                                        <h5 class="card-title"><?php echo htmlspecialchars($property['PropertyType']); ?></h5>
                                                        <p class="card-text"><?php echo htmlspecialchars($property['PropertyAddress']); ?></p>
                                                        <p class="card-text fw-bold">RM <?php echo number_format($property['PropertyPrice'], 2); ?></p>
                                                        <p><strong>Booking ID:</strong> <?php echo htmlspecialchars($property['BookingID']); ?></p>
                                                        <p><strong>Tenant Information:</strong> <?php echo htmlspecialchars($property['StudentID']); ?></p>
                                                        <div class="d-flex gap-2">
                                                            <a href="ApproveBooking.php?id=<?php echo $property['BookingID']; ?>" class="btn btn-success">Approve</a>
                                                            <a href="RejectBooking.php?id=<?php echo $property['BookingID']; ?>" class="btn btn-danger">Reject</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p>No bookings to confirm.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($tab === 'rented'): ?>
                            <div class="tab-pane fade show active">
                                <div class="row">
                                    <?php
                                    $sql = "SELECT PropertyID, PropertyType, PropertyAddress, PropertyPrice, Photo 
                                            FROM property 
                                            WHERE UserID = ? AND is_approved = 2";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("i", $homeownerID);
                                    $stmt->execute();
                                    $result = $stmt->get_result();

                                    if ($result->num_rows > 0):
                                        while ($row = $result->fetch_assoc()):
                                            include("PropertyCard.php");
                                        endwhile;
                                    else:
                                        echo "<p>No rented properties.</p>";
                                    endif;

                                    $stmt->close();
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($tab === 'rejected'): ?>
                            <div class="tab-pane fade show active">
                                <div class="row">
                                    <?php
                                    $sql = "SELECT PropertyID, PropertyType, PropertyAddress, PropertyPrice, Photo 
                                            FROM property 
                                            WHERE UserID = ? AND is_approved = -1";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("i", $homeownerID);
                                    $stmt->execute();
                                    $result = $stmt->get_result();

                                    if ($result->num_rows > 0):
                                        while ($row = $result->fetch_assoc()):
                                            include("PropertyCard.php");
                                        endwhile;
                                    else:
                                        echo "<p>No rejected properties.</p>";
                                    endif;

                                    $stmt->close();
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>  
        </main>

        <?php if (isset($_GET['error']) || isset($_GET['success'])): ?>
        <!-- Bootstrap Modal -->
        <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="statusModalLabel">
                            <?php echo isset($_GET['success']) ? "Success" : "Error"; ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php 
                            if (isset($_GET['success'])) {
                                echo htmlspecialchars($_GET['success']);
                            } elseif (isset($_GET['error'])) {
                                echo htmlspecialchars($_GET['error']);
                            }
                        ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            const statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
            statusModal.show();
        </script>
        <?php endif; ?>


        <!-- Footer Section -->
        <?php include 'Footer.php'; ?>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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
