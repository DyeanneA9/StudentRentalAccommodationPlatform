<?php
include("config.php");
include("Authenticate.php");
include("Navigation.php");
include("getUserProfile.php");


$userID = $_SESSION['UserID'] ?? null;
$currentUserPicture = 'uploads/default-user.png';
$property = null;
$ownerDetails = null;
$ownerPhoneNumber = null;
$formattedPhoneNumber = null;

// Fetch profile picture for the current user
if ($userID) {
    // Fetch profile picture path from the database
    $query = "SELECT profile_picture FROM users WHERE UserID = ?";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $stmt->bind_result($dbProfilePicture);
        $stmt->fetch();
        $stmt->close();

        // Set the profile picture if it exists and is valid
        if (!empty($dbProfilePicture) && file_exists($dbProfilePicture)) {
            $currentUserPicture = $dbProfilePicture;
        }
    }
}

// Check if the 'id' parameter exists in the URL
if (isset($_GET['id'])) {
    $propertyID = intval($_GET['id']);

    // Fetch property details from the database
    $sql = "SELECT * FROM property WHERE PropertyID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $propertyID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $property = $result->fetch_assoc();

        // Decode Photo JSON
        $photos = json_decode($property['Photo'], true);
        $photos = is_array($photos) ? $photos : [];

        // Fetch owner details using the UserID from the property table
        if (isset($property['UserID']) && !empty($property['UserID'])) {
            $queryOwner = "SELECT name, phone_number, user_type FROM users WHERE UserID = ? AND user_type = 'homeowner'";
            $stmtOwner = $conn->prepare($queryOwner);
            $stmtOwner->bind_param("i", $property['UserID']);
            $stmtOwner->execute();
            $ownerResult = $stmtOwner->get_result();

            if ($ownerResult->num_rows > 0) {
                $ownerDetails = $ownerResult->fetch_assoc();
                $ownerPhoneNumber = $ownerDetails['phone_number'] ?? null;

                // Format the phone number for WhatsApp (e.g., remove non-digit characters)
                $formattedPhoneNumber = $ownerPhoneNumber ? preg_replace('/\D/', '', $ownerPhoneNumber) : null;
            }
            $stmtOwner->close();
        }
    } else {
        echo "<p>Property not found.</p>";
        exit;
    }
} else {
    echo "<p>Invalid property ID.</p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Details</title>


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="wrapper">
    <main class="content">
        <div class="container mt-5">
            <!-- Property Header -->
            <div class="property-header d-flex justify-content-between align-items-center">
                <div>
                    <h1><?php echo htmlspecialchars($property['PropertyType']); ?></h1>
                    <p><i class="bi bi-geo-alt me-2"></i><?php echo htmlspecialchars($property['PropertyAddress']); ?></p>
                </div>
                <h3 class="text-white fw-bold">RM<?php echo number_format($property['PropertyPrice'], 2); ?>/month</h3>
            </div>

            <!-- Property Details -->
            <div class="row mt-4">
                <div class="col-lg-8">
                    <!-- Image Gallery with Carousel -->
                    <div id="propertyCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php
                            $isActive = true;
                            foreach ($photos as $photo) { ?>
                                <div class="carousel-item <?php echo $isActive ? 'active' : ''; ?>">
                                    <img src="<?php echo htmlspecialchars($photo); ?>" alt="Property Image" class="d-block w-100 img-fluid rounded">
                                </div>
                            <?php
                            $isActive = false;
                            } ?>
                        </div>
                        <!-- Carousel Controls -->
                        <button class="carousel-control-prev" type="button" data-bs-target="#propertyCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#propertyCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>

                    <!-- Description -->
                    <div class="property-description mt-4 p-2 rounded">
                        <h4 class="fw-bold">Property Description</h4>
                        <p><?php echo nl2br(htmlspecialchars($property['Description'])); ?></p>
                    </div>


                    <!-- Property Features (Grouped Section) -->
                    <div class="property-features mt-4">
                        <h4 class="fw-bold">Property Features</h4>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-door-closed me-2"></i><strong>Bedrooms:</strong> <?php echo htmlspecialchars($property['NoOfBedroom']); ?></li>
                            <li><i class="bi bi-droplet me-2"></i><strong>Bathrooms:</strong> <?php echo htmlspecialchars($property['NoOfBathroom']); ?></li>
                            <li><i class="bi bi-house me-2"></i><strong>Furnishing:</strong> <?php echo htmlspecialchars($property['Furnishing']); ?></li>
                        </ul>
                    </div>

                    <!-- Amenities -->
                    <div class="amenities mt-4">
                        <h4 class="fw-bold">Amenities</h4>
                        <div class="amenities-list mt-2">
                            <?php
                            $amenities = explode(',', $property['PropertyAmenities']);
                            foreach ($amenities as $amenity) { ?>
                                <span><i class="bi bi-check-circle"></i> <?php echo htmlspecialchars(trim($amenity)); ?></span>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Property Details Section -->
                    <div class="contact-box mb-4 p-2 rounded">
                        <h5 class="fw-bold">Property Details</h5>
                        <ul class="list-unstyled">
                            <li><strong>Property Type:</strong> <?php echo htmlspecialchars($property['PropertyType']); ?></li>
                            <li><strong>Total Tenant:</strong> <?php echo htmlspecialchars($property['TotalTenants']); ?></li>
                            <li><strong>Lease Length:</strong>
                                <?php
                                    $leaseLength = $property['LeaseLength'];
                                    if ($leaseLength == 1) {
                                        echo "1 day";
                                    } elseif ($leaseLength == 6) {
                                        echo "6 Months";
                                    } elseif ($leaseLength == 12) {
                                        echo "1 Year";
                                    } elseif ($leaseLength == 24) {
                                        echo "2 Years";
                                    } else {
                                        echo $leaseLength . " months";
                                    }
                                ?>
                            </li>
                            <li><strong>Security Deposit* (2 + 1):</strong> RM<?php echo number_format($property['SecurityDeposit'], 2); ?><br>
                                <div class="caption-text">
                                    <small>The security deposit consists of 2 months' rent as a refundable deposit and 1 month's rent as an advance payment. This protects the property owner against damages or unpaid rent.</small>
                                </div>
                            </li>  
                        </ul>
                    </div>

                    <!-- Monthly Rent Per Person -->
                    <div class="contact-box mb-4 shadow p-2 rounded">
                        <h5 class="fw-bold">Monthly Rent Per Person</h5>
                        <p>RM<?php echo number_format($property['MonthlyRentPerPerson'], 2); ?>/person</p>
                    </div>

                    <!-- Google Maps Section -->
                    <div class="contact-box mb-4 shadow p-2 rounded">
                        <h5 class="fw-bold">Google Maps</h5>
                        <p>
                            <a href="<?php echo htmlspecialchars($property['google_maps_url']); ?>" target="_blank" class="btn btn-outline-primary w-100">View Location on Google Maps</a>
                        </p>
                    </div>

                   <!-- Rental Agreement -->
                    <div class="contact-box mb-4 shadow p-2 rounded">
                        <h5 class="fw-bold">Rental Agreement</h5>
                        <?php if (!empty($property['RentalAgreement'])): ?>
                            <p>
                                <a href="<?php echo htmlspecialchars($property['RentalAgreement']); ?>"target="_blank" class="btn btn-outline-primary w-100">Download Rental Agreement</a>
                            </p>
                        <?php else: ?>
                            <p class="text-muted">No rental agreement provided for this property.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Contact Owner Section -->
                    <div class="contact-box shadow p-2 rounded">
                        <?php if ($ownerDetails): ?>
                            <!-- Display Owner's Profile Picture -->
                            <div class="d-flex align-items-center mb-3">
                                <img 
                                    src="<?php 
                                        echo !empty($ownerDetails['profile_picture']) && file_exists($ownerDetails['profile_picture']) ? htmlspecialchars($ownerDetails['profile_picture']) : 'uploads/default-user.png'; 
                                    ?>" 
                                    alt="Owner's Picture" 
                                    class="rounded-circle me-3" 
                                    width="50" 
                                    height="50">
                                <div>
                                    <p class="mb-0">
                                        <a href="HomeownerProfile.php?id=<?php echo urlencode($property['UserID']); ?>" class="text-decoration-none fw-bold">
                                            <?php echo htmlspecialchars($ownerDetails['name']); ?>
                                        </a>
                                    </p>
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($ownerDetails['user_type']); ?></p>
                                </div>
                            </div>
                        <?php else: ?>
                            <p>Owner details are unavailable at the moment.</p>
                        <?php endif; ?>

                        <!-- WhatsApp Button -->
                        <?php if ($_SESSION['user_type'] === 'student'): ?>
                            <?php if (!empty($formattedPhoneNumber)): ?>
                                <a href="https://wa.me/<?php echo htmlspecialchars($formattedPhoneNumber); ?>?text=Hi%20<?php echo urlencode($ownerDetails['name']); ?>,%20I%20am%20interested%20in%20your%20property%20listed%20on%20our%20platform."
                                    target="_blank"
                                    class="btn btn-outline-primary w-100">
                                    WhatsApp Owner
                                </a>
                            <?php else: ?>
                                <button class="btn btn-outline-primary w-100 disabled">Phone Number Not Available</button>
                            <?php endif; ?>


                            <!-- Book Property Button -->
                            <button id="bookPropertyBtn" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#bookingFormModal">Book Property</button>
                        <?php endif; ?>
                    </div>
                </div>


                <!-- Rate and Review Section -->
                <div class="mt-5">
                    <div class="card p-4 shadow-sm border-0">
                    <!-- Display All Reviews -->
                        <h5 class="fw-bold mt-5">Reviews</h5>
                        <?php
                        // Fetch reviews for the property
                        $reviewQuery = "SELECT r.Rating, r.Comment, r.Date, u.name, u.profile_picture
                                        FROM review r
                                        INNER JOIN users u ON r.UserID = u.UserID
                                        WHERE r.PropertyID = ?
                                        ORDER BY r.Date DESC";
                        $stmtReviews = $conn->prepare($reviewQuery);
                        if (!$stmtReviews) {
                            die("Error preparing reviews query: " . $conn->error);
                        }
                        $stmtReviews->bind_param("i", $propertyID);
                        $stmtReviews->execute();
                        $resultReviews = $stmtReviews->get_result();

                        if ($resultReviews->num_rows > 0):
                            while ($review = $resultReviews->fetch_assoc()):
                                $reviewerPicture = !empty($review['profile_picture']) && file_exists($review['profile_picture'])
                                                    ? $review['profile_picture']
                                                    : 'uploads/default-user.png';
                                ?>
                                <div class="card mb-3 p-3 shadow-sm border-0">
                                    <div class="d-flex align-items-center mb-3">
                                        <img src="<?php echo htmlspecialchars($reviewerPicture); ?>" alt="Reviewer Picture" class="profile-picture me-3">
                                        <div>
                                            <p class="mb-0 fw-bold"><?php echo htmlspecialchars($review['name']); ?></p>
                                            <p class="text-muted small mb-0"><?php echo date("d/m/Y", strtotime($review['Date'])); ?></p>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi <?php echo $i <= $review['Rating'] ? 'bi-star-fill text-warning' : 'bi-star text-muted'; ?>" style="font-size: 1.2rem;"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($review['Comment'])); ?></p>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted">No reviews yet for this property.</p>
                        <?php endif; ?>
                        <?php $stmtReviews->close(); ?>
                        </div>
                </div>
            </div>
        </div>


        <!-- Modal for Booking Form -->
        <div class="modal fade" id="bookingFormModal" tabindex="-1" aria-labelledby="bookingFormModalLabel">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bookingFormModalLabel">Booking Form</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Booking Form will be dynamically loaded here -->
                        <iframe id="bookingFormIframe" src="" style="width: 100%; height: 500px; border: none;"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </main>

        <?php include 'Footer.php'; ?>
    </div>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <script src="script.js"></script>
   
    <script>
    // Pass the propertyID safely as a JavaScript variable
    const propertyID = <?php echo json_encode($propertyID); ?>;


    document.getElementById("bookPropertyBtn").addEventListener("click", function () {
        const iframe = document.getElementById("bookingFormIframe");
        iframe.src = `BookingForm.php?id=${propertyID}`; // Set iframe source dynamically
    });


    // Show/hide review form when Rate button is clicked
    document.getElementById("rateButton").addEventListener("click", function () {
        var reviewForm = document.getElementById("reviewForm");
        reviewForm.style.display = (reviewForm.style.display === "none") ? "block" : "none";
    });


    // Update the rating value when a star is clicked
    const ratingStars = document.querySelectorAll('#ratingStars i');
    const ratingInput = document.getElementById('ratingInput');


    ratingStars.forEach(star => {
        star.addEventListener('click', function () {
            const ratingValue = star.getAttribute('data-value');
            ratingInput.value = ratingValue;


            // Update the star colors
            ratingStars.forEach(s => {
                if (s.getAttribute('data-value') <= ratingValue) {
                    s.classList.remove('text-muted');
                    s.classList.add('text-warning');
                } else {
                    s.classList.remove('text-warning');
                    s.classList.add('text-muted');
                }
            });
        });
    });
   
    //star rating interaction
    document.addEventListener('DOMContentLoaded', function () {
        const stars = document.querySelectorAll('#ratingStars i');
        const ratingInput = document.getElementById('ratingInput');


        stars.forEach(star => {
            star.addEventListener('click', () => {
                // Update the hidden input with the selected rating value
                const ratingValue = star.getAttribute('data-value');
                ratingInput.value = ratingValue;


                // Highlight stars up to the selected value
                stars.forEach(s => {
                    if (s.getAttribute('data-value') <= ratingValue) {
                        s.classList.remove('text-muted');
                        s.classList.add('text-warning'); // Highlight selected stars in yellow
                    } else {
                        s.classList.remove('text-warning');
                        s.classList.add('text-muted'); // Dim unselected stars
                    }
                });
            });
        });
    });


    </script>
</body>
</html>