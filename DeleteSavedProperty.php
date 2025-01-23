<?php
session_start();
include("config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and decode the JSON input
    $data = json_decode(file_get_contents('php://input'), true);

    // Retrieve the property ID and tenant ID
    $propertyId = $data['propertyId'] ?? null;
    $tenantId = $_SESSION['UserID'] ?? null;

    // Check if both property ID and tenant ID are valid
    if ($tenantId && $propertyId) {
        // Prepare and execute the SQL query to delete the saved property
        $sql = "DELETE FROM saved_properties WHERE tenant_id = ? AND property_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $tenantId, $propertyId);

        // Execute the query and check if rows were affected
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'action' => 'deleted']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete saved property.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid tenant or property ID.']);
    }
}

$conn->close();
?>
