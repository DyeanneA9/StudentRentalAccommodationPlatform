<?php
session_start();
include("config.php");

// Ensure the user is authorized to update property (homeowner)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve PropertyID from GET or POST
    $propertyID = $_GET['id'] ?? $_POST['id'] ?? null;
    if (!$propertyID) {
        die("Error: PropertyID not found in the URL or POST data.");
    }

    // Fetch existing property data
    $existingDataSql = "SELECT PropertyGrant, RentalAgreement, Photo FROM property WHERE PropertyID = ?";
    $stmt = $conn->prepare($existingDataSql);
    $stmt->bind_param("i", $propertyID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $propertyData = $result->fetch_assoc();
        $propertyGrantPath = $propertyData['PropertyGrant'];
        $rentalAgreementPath = $propertyData['RentalAgreement'];
        $existingPhotos = $propertyData['Photo'];
    } else {
        die("Error: Property not found.");
    }
    $stmt->close();

    // Sanitize and assign POST values
    $propertyType = $_POST['propertyType'];
    $address = $_POST['address'];
    $googleMapsUrl = $_POST['googleMapsLink'];
    $monthlyRent = $_POST['monthlyRent'];
    $totalTenants = $_POST['totalTenants'];
    $monthlyRentPerPerson = ($totalTenants > 0) ? ($monthlyRent / $totalTenants) : 0;
    $securityDeposit = $_POST['securityDeposit'];
    $leaseLength = $_POST['leaseLength'];
    $noOfBedrooms = $_POST['noOfBedrooms'];
    $noOfBathrooms = $_POST['noOfBathrooms'];
    $propertyDescription = $_POST['propertyDescription'];
    $furnishing = $_POST['furnishing'];
    $amenities = isset($_POST['amenities']) ? implode(',', $_POST['amenities']) : '';
    $bankNumber = htmlspecialchars(trim($_POST['bankNumber'])); 
    $bankName = htmlspecialchars(trim($_POST['bankName'])); 

    // Initialize photo array and upload directory
    $photoArray = [];
    $uploadDir = "uploads/";
    $userID = $_SESSION['UserID'];
    $targetDir = $uploadDir . $userID . "/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Handle property photos upload
    if (isset($_FILES['propertyPhotos']) && $_FILES['propertyPhotos']['error'][0] == 0) {
        foreach ($_FILES['propertyPhotos']['name'] as $index => $fileName) {
            $fileTmpName = $_FILES['propertyPhotos']['tmp_name'][$index];
            $newFileName = uniqid() . "-" . basename($fileName);
            $targetFilePath = $targetDir . $newFileName;
            if (move_uploaded_file($fileTmpName, $targetFilePath)) {
                $photoArray[] = str_replace("\\", "/", $targetFilePath);
            }
        }

        // Merge newly uploaded photos with existing ones
        $existingPhotoArray = $existingPhotos ? json_decode($existingPhotos, true) : [];
        $photoArray = array_merge($existingPhotoArray, $photoArray);
    } else {
        $photoArray = $existingPhotos ? json_decode($existingPhotos, true) : [];
    }

    // Handle Property Grant upload
    if (isset($_FILES['PropertyGrant']) && $_FILES['PropertyGrant']['error'] == 0) {
        $propertyGrantFileName = uniqid() . "-" . basename($_FILES['PropertyGrant']['name']);
        $propertyGrantPath = $targetDir . $propertyGrantFileName;
        move_uploaded_file($_FILES['PropertyGrant']['tmp_name'], $propertyGrantPath);
        $propertyGrantPath = str_replace("\\", "/", $propertyGrantPath);
    }

    // Handle Rental Agreement upload
    if (isset($_FILES['RentalAgreement']) && $_FILES['RentalAgreement']['error'] == 0) {
        $rentalAgreementFileName = uniqid() . "-" . basename($_FILES['RentalAgreement']['name']);
        $rentalAgreementPath = $targetDir . $rentalAgreementFileName;
        move_uploaded_file($_FILES['RentalAgreement']['tmp_name'], $rentalAgreementPath);
        $rentalAgreementPath = str_replace("\\", "/", $rentalAgreementPath);
    }

    // JSON encode photo paths
    $updatedPhotos = json_encode($photoArray, JSON_UNESCAPED_SLASHES);

    // Update the property in the database
    $updateSql = "UPDATE property SET
                    PropertyType = ?, 
                    PropertyAddress = ?, 
                    google_maps_url = ?, 
                    PropertyPrice = ?, 
                    MonthlyRentPerPerson = ?, 
                    SecurityDeposit = ?, 
                    LeaseLength = ?, 
                    NoOfBedroom = ?, 
                    NoOfBathroom = ?, 
                    TotalTenants = ?, 
                    Description = ?, 
                    Furnishing = ?, 
                    PropertyAmenities = ?,
                    bankNumber = ?,
                    bankName = ?,  
                    Photo = ?, 
                    PropertyGrant = ?, 
                    RentalAgreement = ?, 
                    is_approved = CASE WHEN is_approved = -1 THEN 0 ELSE is_approved END
                    WHERE PropertyID = ?";

    // Prepare statement for updating the property data
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param(
        "sssiiiiiiissssssssi", 
        $propertyType, 
        $address, 
        $googleMapsUrl, 
        $monthlyRent, 
        $monthlyRentPerPerson, 
        $securityDeposit, 
        $leaseLength, 
        $noOfBedrooms, 
        $noOfBathrooms, 
        $totalTenants, 
        $propertyDescription, 
        $furnishing, 
        $amenities, 
        $bankNumber,
        $bankName,
        $updatedPhotos, 
        $propertyGrantPath, 
        $rentalAgreementPath, 
        $propertyID
    );

    // Execute the update query and handle success or failure
    if ($stmt->execute()) {
        // Log activity for the update
        $userSql = "SELECT name FROM users WHERE UserID = ?";
        $userStmt = $conn->prepare($userSql);
        $userStmt->bind_param("i", $userID);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        $userName = $userResult->fetch_assoc()['name'] ?? "Unknown User";

        // Log the activity to the database
        $activityDescription = "The user $userName has updated property ID $propertyID.";
        $activitySql = "INSERT INTO activities (UserID, action, created_at) VALUES (?, ?, NOW())";
        $activityStmt = $conn->prepare($activitySql);
        $activityStmt->bind_param("is", $userID, $activityDescription);
        $activityStmt->execute();

        $_SESSION['success_message'] = "Property updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error updating property: " . $stmt->error;
    }

    // Redirect back to the property edit page
    header("Location: EditProperty.php?id=" . $propertyID);
    exit();
}
?>
