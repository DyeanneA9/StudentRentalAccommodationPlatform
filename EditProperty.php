<?php
include("Auth.php");
include("config.php");
include("Navbar.php");

if (!isset($_SESSION['UserID'])) {
    die("Error: User not logged in.");
}

if (isset($_GET['id'])) {
    $property_id = $_GET['id'];
} else {
    die("Error: Property ID not provided.");
}

// message for delete property photo
if (isset($_SESSION['delete_success'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> ' . $_SESSION['delete_success'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['delete_success']);  // Unset the session variable after displaying the message
} elseif (isset($_SESSION['delete_error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> ' . $_SESSION['delete_error'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['delete_error']);  // Unset the session variable after displaying the message
}

// message for updating property
if (isset($_SESSION['success_message'])) {
    echo "
        <div class='alert alert-success alert-dismissible fade show' role='alert'>
            <i class='fas fa-check-circle'></i> <strong>Success!</strong> " . $_SESSION['success_message'] . "
            <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                <span aria-hidden='true'>&times;</span>
            </button>
        </div>
    ";
    // Unset the success message after displaying it
    unset($_SESSION['success_message']);
} elseif (isset($_SESSION['error_message'])) {
    echo "
        <div class='alert alert-danger alert-dismissible fade show' role='alert'>
            <i class='fas fa-exclamation-circle'></i> <strong>Error!</strong> " . $_SESSION['error_message'] . "
        </div>
    ";
    // Unset the error message after displaying it
    unset($_SESSION['error_message']);
}

$propertyID = $_GET['id'];

$sql = "SELECT * FROM property WHERE PropertyID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $propertyID);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $propertyData = $result->fetch_assoc();
        $photos = $propertyData['Photo']; 
        $photoArray = explode(",", $photos);
    } else {
        die("Error: Property not found.");
    }
} else {
    die("Error retrieving property: " . $stmt->error);
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Property</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="wrapper">
        <main class="content">
            <div class="container my-5">
                <div class="card">
                    <div class="form-header">
                        <h5>Edit Property</h5>
                        <p class="mb-0">Please modify the property details below</p>
                    </div>
                    <div class="card-body">
                        <form action="EditProperty_action.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?php echo $propertyID; ?>" />
                            
                            <!-- Property Type and Address Section -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="propertyType" class="form-label">Property Type*</label>
                                    <select class="form-select" id="propertyType" name="propertyType" required>
                                        <option value="A Room" <?php echo ($propertyData['PropertyType'] == 'A Room') ? 'selected' : ''; ?>>A Room</option>
                                        <option value="Shared Room" <?php echo ($propertyData['PropertyType'] == 'Shared Room') ? 'selected' : ''; ?>>Shared Room</option>
                                        <option value="Shared Apartment" <?php echo ($propertyData['PropertyType'] == 'Shared Apartment') ? 'selected' : ''; ?>>Shared Apartment</option>
                                        <option value="Shared House" <?php echo ($propertyData['PropertyType'] == 'Shared House') ? 'selected' : ''; ?>>Shared House</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="address" class="form-label">Full Address*</label>
                                    <textarea class="form-control" id="address" name="address" rows="2" required><?php echo $propertyData['PropertyAddress']; ?></textarea>
                                </div>
                            </div>

                            <!-- Proximity and Google Maps URL -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="proximityToCollege" class="form-label">Proximity to College/University (in km)*</label>
                                    <input type="number" step="0.1" class="form-control" id="proximityToCollege" name="proximityToCollege" value="<?php echo $propertyData['Proximity']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="googleMapsLink" class="form-label">Google Maps Link*</label>
                                    <input type="url" class="form-control" id="googleMapsLink" name="googleMapsLink" value="<?php echo $propertyData['google_maps_url']; ?>" required>
                                </div>
                            </div>

                            <!-- Monthly Rent, Security Deposit, and Lease Length -->
                            <h6 class="section">Rental Information</h6>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="monthlyRent" class="form-label">Monthly Rent (RM)*</label>
                                    <input type="number" class="form-control" id="monthlyRent" name="monthlyRent" value="<?php echo $propertyData['PropertyPrice']; ?>" required oninput="calculateRentPerPerson()">
                                </div>

                                <div class="col-md-4">
                                    <label for="totalTenants" class="form-label">Total Tenants Needed*</label>
                                    <input type="number" class="form-control" id="totalTenants" name="totalTenants" value="<?php echo $propertyData['TotalTenants']; ?>" required oninput="calculateRentPerPerson()">
                                </div>

                                <div class="col-md-4">
                                    <label for="monthlyRentPerPerson" class="form-label">Monthly Rent Per Person (RM)*</label>
                                    <input type="text" class="form-control" id="monthlyRentPerPerson" name="monthlyRentPerPerson" placeholder="Auto-calculated" value="<?php echo $propertyData['MonthlyRentPerPerson']; ?>" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label for="securityDeposit" class="form-label">Security Deposit (RM) (2+1)*</label>
                                    <input type="number" class="form-control" id="securityDeposit" name="securityDeposit" value="<?php echo $propertyData['SecurityDeposit']; ?>" required>
                                    <small class="form-text text-muted">
                                        The security deposit is typically 2 months of rent, plus an additional 1 month as a refundable security fee.
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <label for="leaseLength" class="form-label">Lease Length*</label>
                                    <select class="form-select" id="leaseLength" name="leaseLength" required>
                                        <option value="6 Months" <?php echo ($propertyData['LeaseLength'] == '6') ? 'selected' : ''; ?>>6 Months</option>
                                        <option value="1 Year" <?php echo ($propertyData['LeaseLength'] == '1') ? 'selected' : ''; ?>>1 Year</option>
                                        <option value="2 Years" <?php echo ($propertyData['LeaseLength'] == '2') ? 'selected' : ''; ?>>2 Years</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Rental Agreement and Property Grant Display -->
                            <h6 class="section">Documents</h6>
                            <div class="row mb-3">
                                <!-- Property Grant -->
                                <div class="col-md-6">
                                    <label for="PropertyGrant" class="form-label">Property Grant (Proof of Ownership)*</label>
                                    <?php if (!empty($propertyData['PropertyGrant'])): ?>
                                        <div class="mb-2">
                                            <a href="<?php echo htmlspecialchars($propertyData['PropertyGrant']); ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                                View Property Grant
                                            </a>
                                            <a href="DeleteDocument.php?id=<?php echo urlencode($propertyID); ?>&type=PropertyGrant" class="btn btn-danger btn-sm">
                                                Delete
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted">No Property Grant uploaded.</p>
                                    <?php endif; ?>
                                    <input type="file" id="PropertyGrant" name="PropertyGrant" class="form-control" accept="application/pdf,image/*">
                                </div>

                                <!-- Rental Agreement -->
                                <div class="col-md-6">
                                    <label for="RentalAgreement" class="form-label">Rental Agreement (PDF)*</label>
                                    <?php if (!empty($propertyData['RentalAgreement'])): ?>
                                        <div class="mb-2">
                                            <a href="<?php echo htmlspecialchars($propertyData['RentalAgreement']); ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                                View Rental Agreement
                                            </a>
                                            <a href="DeleteDocument.php?id=<?php echo urlencode($propertyID); ?>&type=RentalAgreement" class="btn btn-danger btn-sm">
                                                Delete
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted">No Rental Agreement uploaded.</p>
                                    <?php endif; ?>
                                    <input type="file" id="RentalAgreement" name="RentalAgreement" class="form-control" accept=".pdf">
                                </div>
                            </div>


                            <!-- No. of Bedrooms, Bathrooms, and Property Description -->
                            <h6 class="section">Furnishing and Amenities</h6>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="noOfBedrooms" class="form-label">No. of Bedrooms*</label>
                                    <input type="number" class="form-control" id="noOfBedrooms" name="noOfBedrooms" placeholder="Enter number of bedrooms" value="<?php echo $propertyData['NoOfBedroom']; ?>" required>

                                    <label for="noOfBathrooms" class="form-label">No. of Bathrooms*</label>
                                    <input type="number" class="form-control" id="noOfBathrooms" name="noOfBathrooms" placeholder="Enter number of bathrooms" value="<?php echo $propertyData['NoOfBathroom']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="propertyDescription" class="form-label">Property Description*</label>
                                    <textarea class="form-control" id="propertyDescription" name="propertyDescription" rows="4" placeholder="Enter property description" required><?php echo $propertyData['Description']; ?></textarea>
                                </div>
                            </div>

                            <!-- Furnishing Details -->
                            <div class="mb-3">
                                <label class="form-label">Furnishing Details*</label>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" id="fullyFurnished" name="furnishing" value="Fully Furnished" <?php echo ($propertyData['Furnishing'] == 'Fully Furnished') ? 'checked' : ''; ?> onchange="updateFurnishingDetails(this.value)">
                                            <label class="form-check-label" for="fullyFurnished">
                                                <i class="bi bi-house-fill"></i> Fully Furnished
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" id="partiallyFurnished" name="furnishing" value="Partially Furnished" <?php echo ($propertyData['Furnishing'] == 'Partially Furnished') ? 'checked' : ''; ?> onchange="updateFurnishingDetails(this.value)">
                                            <label class="form-check-label" for="partiallyFurnished">
                                                <i class="bi bi-house-door"></i> Partially Furnished
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" id="notFurnished" name="furnishing" value="Not Furnished" <?php echo ($propertyData['Furnishing'] == 'Not Furnished') ? 'checked' : ''; ?> onchange="updateFurnishingDetails(this.value)">
                                            <label class="form-check-label" for="notFurnished">
                                                <i class="bi bi-house"></i> Not Furnished
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Amenities -->
                            <div class="mb-3">
                                <label class="form-label">Amenities*</label>
                                <div class="row">
                                    <!-- Iterate through amenities and check if they are selected -->
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="wifi" name="amenities[]" value="WiFi" <?php echo (in_array('WiFi', explode(',', $propertyData['PropertyAmenities']))) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="wifi">
                                                <i class="bi bi-wifi"></i> WiFi
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="parking" name="amenities[]" value="Parking" <?php echo (in_array('Parking', explode(',', $propertyData['PropertyAmenities']))) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="parking">
                                                <i class="bi bi-car-front"></i> Parking
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="gym" name="amenities[]" value="Gym">
                                            <label class="form-check-label" for="gym">
                                                <i class="bi bi-dumbbell"></i> Gym
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="security" name="amenities[]" value="24/7 Security">
                                            <label class="form-check-label" for="security">
                                                <i class="bi bi-shield-lock"></i> 24/7 Security
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="pool" name="amenities[]" value="Pool">
                                            <label class="form-check-label" for="pool">
                                                <i class="bi bi-water"></i> Pool
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Property Photos -->
                            <div class="mb-3">
                                <label for="propertyPhotos" class="form-label">Property Photos*</label>
                                <?php 
                                $photos = $propertyData['Photo']; 
                                $photoArray = json_decode($photos, true);

                                // Check if the photo array is valid
                                if (is_array($photoArray)) {
                                    echo '<div class="row mb-3">';
                                    foreach ($photoArray as $photo) {
                                        echo '<div class="col-md-3">';
                                        echo '<img src="' . htmlspecialchars($photo) . '" alt="Property Image" class="img-thumbnail" style="width: 100%; height: auto%;">';
                                        // Concatenate PHP variables properly for href
                                        echo '<a href="DeletePropertyPhoto.php?id=' . urlencode($propertyID) . '&photo=' . urlencode($photo) . '" class="btn btn-danger btn-sm mt-2">Delete</a>';
                                        echo '</div>';
                                    }
                                    echo '</div>';
                                } else {
                                    echo "Error: Invalid photo data.";
                                }
                                ?>

                                <!-- File Input for Uploading New Photos -->
                                <input type="file" id="propertyPhotos" name="propertyPhotos[]" class="form-control" accept="image/*" multiple>
                            </div>

                            <!-- Submit Button -->
                            <div class="text-end">
                                <?php if ($propertyData['is_approved'] == -1): ?>
                                    <!-- Resubmit Property Button -->
                                    <button type="submit" class="btn btn-warning">Resubmit Property</button>
                                <?php else: ?>
                                    <!-- Update Property Button -->
                                    <button type="submit" class="btn btn-primary">Update Property</button>
                                <?php endif; ?>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer Section-->
        <?php include 'Footer.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>

    <script>
    function calculateRentPerPerson() {
        // Get values of Monthly Rent and Total Tenants
        const monthlyRent = parseFloat(document.getElementById("monthlyRent").value) || 0;
        const totalTenants = parseInt(document.getElementById("totalTenants").value) || 1;

        // Calculate Monthly Rent Per Person
        const rentPerPerson = totalTenants > 0 ? (monthlyRent / totalTenants).toFixed(2) : 0;

        // Update the Monthly Rent Per Person field
        document.getElementById("monthlyRentPerPerson").value = rentPerPerson;
    }
    </script>

</body>
</html>
