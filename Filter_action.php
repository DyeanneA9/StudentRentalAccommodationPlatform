<?php
include("config.php");

// Initialize the query
$sql = "SELECT * FROM properties WHERE 1=1";

// Add filters based on user input
if (!empty($_GET['PropertyType'])) {
    $PropertyType = $conn->real_escape_string($_GET['PropertyType']);
    $sql .= " AND PropertyType = '$PropertyType'";
}

if (!empty($_GET['PropertyPrice'])) {
    $PropertyPrice = $conn->real_escape_string($_GET['PropertyPrice']);
    $sql .= " AND PropertyPrice <= '$PropertyPrice'";
}

// Execute the query
$result = $conn->query($sql);

// Check if results were found
if ($result && $result->num_rows > 0) {
    $properties = [];
    while ($row = $result->fetch_assoc()) {
        $properties[] = $row;
    }
    renderPropertyCards($properties, 'AddProperty');
} else {
    echo "<p class='text-center'>No properties found.</p>";
}

// Close the database connection
$conn->close();
?>
