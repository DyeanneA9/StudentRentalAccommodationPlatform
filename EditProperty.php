<?php
include("config.php");
include("Authenticate.php");
include("Navigation.php");

// Ensure the user is logged in
if (!isset($_SESSION['UserID'])) {
    die("Error: User not logged in.");
}

// Ensure Property ID is provided
if (isset($_GET['id'])) {
    $propertyID = $_GET['id'];
} else {
    die("Error: Property ID not provided.");
}

// Fetch property details
$sql = "SELECT * FROM property WHERE PropertyID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $propertyID);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $propertyData = $result->fetch_assoc();

        // Safely retrieve data or use fallback values
        $googleMapsLink = $propertyData['google_maps_url'] ?? '';
        $address = $propertyData['PropertyAddress'] ?? '';
        $bankNumber = $propertyData['bankNumber'] ?? ''; 
        $bankName = $propertyData['bankName'] ?? '';
        $photos = $propertyData['Photo'] ?? '[]';
        $photoArray = json_decode($photos, true) ?? [];
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="wrapper">
        <main class="content">
            <div class="container my-5">
                <!-- Display Success or Error Messages -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success">
                        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    </div>
                <?php elseif (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger">
                        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="form-header">
                        <h5>Edit Property</h5>
                        <p class="mb-0">Please modify the property details below</p>
                    </div>
                    <div class="card-body">
                        <form action="EditProperty_action.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?php echo $propertyID; ?>" />
                            
                            <!-- Property Type and Google Map Section -->
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
                                    <label for="googleMapsLink" class="form-label">Google Maps Link*</label>
                                    <input type="url" class="form-control" id="googleMapsLink" name="googleMapsLink" value="<?php echo htmlspecialchars($googleMapsLink); ?>" required>                                </div>
                            </div>

                            <!-- Address -->
                            <div class="row mb-3">
                                <label for="address" class="form-label">Full Address*</label>
                                <textarea class="form-control" id="address" name="address" rows="2" required><?php echo htmlspecialchars($address); ?></textarea>
                            </div>

                            <!-- Monthly Rent, Security Deposit, and Lease Length -->
                            <h6 class="section">Rental Information</h6>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="monthlyRent" class="form-label">Monthly Rent (RM)*</label>
                                    <input type="number" class="form-control" id="monthlyRent" name="monthlyRent" value="<?php echo $propertyData['PropertyPrice']; ?>" required oninput="calculateRentPerPersonEdit()">
                                </div>

                                <div class="col-md-4">
                                    <label for="totalTenants" class="form-label">Total Tenants Needed*</label>
                                    <input type="number" class="form-control" id="totalTenants" name="totalTenants" value="<?php echo $propertyData['TotalTenants']; ?>" required oninput="calculateRentPerPersonEdit()">
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

                            <!-- Payment Information -->
                            <h6 class="section">Payment Information</h6>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="bankNumber" class="form-label">Bank Account Number*</label>
                                    <input type="text" class="form-control" id="bankNumber" name="bankNumber" placeholder="Enter your bank number"  value="<?php echo $bankNumber; ?>" required>
                                    <small class="form-text text-muted">This will be used for payment-related purposes.</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="bankName" class="form-label">Bank Name*</label>
                                    <input type="text" class="form-control" id="bankName" name="bankName" placeholder="Enter your bank name"  value="<?php echo $bankName; ?>" required>
                                </div>
                            </div>

                            <!-- Documents Section -->
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

                            <!-- Furnishing and Amenities -->
                            <h6 class="section">Furnishing and Amenities</h6>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="noOfBedrooms" class="form-label">No. of Bedrooms*</label>
                                    <input type="number" class="form-control" id="noOfBedrooms" name="noOfBedrooms" value="<?php echo $propertyData['NoOfBedroom']; ?>" required>

                                    <label for="noOfBathrooms" class="form-label">No. of Bathrooms*</label>
                                    <input type="number" class="form-control" id="noOfBathrooms" name="noOfBathrooms" value="<?php echo $propertyData['NoOfBathroom']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="propertyDescription" class="form-label">Property Description*</label>
                                    <textarea class="form-control" id="propertyDescription" name="propertyDescription" rows="4" required><?php echo $propertyData['Description']; ?></textarea>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Furnishing Details*</label>
                                <div class="row">
                                    <!-- Furnishing options -->
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" id="fullyFurnished" name="furnishing" value="Fully Furnished" <?php echo ($propertyData['Furnishing'] == 'Fully Furnished') ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="fullyFurnished">Fully Furnished</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" id="partiallyFurnished" name="furnishing" value="Partially Furnished" <?php echo ($propertyData['Furnishing'] == 'Partially Furnished') ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="partiallyFurnished">Partially Furnished</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" id="notFurnished" name="furnishing" value="Not Furnished" <?php echo ($propertyData['Furnishing'] == 'Not Furnished') ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="notFurnished">Not Furnished</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Amenities Section -->
                            <div class="mb-3">
                                <label class="form-label">Amenities*</label>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="wifi" name="amenities[]" value="WiFi" <?php echo (in_array('WiFi', explode(',', $propertyData['PropertyAmenities']))) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="wifi">WiFi</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="parking" name="amenities[]" value="Parking" <?php echo (in_array('Parking', explode(',', $propertyData['PropertyAmenities']))) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="parking">Parking</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="gym" name="amenities[]" value="Gym">
                                            <label class="form-check-label" for="gym">Gym</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="security" name="amenities[]" value="24/7 Security">
                                            <label class="form-check-label" for="security">24/7 Security</label>
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
                                <button type="submit" class="btn btn-primary">Update Property</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </main>

        <?php include 'Footer.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>

</body>
</html>
