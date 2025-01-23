<?php
include("config.php");
include("Authenticate.php");

$propertyID = isset($_GET['id']) ? intval($_GET['id']) : null;
$userID = isset($_SESSION['UserID']) ? $_SESSION['UserID'] : null; 

if (!$propertyID) {
    die("Invalid property ID.");
}

// Fetch the property details
$query = "SELECT * FROM property WHERE PropertyID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $propertyID);
$stmt->execute();
$result = $stmt->get_result();
$property = $result->fetch_assoc();

if (!$property) {
    die("Property not found.");
}

$propertyType = $property['PropertyType'];
$leaseLength = $property['LeaseLength'];
$monthlyRent = $property['PropertyPrice'];
$listedDate = $property['ListedDate'];
$totalTenantsRequired = $property['TotalTenants'];

// Fetch user details
$queryUser = "SELECT * FROM users WHERE UserID = ?";
$stmtUser = $conn->prepare($queryUser);
$stmtUser->bind_param("i", $userID);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$user = $resultUser->fetch_assoc();

if (!$user) {
    die("User not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    
    <form action="Booking_action.php" method="post">
        <input type="hidden" name="propertyID" value="<?php echo $propertyID; ?>">
        <input type="hidden" name="propertyType" value="<?php echo htmlspecialchars($propertyType); ?>">

        <!-- User Details -->
        <div class="mb-3">
            <label for="userName" class="form-label">Your Name</label>
            <input type="text" id="userName" name="userName" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
        </div>

        <!-- Property Details -->
        <div class="mb-3">
            <label for="propertyType" class="form-label">Property Type</label>
            <input type="text" id="propertyType" name="propertyTypeDisplay" class="form-control" value="<?php echo htmlspecialchars($propertyType); ?>" readonly>
        </div>

        <!-- Price --> 
        <div class="mb-3">
            <label for="MonthlyRent" class="form-label">Monthly Rent</label>
            <input type="text" id="MonthlyRent" name="MonthlyRent" class="form-control" value="RM<?php echo number_format($monthlyRent, 2); ?>" readonly>
        </div>

        <!-- Booking Type -->
        <div class="mb-3">
            <label class="form-label">Are you booking for yourself or group booking?</label>
            <select id="bookingType" name="bookingType" class="form-select" required>
                <option value="myself">Myself</option>
                <option value="group">Group Booking</option>
            </select>
        </div>

        <!-- Number of People (Visible for group booking) -->
        <div id="groupDetails" style="display:none;">
            <div class="mb-3">
                <label for="numPeople" class="form-label">Number of People Renting including yourself:</label>
                <input type="number" id="numPeople" name="numPeople" class="form-control" min="1" max="9">
            </div>
            <div id="groupMembers">
                <!-- Group members' details will be dynamically added here -->
            </div>
        </div>

        <!-- Monthly Rent Per Person -->
        <div class="mb-3">
            <label for="monthlyRentPerPerson" class="form-label">Monthly Rent Per Person:</label>
            <input type="text" id="monthlyRentPerPerson" name="monthlyRentPerPerson" class="form-control" value="RM<?php echo number_format($monthlyRent / $property['TotalTenants'], 2); ?>" readonly>
        </div>

        <!-- Move-in and Move-out Dates -->
        <div class="mb-3">
            <label for="moveInDate" class="form-label">Preferred Move-In Date:</label>
            <input type="date" id="moveInDate" name="moveInDate" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="moveOutDate" class="form-label">Move-Out Date:</label>
            <input type="text" id="moveOutDate" name="moveOutDate" class="form-control" value="" readonly>
        </div>

        <button type="submit" class="btn btn-primary">Submit Booking</button>
    </form>

    <!-- Modal -->
    <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookingModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalMessage"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const monthlyRent = <?php echo $monthlyRent; ?>; // Fetch the monthly rent from PHP
    const totalTenantsRequired = <?php echo $property['TotalTenants']; ?>; // Fetch the required total tenants
    const leaseLength = <?php echo $leaseLength; ?>; // Fetch the lease length from PHP (in months)
    
    // Disable "Group Booking" if only 1 tenant is required
    if (totalTenantsRequired === 1) {
        document.querySelector('select[name="bookingType"] option[value="group"]').disabled = true;
    }

    // Update Monthly Rent Per Person based on booking type
    document.getElementById("bookingType").addEventListener("change", function () {
        const bookingType = this.value;
        const numPeopleField = document.getElementById("numPeople");
        const monthlyRentPerPersonField = document.getElementById("monthlyRentPerPerson");

        if (bookingType === "myself") {
            // Calculate rent per person for "myself"
            const rentPerPerson = monthlyRent / totalTenantsRequired;
            monthlyRentPerPersonField.value = `RM${rentPerPerson.toFixed(2)}`;
            document.getElementById("groupDetails").style.display = "none";
        } else {
            // Show group booking details and reset the value
            document.getElementById("groupDetails").style.display = "block";
            numPeopleField.addEventListener("input", function () {
                const numPeople = parseInt(this.value) || 1;
                const rentPerPerson = monthlyRent / numPeople;
                monthlyRentPerPersonField.value = `RM${rentPerPerson.toFixed(2)}`;
            });
        }
    });

    // Set the minimum date for moveInDate based on property upload date
    const moveInDateInput = document.getElementById("moveInDate");
    const listedDate = "<?php echo $listedDate; ?>"; // Fetch the property upload date
    moveInDateInput.setAttribute("min", listedDate);

    // Dynamically calculate the move-out date based on lease length and move-in date
    moveInDateInput.addEventListener("change", function () {
        const moveInDate = new Date(this.value);

        if (leaseLength === 1) {
            // If lease length is 1 day, add 1 day to the move-in date
            moveInDate.setDate(moveInDate.getDate() + 1);
        } else {
            // Add the lease length (in months) to the move-in date for other lease lengths
            moveInDate.setMonth(moveInDate.getMonth() + leaseLength);
        }

        // Format the date as YYYY-MM-DD
        const moveOutDate = moveInDate.toISOString().split("T")[0];
        document.getElementById("moveOutDate").value = moveOutDate;
    });

    // Dynamically add fields for group members if group booking is selected
    document.getElementById("numPeople").addEventListener("input", function () {
        const numPeople = parseInt(this.value) || 0;
        const groupMembersDiv = document.getElementById("groupMembers");
        groupMembersDiv.innerHTML = ''; // Clear existing fields

        for (let i = 1; i < numPeople; i++) {
            groupMembersDiv.innerHTML += `
                <h5>Member ${i}</h5>
                <div class="mb-3">
                    <label for="name${i}" class="form-label">Name:</label>
                    <input type="text" name="memberName[]" id="name${i}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="icUpload${i}" class="form-label">Upload IC (Front and Back):</label>
                    <input type="file" name="memberIC[]" id="icUpload${i}" class="form-control" accept=".png, .jpg, .jpeg, .pdf" required>
                </div>
                <div class="mb-3">
                    <label for="phone${i}" class="form-label">Phone Number:</label>
                    <input type="tel" name="memberPhone[]" id="phone${i}" class="form-control" required>
                </div>
                <hr>
            `;
        }
    });
</script>

<?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
    <script>
        window.onload = function() {
            var bookingModal = new bootstrap.Modal(document.getElementById('bookingModal'));
            document.getElementById('bookingModalLabel').innerText = "<?php echo htmlspecialchars($_GET['status']); ?>";
            document.getElementById('modalMessage').innerText = "<?php echo htmlspecialchars($_GET['message']); ?>";
            bookingModal.show();
        }
    </script>
<?php endif; ?>


</body>
</html>
