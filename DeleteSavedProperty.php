<?php
session_start();
include("config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $propertyId = $data['propertyId'];
    $tenantId = $_SESSION['UserID'] ?? null;

    if ($tenantId && $propertyId) {
        // Delete the property from the saved properties
        $sql = "DELETE FROM saved_properties WHERE tenant_id = ? AND property_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $tenantId, $propertyId);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
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
