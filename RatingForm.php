<?php
include("config.php");
session_start();

// Check if the user is logged in
if (!isset($_SESSION['UserID'])) {
    die("Error: You must be logged in to rate a property.");
}

// Get PropertyID from URL
if (!isset($_GET['id'])) {
    die("Error: No property selected for rating.");
}
$propertyID = intval($_GET['id']);

// Fetch property details
$query = "SELECT PropertyType, PropertyAddress FROM property WHERE PropertyID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $propertyID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Error: Property not found.");
}

$property = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Property</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Rate Property</h2>
        <p class="text-muted text-center">
            <?php echo htmlspecialchars($property['PropertyType']) . " - " . htmlspecialchars($property['PropertyAddress']); ?>
        </p>
        <form action="SubmitReview.php" method="POST">
            <!-- PropertyID (Hidden) -->
            <input type="hidden" name="propertyID" value="<?php echo $propertyID; ?>">

            <!-- Rating -->
            <div class="mb-3">
                <label class="form-label">Rate the Property*</label>
                <div class="d-flex justify-content-between" style="width: 150px;">
                    <label>
                        <input type="radio" name="rating" value="1" required> 1
                    </label>
                    <label>
                        <input type="radio" name="rating" value="2"> 2
                    </label>
                    <label>
                        <input type="radio" name="rating" value="3"> 3
                    </label>
                    <label>
                        <input type="radio" name="rating" value="4"> 4
                    </label>
                    <label>
                        <input type="radio" name="rating" value="5"> 5
                    </label>
                </div>
            </div>

            <!-- Feedback -->
            <div class="mb-3">
                <label for="feedback" class="form-label">Feedback</label>
                <textarea id="feedback" name="feedback" class="form-control" rows="4" placeholder="Share your experience (optional)"></textarea>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary">Submit Rating</button>
        </form>
    </div>
</body>
</html>
