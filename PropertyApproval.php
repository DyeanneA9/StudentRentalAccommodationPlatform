<?php
include("Auth.php");
include("config.php");
include("NavBar.php");

// Fetch properties pending approval
$sql = "SELECT p.PropertyID, p.PropertyType, p.PropertyAddress, p.PropertyPrice, p.Description, p.Photo, 
               p.RentalAgreement, p.PropertyGrant, u.name AS owner_name, p.ListedDate 
        FROM property p
        JOIN users u ON p.UserID = u.UserID
        WHERE p.is_approved = 0";
$result = $conn->query($sql);

if (!$result) {
    die("Error fetching properties: " . $conn->error);
}

$pendingProperties = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pendingProperties[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Approval Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="wrapper">
        <main class="content">
            <div class="container mt-5">
                <!-- Header -->
                <div class="mb-4">
                    <h2 class="text-white bg-dark p-3 rounded">Properties Approval</h2>
                    <p class="text-muted">Review and manage property listings submitted by homeowners</p>
                </div>

                <!-- Pending Properties -->
                <?php if (!empty($pendingProperties)): ?>
                    <?php foreach ($pendingProperties as $property): ?>
                        <div class="card mb-3 shadow-sm">
                            <div class="card-body p-3">
                                <!-- Header Row -->
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($property['PropertyType']); ?></h5>
                                        <span class="badge bg-warning text-dark">Pending Approval</span>
                                    </div>
                                    <small class="text-muted">Submitted: <?php echo htmlspecialchars($property['ListedDate']); ?></small>
                                </div>

                                <!-- Content Row -->
                                <div class="row g-2">
                                    <!-- Property Image -->
                                    <div class="col-md-4">
                                        <?php
                                        $photos = json_decode($property['Photo'], true);
                                        $firstPhoto = isset($photos[0]) ? $photos[0] : 'path/to/default-image.jpg';
                                        ?>
                                        <img src="<?php echo htmlspecialchars($firstPhoto); ?>" class="img-fluid rounded" 
                                             style="object-fit: cover; height: 120px; width: 100%;" alt="Property Image">
                                    </div>

                                    <!-- Property Details -->
                                    <div class="col-md-8">
                                        <div class="row">
                                            <div class="col-md-6 small">
                                                <p class="mb-1"><strong>Owner:</strong> <?php echo htmlspecialchars($property['owner_name']); ?></p>
                                                <p class="mb-1"><strong>Address:</strong> <?php echo htmlspecialchars($property['PropertyAddress']); ?></p>
                                                <p class="mb-1">
                                                    <strong>Rental Agreement:</strong> 
                                                    <?php if (!empty($property['RentalAgreement'])): ?>
                                                        <a href="<?php echo htmlspecialchars($property['RentalAgreement']); ?>" 
                                                           target="_blank" class="text-decoration-none">Download</a>
                                                    <?php else: ?>
                                                        <span class="text-muted">Not Provided</span>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                            <div class="col-md-6 small">
                                                <p class="mb-1"><strong>Monthly Rent:</strong> RM<?php echo number_format($property['PropertyPrice'], 2); ?></p>
                                                <p class="mb-1"><strong>Description:</strong> <?php echo htmlspecialchars($property['Description']); ?></p>
                                                <p class="mb-1">
                                                    <strong>Property Grant:</strong> 
                                                    <?php if (!empty($property['PropertyGrant'])): ?>
                                                        <a href="<?php echo htmlspecialchars($property['PropertyGrant']); ?>" 
                                                           target="_blank" class="text-decoration-none">Download</a>
                                                    <?php else: ?>
                                                        <span class="text-muted">Not Provided</span>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="mt-4 d-flex justify-content-end">
                                    <a href="PropertyDetails.php?id=<?php echo $property['PropertyID']; ?>" class="btn btn-primary me-2">View Details</a>
                                    <button class="btn btn-success me-2" onclick="approveProperty(<?php echo $property['PropertyID']; ?>)">Approve</button>
                                    <button class="btn btn-danger" onclick="openRejectModal(<?php echo $property['PropertyID']; ?>)">Reject</button>
                                </div>

                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <form action="RejectProperty.php" method="POST">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="rejectModalLabel">Reject Property</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="PropertyID" id="rejectPropertyID">
                                                <div class="mb-3">
                                                    <label for="rejectionReason" class="form-label">Reason for Rejection</label>
                                                    <textarea name="rejectionReason" id="rejectionReason" class="form-control" rows="4" placeholder="Provide a reason for rejecting this property" required></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Submit Rejection</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info">No pending properties for approval at the moment.</div>
            <?php endif; ?>

            <?php
            // Fetch all properties
            $sql_all = "SELECT p.PropertyID, p.PropertyType, p.PropertyAddress, p.PropertyPrice, p.is_approved, u.name AS owner_name 
                        FROM property p
                        JOIN users u ON p.UserID = u.UserID";
            $result_all = $conn->query($sql_all);

            if (!$result_all) {
                die("Error fetching all properties: " . $conn->error);
            }

            $allProperties = [];
            if ($result_all->num_rows > 0) {
                while ($row = $result_all->fetch_assoc()) {
                    $allProperties[] = $row;
                }
            }
            ?>

            <!-- All Properties Section -->
            <div class="mt-5">
                <h2 class="text-white bg-dark p-3 rounded">All Properties</h2>
                <p class="text-muted">View all property listings, including approved, pending, and rejected properties.</p>

                <?php if (!empty($allProperties)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Property ID</th>
                                    <th>Owner Name</th>
                                    <th>Property Type</th>
                                    <th>Address</th>
                                    <th>Monthly Rent (RM)</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allProperties as $property): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($property['PropertyID']); ?></td>
                                        <td><?php echo htmlspecialchars($property['owner_name']); ?></td>
                                        <td><?php echo htmlspecialchars($property['PropertyType']); ?></td>
                                        <td><?php echo htmlspecialchars($property['PropertyAddress']); ?></td>
                                        <td><?php echo number_format($property['PropertyPrice'], 2); ?></td>
                                        <td>
                                            <?php 
                                                if ($property['is_approved'] == 0) echo '<span class="badge bg-warning text-dark">Pending</span>';
                                                elseif ($property['is_approved'] == 1) echo '<span class="badge bg-success">Approved</span>';
                                                elseif ($property['is_approved'] == -1) echo '<span class="badge bg-danger">Rejected</span>';
                                            ?>
                                        </td>
                                        <td>
                                            <a href="PropertyDetails.php?id=<?php echo $property['PropertyID']; ?>" class="btn btn-sm btn-info">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">No properties found in the system.</div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include 'Footer.php'; ?>
</div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="script.js"></script>
</body>
</html>