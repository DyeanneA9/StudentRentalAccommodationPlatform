<?php
include("Authenticate.php");
include("config.php");
include("Navigation.php");
include("getUserProfile.php");

// Make sure the owner's phone number is available
$ownerPhoneNumber = isset($ownerDetails['phone_number']) ? $ownerDetails['phone_number'] : null;

// Check if a phone number exists
if ($ownerPhoneNumber) {
    // Format the phone number (remove any non-digit characters and ensure it’s in international format)
    $formattedPhoneNumber = preg_replace('/\D/', '', $ownerPhoneNumber); // Remove non-digit characters
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
        $ownerDetails = getUserProfile($property['UserID']);
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

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
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
                            <li><strong>Lease Term:</strong> <?php echo htmlspecialchars($property['LeaseLength']); ?> months</li>
                            <li><strong>Total Tenant:</strong> <?php echo htmlspecialchars($property['TotalTenants']); ?> people</li>
                            <li>
                                <strong>Security Deposit* (2 + 1):</strong> RM<?php echo number_format($property['SecurityDeposit'], 2); ?> 
                                <br>
                                <small class="text-muted">
                                    The security deposit consists of 2 months' rent as a refundable deposit and 1 month's rent as an advance payment. This protects the property owner against damages or unpaid rent.
                                </small>
                            </li>  
                        </ul>
                    </div>

                    <!-- Proximity Section -->
                    <div class="contact-box mb-4 shadow p-2 rounded">
                        <h5 class="fw-bold">Proximity</h5>
                        <p><?php echo htmlspecialchars($property['Proximity']); ?> km from College/University</p>
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
                                <img src="<?php echo htmlspecialchars($ownerDetails['profile_picture']); ?>" alt="Owner's Picture" class="rounded-circle me-3" width="50" height="50">
                                <div>
                                <p class="mb-0">
                                    <a href="HomeownerProfile.php?id=<?php echo urlencode($property['UserID']); ?>" class="text-decoration-none fw-bold"><?php echo htmlspecialchars($ownerDetails['name']); ?></a>
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
                                <a href="https://wa.me/<?php echo $formattedPhoneNumber; ?>?text=Hi%20<?php echo urlencode($ownerDetails['name']); ?>,%20I%20am%20interested%20in%20your%20property%20listed%20on%20our%20platform." 
                                target="_blank" 
                                class="btn btn-outline-primary w-100">
                                    WhatsApp Owner
                                </a>
                            <?php else: ?>
                                <button class="btn btn-outline-primary w-100">WhatsApp Owner</button>
                            <?php endif; ?>

                            <button id="bookPropertyBtn" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#bookingFormModal">Book Property</button>
                        <?php else: ?>
                            <!-- Optionally, display nothing or a message for non-students -->
                        <?php endif; ?>
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

        <!-- Footer Section-->
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
    </script>
</body>
</html>
