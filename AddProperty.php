<?php
include("config.php");
include("Authenticate.php");
include("Navigation.php");

if (!isset($_SESSION['UserID'])) {
    die("Error: User not logged in.");
}

// Check if property is successfully added
$successMessage = isset($_GET['success']) && $_GET['success'] == '1';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Property</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="wrapper">
        <main class="content">
            <div class="container my-5">
                <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
                <!-- Success Message -->
                <div class="alert alert-success" role="alert">
                    <h4 class="alert-heading">Success!</h4>
                    <p>Your property has been successfully submitted and is currently under review. Please wait for our approval.</p>
                    <hr>
                    <p class="mb-0">You can return to the dashboard or add another property.</p>
                </div>
                <?php else: ?>

                <!-- Property Form -->
                <div class="card">
                    <div class="form-header">
                        <h5>Add New Property</h5>
                        <p class="mb-0">Please fill in the property details below</p>
                    </div>
                    <div class="card-body">
                        <form action="AddProperty_action.php" method="POST" enctype="multipart/form-data">
                            
                        <!-- Property Type and Google map link Section -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="propertyType" class="form-label">Property Type*</label>
                                    <select class="form-select" id="propertyType" name="propertyType" required>
                                        <option value="" disabled selected>Select Property Type</option>
                                        <option value="A Room">A Room</option>
                                        <option value="Shared Room">Shared Room</option>
                                        <option value="Shared Apartment">Shared Apartment</option>
                                        <option value="Shared House">Shared House</option> 
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="googleMapsLink" class="form-label">Google Maps Link*</label>
                                    <input type="url" class="form-control" id="googleMapsLink" name="googleMapsLink" placeholder="Paste Your Property Location Google Maps URL" required>
                                </div>
                            </div>

                            <!-- Address Section -->
                            <div class="row mb-3">
                                <label for="address" class="form-label">Full Address*</label>
                                <textarea class="form-control" id="address" name="address" rows="2" placeholder="Enter property address" required></textarea>
                            </div>

                            <!-- Monthly Rent, Security Deposit, and Lease Length -->
                            <h6 class="section">Rental Information</h6>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="monthlyRent" class="form-label">Monthly Rent (RM)*</label>
                                    <input type="number" class="form-control" id="monthlyRent" name="monthlyRent" placeholder="Enter monthly rent" required oninput="calculateRentPerPerson()">
                                </div>
                                <div class="col-md-4">
                                    <label for="totalTenants" class="form-label">Total Tenants Needed*</label>
                                    <input type="number" class="form-control" id="totalTenants" name="totalTenants" placeholder="Enter total tenants needed" required oninput="calculateRentPerPerson()">
                                </div>
                                <div class="col-md-4">
                                    <label for="monthlyRentPerPerson" class="form-label">Monthly Rent Per Person (RM)*</label>
                                    <input type="text" class="form-control" id="monthlyRentPerPerson" name="monthlyRentPerPerson" placeholder="Auto-calculated" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label for="securityDeposit" class="form-label">Security Deposit (RM) (2+1)*</label>
                                    <input type="number" class="form-control" id="securityDeposit" name="securityDeposit" placeholder="Enter security deposit" required>
                                    <small class="form-text text-muted">
                                        The security deposit is typically 2 months of rent, plus an additional 1 month as a refundable security fee.
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <label for="leaseLength" class="form-label">Lease Length*</label>
                                    <select class="form-select" id="leaseLength" name="leaseLength" required>
                                        <option value="" disabled selected>Select lease length</option>
                                        <option value="1">1 day</option>
                                        <option value="6">6 Months</option>
                                        <option value="12">1 Year</option>
                                        <option value="24">2 Years</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Payment Information -->
                            <h6 class="section">Payment Information</h6>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="bankNumber" class="form-label">Bank Account Number*</label>
                                    <input type="text" class="form-control" id="bankNumber" name="bankNumber" placeholder="Enter your bank account number" required>
                                    <small class="form-text text-muted">This will be used for payment-related purposes.</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="bankName" class="form-label">Bank Name*</label>
                                    <input type="text" class="form-control" id="bankName" name="bankName" placeholder="Enter your bank name" required>
                                </div>
                            </div>

                            <h6 class="section">Document Upload</h6>
                            <!-- Property Grant and Rental Agreement -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="PropertyGrant" class="form-label">Property Grant (Proof of Ownership)*</label>
                                    <input type="file" id="PropertyGrant" name="PropertyGrant" class="form-control" accept="application/pdf,image/*" required>
                                    <small class="form-text text-muted">
                                        Please upload a scanned copy or digital image of the property grant or proof of ownership. This document will be reviewed by our admin team for verification purposes only.
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label for="RentalAgreement" class="form-label">Rental Agreement (PDF)*</label>
                                    <input type="file" id="RentalAgreement" name="RentalAgreement" class="form-control" accept=".pdf" required>
                                    <small class="form-text text-muted">
                                        Please upload a rental agreement. The document must be in PDF. This document will be reviewed by our admin team for verification purposes.
                                    </small>
                                </div>
                            </div>

                            <!-- No. of Bedrooms, Bathrooms, and Property Description -->
                            <h6 class="section">Furnishing and Amenities</h6>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="noOfBedrooms" class="form-label">No. of Bedrooms*</label>
                                    <input type="number" class="form-control" id="noOfBedrooms" name="noOfBedrooms" placeholder="Enter number of bedrooms" required>

                                    <label for="noOfBathrooms" class="form-label">No. of Bathrooms*</label>
                                    <input type="number" class="form-control" id="noOfBathrooms" name="noOfBathrooms" placeholder="Enter number of bathrooms" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="propertyDescription" class="form-label">Property Description*</label>
                                    <textarea class="form-control" id="propertyDescription" name="propertyDescription" rows="4" placeholder="Enter property description" required></textarea>
                                </div>
                            </div>

                            <!-- Furnishing Details -->
                            <div class="mb-3">
                                <label class="form-label">Furnishing Details*</label>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" id="fullyFurnished" name="furnishing" value="Fully Furnished" onchange="updateFurnishingDetails(this.value)">
                                            <label class="form-check-label" for="fullyFurnished">
                                                <i class="bi bi-house-fill"></i> Fully Furnished
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" id="partiallyFurnished" name="furnishing" value="Partially Furnished" onchange="updateFurnishingDetails(this.value)">
                                            <label class="form-check-label" for="partiallyFurnished">
                                                <i class="bi bi-house-door"></i> Partially Furnished
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" id="notFurnished" name="furnishing" value="Not Furnished" onchange="updateFurnishingDetails(this.value)">
                                            <label class="form-check-label" for="notFurnished">
                                                <i class="bi bi-house"></i> Not Furnished
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="furnishingDescription" class="mt-3"></div>

                            <!-- Amenities -->
                            <div class="mb-3">
                                <label class="form-label">Amenities*</label>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="wifi" name="amenities[]" value="WiFi">
                                            <label class="form-check-label" for="wifi">
                                                <i class="bi bi-wifi"></i> WiFi
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="parking" name="amenities[]" value="Parking">
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
                                <input type="file" id="propertyPhotos" name="propertyPhotos[]" class="form-control" accept="image/*" multiple>
                            </div>

                            <!-- Submit Button -->
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">Add Property</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php endif; ?>
            </div> 
        </main>

        <!-- Footer Section-->
        <?php include 'Footer.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>

</body>
</html>

