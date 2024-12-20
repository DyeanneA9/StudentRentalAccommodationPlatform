<?php
session_start();
include("config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate session and inputs
    if (!isset($_SESSION['UserID'])) {
        die("Error: User not logged in.");
    }

    $UserID = intval($_SESSION['UserID']);

    // Collect form inputs and sanitize them
    $propertyType = htmlspecialchars(trim($_POST['propertyType']));
    $address = htmlspecialchars(trim($_POST['address']));
    $googleMapsUrl = filter_var($_POST['googleMapsLink'], FILTER_SANITIZE_URL);
    $monthlyRent = floatval($_POST['monthlyRent']);
    $securityDeposit = floatval($_POST['securityDeposit']);
    $leaseLength = intval($_POST['leaseLength']);
    $noOfBedrooms = intval($_POST['noOfBedrooms']);
    $noOfBathrooms = intval($_POST['noOfBathrooms']);
    $totalTenants = intval($_POST['totalTenants']);
    $proximityToCollege = htmlspecialchars(trim($_POST['proximityToCollege']));
    $description = htmlspecialchars(trim($_POST['propertyDescription']));
    $furnishingDetails = htmlspecialchars(trim($_POST['furnishing']));
    $amenities = isset($_POST['amenities']) ? implode(", ", array_map('htmlspecialchars', $_POST['amenities'])) : '';

    // Calculate Monthly Rent Per Person 
    $monthlyRentPerPerson = ($totalTenants > 0) ? ($monthlyRent / $totalTenants) : 0;

    // File Upload: Handle Property Photos
    $uploadedImages = [];
    $target_dir = "uploads/" . $UserID . "/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Property Photos
    $totalFiles = count($_FILES['propertyPhotos']['name']);
    for ($i = 0; $i < $totalFiles; $i++) {
        if ($_FILES['propertyPhotos']['error'][$i] === UPLOAD_ERR_OK) {
            $fileName = uniqid() . "-" . basename($_FILES['propertyPhotos']['name'][$i]);
            $target_file = $target_dir . $fileName;

            if (move_uploaded_file($_FILES['propertyPhotos']['tmp_name'][$i], $target_file)) {
                $uploadedImages[] = $target_file;
            }
        }
    }

    // Convert uploaded images array to JSON
    $photosJson = json_encode($uploadedImages, JSON_UNESCAPED_SLASHES);

    // File Upload: Property Grant and Rental Agreement
    $propertyGrantPath = '';
    $rentalAgreementPath = '';

    if (isset($_FILES['PropertyGrant']) && $_FILES['PropertyGrant']['error'] === UPLOAD_ERR_OK) {
        $propertyGrantName = uniqid() . "-" . basename($_FILES['PropertyGrant']['name']);
        $propertyGrantPath = $target_dir . $propertyGrantName;
        move_uploaded_file($_FILES['PropertyGrant']['tmp_name'], $propertyGrantPath);
    }

    if (isset($_FILES['RentalAgreement']) && $_FILES['RentalAgreement']['error'] === UPLOAD_ERR_OK) {
        $rentalAgreementName = uniqid() . "-" . basename($_FILES['RentalAgreement']['name']);
        $rentalAgreementPath = $target_dir . $rentalAgreementName;
        move_uploaded_file($_FILES['RentalAgreement']['tmp_name'], $rentalAgreementPath);
    }

    // Prepare SQL Query
    $sql = "INSERT INTO property (
                UserID, 
                PropertyType, 
                PropertyAddress, 
                google_maps_url, 
                PropertyPrice, 
                MonthlyRentPerPerson, 
                SecurityDeposit, 
                NoOfBedroom, 
                NoOfBathroom, 
                LeaseLength, 
                TotalTenants, 
                Proximity, 
                Description, 
                Furnishing, 
                PropertyAmenities, 
                Photo, 
                PropertyGrant, 
                RentalAgreement, 
                ListedDate
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing SQL statement: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param(
        "isssidiiississssss",
        $UserID,
        $propertyType,
        $address,
        $googleMapsUrl,
        $monthlyRent,
        $monthlyRentPerPerson,
        $securityDeposit,
        $noOfBedrooms,
        $noOfBathrooms,
        $leaseLength,
        $totalTenants,
        $proximityToCollege,
        $description,
        $furnishingDetails,
        $amenities,
        $photosJson,
        $propertyGrantPath,
        $rentalAgreementPath
    );

    // Execute Query
    if ($stmt->execute()) {
        header("Location: AddProperty.php?success=1");
        exit();
    } else {
        echo "<p style='color: red;'>Error: " . htmlspecialchars($stmt->error) . "</p>";
    }

    $stmt->close();
    $conn->close();
}
?>
