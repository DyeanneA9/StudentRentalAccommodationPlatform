<?php
include("config.php");
include("Authenticate.php");
include("Navigation.php");

$waitingListID = isset($_GET['id']) ? intval($_GET['id']) : null;
$userID = isset($_SESSION['UserID']) ? $_SESSION['UserID'] : null;

if (!$waitingListID || !$userID) {
    die("Invalid request or user not logged in.");
}

// Fetch the waiting list details
$query = "SELECT wl.*, p.PropertyType, p.PropertyAddress, p.PropertyPrice, p.TotalTenants, p.Photo, p.LeaseLength 
          FROM waiting_list wl 
          JOIN property p ON wl.PropertyID = p.PropertyID 
          WHERE wl.WaitingListID = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Prepare statement failed: " . $conn->error);
}

$stmt->bind_param("i", $waitingListID);
if (!$stmt->execute()) {
    die("Execution failed: " . $stmt->error);
}

$result = $stmt->get_result();
$waitingList = $result->fetch_assoc();

if (!$waitingList) {
    die("Waiting list entry not found.");
}

$remainingSpots = $waitingList['TotalTenants'] - $waitingList['NumOfPeople'];
$leaseLength = isset($waitingList['LeaseLength']) ? (int)$waitingList['LeaseLength'] : 0;

// Fetch user details
$queryUser = "SELECT * FROM users WHERE UserID = ?";
$stmtUser = $conn->prepare($queryUser);

if (!$stmtUser) {
    die("Prepare statement failed: " . $conn->error);
}

$stmtUser->bind_param("i", $userID);
if (!$stmtUser->execute()) {
    die("Execution failed: " . $stmtUser->error);
}

$resultUser = $stmtUser->get_result();
$user = $resultUser->fetch_assoc();

if (!$user) {
    die("User not found.");
}

// Fetch users who already submitted bookings for the same property
$queryBookings = "SELECT u.gender, u.university, wl.MoveInDate, wl.MoveOutDate FROM waiting_list wl 
                   JOIN users u ON wl.UserID = u.UserID 
                   WHERE wl.PropertyID = ?";
$stmtBookings = $conn->prepare($queryBookings);

if (!$stmtBookings) {
    die("Prepare statement failed: " . $stmtBookings->error);
}

$stmtBookings->bind_param("i", $waitingList['PropertyID']);
if (!$stmtBookings->execute()) {
    die("Execution failed: " . $stmtBookings->error);
}

$bookingsResult = $stmtBookings->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Waiting List</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <div class="content">
        <div class="joinwaitinglist-content">
            <!-- Users Already in Waiting List -->
            <div class="mb-4">
                <h4 class="joinwaitinglist-heading mb-3 text-center">Users Already in Waiting List</h4>
                <?php if ($bookingsResult->num_rows > 0): ?>
                    <table class="table joinwaitinglist-table table-hover table-bordered align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Gender</th>
                                <th>University</th>
                                <th>Preferred Move-In Date</th>
                                <th>Move-Out Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($booking = $bookingsResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($booking['gender']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['university']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['MoveInDate']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['MoveOutDate']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-center text-muted">No users have joined this waiting list yet.</p>
                <?php endif; ?>
            </div>

            <!-- Join Waiting List Form -->
            <form action="JoinWaitingList_action.php" method="post">
                <input type="hidden" name="waitingListID" value="<?php echo $waitingListID; ?>">

                <p class="text-danger text-center">PLEASE ENSURE THAT YOU ARE AWARE OF THE MOVE IN DATE AND AGREE WITH IT BEFORE FILLING IN THE FORM.</p>

                <!-- User Details -->
                <div class="mb-3">
                    <label for="userName" class="joinwaitinglist-form-label"><i class="bi bi-person-fill"></i> Your Name</label>
                    <input type="text" id="userName" name="userName" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                </div>

                <div class="mb-3">
                    <label for="userUniversity" class="joinwaitinglist-form-label"><i class="bi bi-building"></i> University</label>
                    <input type="text" id="userUniversity" name="userUniversity" class="form-control" value="<?php echo htmlspecialchars($user['university']); ?>" readonly>
                </div>

                <!-- Move-In Date -->
                <div class="mb-3">
                    <label for="moveInDate" class="joinwaitinglist-form-label"><i class="bi bi-calendar-event"></i> Preferred Move-In Date</label>
                    <input type="date" id="moveInDate" name="moveInDate" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <!-- Move-Out Date -->
                <div class="mb-3">
                    <label for="moveOutDate" class="joinwaitinglist-form-label"><i class="bi bi-calendar-check"></i> Move-Out Date</label>
                    <input type="text" id="moveOutDate" name="moveOutDate" class="form-control" readonly>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn joinwaitinglist-btn btn-success px-4"><i class="bi bi-check-circle"></i> Join</button>
                    <a href="Dashboard.php" class="btn joinwaitinglist-btn btn-secondary px-4">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Structure -->
    <div class="modal fade" id="noSpotsModal" tabindex="-1" aria-labelledby="noSpotsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="noSpotsModalLabel">Success</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    You have successfully joined the waiting list, and now no remaining spots are left for this property.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show modal if no spots are available
        <?php if ($remainingSpots <= 0): ?>
            var noSpotsModal = new bootstrap.Modal(document.getElementById('noSpotsModal'));
            noSpotsModal.show();
        <?php endif; ?>

        // Set the move-out date dynamically
        const leaseLength = <?php echo json_encode($leaseLength); ?>;
        const moveInDateInput = document.getElementById("moveInDate");
        const moveOutDateInput = document.getElementById("moveOutDate");

        moveInDateInput.addEventListener("change", function () {
            const moveInDate = new Date(this.value);
            if (leaseLength > 0 && !isNaN(moveInDate.getTime())) {
                moveInDate.setMonth(moveInDate.getMonth() + leaseLength);
                const moveOutDate = moveInDate.toISOString().split("T")[0];
                moveOutDateInput.value = moveOutDate;
            } else {
                moveOutDateInput.value = '';
            }
        });
    </script>

</body>
</html>
