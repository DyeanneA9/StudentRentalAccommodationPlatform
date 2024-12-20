<?php
include("config.php");

function getUserProfile($UserID) {
    global $conn; 

    if (!$conn) {
        die("Database connection failed: " . mysqli_connect_error());
    }

    $sql = "SELECT name, profile_picture, user_type FROM users WHERE UserID = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing the statement: " . $conn->error);
    }

    $stmt->bind_param("i", $UserID);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData = $result->num_rows === 1 ? $result->fetch_assoc() : null;
    $stmt->close();

    //If owner is found, fetch the property listed
    if($userData) {
        $sqlPropertyCount = "SELECT COUNT(*) AS property_count FROM property WHERE UserID = ?";
        $stmtPropertyCount = $conn->prepare($sqlPropertyCount);

        if($stmtPropertyCount === false) {
            die("Error preparing the property count statement: " . $conn->error);
        }

        $stmtPropertyCount->bind_param("i", $UserID);
        $stmtPropertyCount->execute();
        $resultPropertyCount = $stmtPropertyCount->get_result();
        $propertyData = $resultPropertyCount->fetch_assoc();
        $stmtPropertyCount->close();

        // Add property count to the user data
        $userData['property_count'] = $propertyData['property_count'];
    }

    return $userData;
}

// Example usage:
if (isset($_SESSION['UserID'])) {
    $userProfile = getUserProfile($_SESSION['UserID']);

    if ($userProfile) {
        // Use $userProfile['name'], $userProfile['photo'], $userProfile['user_type'] as needed
    } else {
        // Redirect to login if user data is not found
        header("Location: Login.php");
        exit();
    }
} else {
    // Redirect to login if session user_id is not set
    header("Location: Login.php");
    exit();
}
?>
