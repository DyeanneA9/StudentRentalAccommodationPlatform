<?php
session_start();
include("config.php");

if (!isset($_GET['id']) || !isset($_GET['type'])) {
    die("Error: Missing parameters.");
}

$propertyID = intval($_GET['id']);
$type = $_GET['type'];

$column = "";
switch ($type) {
    case "PropertyGrant":
        $column = "PropertyGrant";
        break;
    case "RentalAgreement":
        $column = "RentalAgreement";
        break;
    default:
        die("Error: Invalid document type.");
}

// Fetch the file path
$sql = "SELECT $column FROM property WHERE PropertyID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $propertyID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $filePath = $result->fetch_assoc()[$column];
    if (file_exists($filePath)) {
        unlink($filePath); // Delete the file from storage
    }
    
    // Update the database to remove the file reference
    $updateSql = "UPDATE property SET $column = NULL WHERE PropertyID = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("i", $propertyID);
    if ($updateStmt->execute()) {
        $_SESSION['delete_success'] = ucfirst($type) . " deleted successfully.";
    } else {
        $_SESSION['delete_error'] = "Failed to delete " . ucfirst($type) . ".";
    }
} else {
    $_SESSION['delete_error'] = "Document not found.";
}

header("Location: EditProperty.php?id=" . $propertyID);
exit();
?>
