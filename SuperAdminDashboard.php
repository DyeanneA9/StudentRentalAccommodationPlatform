<?php
include("config.php");
include("Authenticate.php");
include("Navigation.php");

// Function to get the count of rows based on a condition
function getCount($table, $condition) {
    global $conn;
    $sql = "SELECT COUNT(*) AS total FROM $table WHERE $condition";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['total'];
    } else {
        return 0;
    }
}

// Fetch total counts
$totalUsers = getCount('users', "role != 'super_admin'");
$totalAdmins = getCount('users', "role = 'admin'");
$pendingApprovals = getCount('users', "is_approved = 0");
$pendingPropertyApprovals = getCount('property', "is_approved = 0");

// Fetch recent activities
function getRecentActivities() {
    global $conn;
    $sql = "SELECT action, created_at FROM activities ORDER BY created_at DESC LIMIT 10";
    $result = $conn->query($sql);
    $activities = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
    }
    return $activities;
}

$recentActivities = getRecentActivities();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                    <!-- Total Users -->
                    <div class="col-md-4 mb-3">
                        <div class="card text-white bg-primary shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Total Users</h5>
                                <p class="card-text fs-3"><?= number_format($totalUsers); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Active Admins -->
                    <div class="col-md-4 mb-3">
                        <div class="card text-white bg-success shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Active Admins</h5>
                                <p class="card-text fs-3"><?= number_format($totalAdmins); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Pending User Approvals -->
                    <div class="col-md-4 mb-3">
                        <div onclick="togglePendingApprovals()" class="card text-white bg-danger shadow-sm" style="cursor: pointer;">
                            <div class="card-body">
                                <h5 class="card-title">Pending User Approvals</h5>
                                <p class="card-text fs-3"><?= number_format($pendingApprovals); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hidden Pending User Approvals Table -->
                <div id="pendingApprovalsSection" class="card shadow-sm mt-4" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
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
                                $sql = "SELECT UserID, name, email, role, user_type 
                                        FROM users 
                                        WHERE is_approved = 0 AND (user_type = 'student' OR user_type = 'homeowner')";
                                $result = $conn->query($sql);
                                if ($result && $result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['UserID']) . "</td>";
                                        echo "<td><a href='UserDetails.php?UserID=" . urlencode($row['UserID']) . "'>" . htmlspecialchars($row['name']) . "</a></td>";
                                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['role']) . "</td>";
                                        echo "<td>" . ucfirst(htmlspecialchars($row['user_type'])) . "</td>";
                                        echo "<td>
                                                <button class='btn btn-sm btn-success me-2' onclick='approveUser(" . htmlspecialchars($row['UserID']) . ")'>Approve</button>
                                                <button class='btn btn-sm btn-danger' onclick='rejectUser(" . htmlspecialchars($row['UserID']) . ")'>Reject</button>
                                            </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center'>No pending approvals</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pending Property Approvals -->
                <div class="col-md-4 mb-3 mt-3">
                    <a href="PropertyApproval.php" class="text-decoration-none">
                        <div class="card text-white bg-warning shadow-sm" style="cursor: pointer;">
                            <div class="card-body">
                                <h5 class="card-title text-center">Pending Property Approvals</h5>
                                <p class="card-text fs-3 text-center"><?php echo number_format($pendingPropertyApprovals); ?></p>
                            </div>
                        </div>
                    </a>
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
