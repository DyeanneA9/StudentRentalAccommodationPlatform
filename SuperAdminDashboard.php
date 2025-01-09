<?php
include("config.php");
include("Authenticate.php");
include("Navigation.php");

// Query to count total users
$sql = "SELECT COUNT(*) AS total_users FROM users WHERE role != 'super_admin'";
$result = $conn->query($sql);

$totalUsers = 0; 
if ($result && $row = $result->fetch_assoc()) {
    $totalUsers = $row['total_users'];
}

// Query to count regular admins
$sql = "SELECT COUNT(*) AS total_admins FROM users WHERE role = 'admin'";
$result = $conn->query($sql);

$totalAdmins = 0;
if ($result && $row = $result->fetch_assoc()) {
    $totalAdmins = $row['total_admins'];
}

// Query to count users pending approval
$sql = "SELECT COUNT(*) AS pending_approvals FROM users WHERE is_approved = 0";
$result = $conn->query($sql);

$pendingApprovals = 0; // Default value if query fails
if ($result && $row = $result->fetch_assoc()) {
    $pendingApprovals = $row['pending_approvals'];
}

// Query to count pending property approvals
$sql = "SELECT COUNT(*) AS pending_property_approvals FROM property WHERE is_approved = 0";
$result = $conn->query($sql);

$pendingPropertyApprovals = 0; // Default value if query fails
if ($result && $row = $result->fetch_assoc()) {
    $pendingPropertyApprovals = $row['pending_property_approvals'];
}

// Query to fetch recent activities (limit to the 10 most recent)
$sql = "SELECT action, created_at FROM activities ORDER BY created_at DESC LIMIT 10";
$result = $conn->query($sql);

$recentActivities = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recentActivities[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="wrapper">
        <main class="content">
            <div class="container mt-5">
                <!-- Welcome Message -->
                <div class="text-center mb-4">
                    <h4>Welcome, Super Admin</h4>
                </div>

                <!-- Statistics Section -->
                <div class="row text-center">
                    <div class="col-md-4 mb-3">
                        <div class="card text-white bg-primary shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Total Users</h5>
                                <p class="card-text fs-3"><?php echo number_format($totalUsers); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card text-white bg-success shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Active Admins</h5>
                                <p class="card-text fs-3"><?php echo number_format($totalAdmins); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div onclick="togglePendingApprovals()" class="card text-white bg-danger shadow-sm" style="cursor: pointer;">
                            <div class="card-body">
                                <h5 class="card-title">Pending User Approvals</h5>
                                <p class="card-text fs-3"><?php echo number_format($pendingApprovals); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden Pending Approvals Table -->
                    <div id="pendingApprovalsSection" class="mt-4" style="display: none;">
                        <h2 class="mb-4">Pending Approvals</h2>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>User Type</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php

                                    $sql = "SELECT UserID, name, email, role, user_type FROM users WHERE is_approved = 0 AND (user_type = 'student' OR user_type = 'homeowner')";
                                    $result = $conn->query($sql);

                                    if (!$result) {
                                        die("<tr><td colspan='5' class='text-center'>Query Error: " . $conn->error . "</td></tr>");
                                    }

                                    if (isset($_GET['message'])) {
                                        echo "<div class='alert alert-success'>" . htmlspecialchars($_GET['message']) . "</div>";
                                    }

                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>" . $row['UserID'] . "</td>";
                                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['role']) . "</td>";
                                            echo "<td>" . ucfirst($row['user_type']) . "</td>";
                                            echo "<td>
                                                    <button class='btn btn-sm btn-success me-2' onclick='approveUser(" . $row['UserID'] . ")'>Approve</button>
                                                    <button class='btn btn-sm btn-danger' onclick='rejectUser(" . $row['UserID'] . ")'>Reject</button>
                                                </td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' class='text-center'>No pending approvals</td></tr>";
                                    }
                                    $conn->close();
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pending Property Approvals -->
                    <div class="col-md-4 mb-3">
                        <a href="PropertyApproval.php" class="text-decoration-none">
                            <div class="card text-white bg-warning shadow-sm" style="cursor: pointer;">
                                <div class="card-body">
                                    <h5 class="card-title">Pending Property Approvals</h5>
                                    <p class="card-text fs-3"><?php echo number_format($pendingPropertyApprovals); ?></p>
                                </div>
                            </div>
                        </a>
                    </div>

                </div>

                <!-- Recent Activities Section -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold">Recent Activities</h5>
                    </div>
                    <ul class="list-group list-group-flush">
                        <?php if (!empty($recentActivities)): ?>
                            <?php foreach ($recentActivities as $activity): ?>
                                <li class="list-group-item">
                                    <?php echo htmlspecialchars($activity['action']); ?>
                                    <span class="text-muted d-block small"><?php echo date('F j, Y, g:i a', strtotime($activity['created_at'])); ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-center">No recent activities available.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </main>

        <!-- Footer Section -->
        <?php include 'Footer.php'; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="script.js"></script>
</body>
</html>
