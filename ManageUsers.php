<?php
include("Authenticate.php");
include("config.php");
include("Navigation.php");
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
                <!-- Page Title -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3">Manage Users</h1>
                    <button class="btn btn-primary" onclick="toggleAdminForm()">Add New Admin</button>
                </div>

                <!-- Admin Form (Hidden)-->
                <div id="addAdminForm" class="card p-3 mb-4" style="display: none; background-color: white; border: 1px solid #ddd; border-radius: 8px;">
                    <h5 class="mb-4 fw-bold">Add New Admin</h5>
                    <form action="AddAdmin.php" method="POST">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="adminName" name="adminName" placeholder="Full Name" required>
                            </div>
                            <div class="col-md-6">
                                <input type="email" class="form-control" id="adminEmail" name="adminEmail" placeholder="Email" required>
                            </div>
                        </div>
                        <div class="row g-2 mt-2">
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="adminPhone" name="adminPhone" placeholder="Phone Number" required>
                            </div>
                            <div class="col-md-6">
                                <input type="password" class="form-control" id="adminPassword" name="adminPassword" placeholder="Password" required>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-success me-2">Save Admin</button>
                            <button type="button" class="btn btn-secondary" onclick="toggleAdminForm()">Cancel</button>
                        </div>
                    </form>
                </div>

                <!-- Search Bar -->
                <div class="d-flex justify-content-end mb-4">
                    <form action="ManageUsers.php" method="GET" class="d-flex">
                        <input type="text" class="form-control me-3" name="search" style="width: 350px;" placeholder="Search users by name or email" id="searchUser" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button class="btn search-btn" type="submit">Search</button>
                    </form>
                </div>

                <!-- User Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>UserID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>User Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $searchQuery = '';
                            $users = [];

                            // Check if a search query was submitted
                            if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
                                $searchQuery = trim($_GET['search']);

                                // Query to search users by name or email
                                $sql = "SELECT UserID, name, email, role, user_type, is_active 
                                        FROM users 
                                        WHERE (name LIKE ? OR email LIKE ?) AND role != 'super_admin'";
                                $stmt = $conn->prepare($sql);

                                if ($stmt) {
                                    // Use wildcards to match partial searches
                                    $likeSearch = "%$searchQuery%";
                                    $stmt->bind_param("ss", $likeSearch, $likeSearch);
                                    $stmt->execute();
                                    $result = $stmt->get_result();

                                    // Fetch users into an array
                                    while ($row = $result->fetch_assoc()) {
                                        $users[] = $row;
                                    }
                                    $stmt->close();
                                } else {
                                    echo "<tr><td colspan='6' class='text-center'>Error: " . $conn->error . "</td></tr>";
                                }
                            } else {
                                $sql = "SELECT UserID, name, email, role, user_type, is_active 
                                        FROM users 
                                        WHERE role != 'super_admin'";
                                $result = $conn->query($sql);

                                if ($result) {
                                    while ($row = $result->fetch_assoc()) {
                                        $users[] = $row;
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center'>Error: " . $conn->error . "</td></tr>";
                                }
                            }

                            // Display users in the table
                            if (!empty($users)) {
                                foreach ($users as $user) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($user['UserID']) . "</td>";
                                    echo "<td><a href='UserDetails.php?UserID=" . urlencode($user['UserID']) . "'>" . htmlspecialchars($user['name']) . "</a></td>";
                                    echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                                    echo "<td>" . ucfirst($user['role']) . "</td>";
                                    echo "<td>" . htmlspecialchars($user['user_type']) . "</td>"; 
                                    echo "<td>
                                            <span class='badge " . ($user['is_active'] ? 'bg-success' : 'bg-danger') . "'>" .
                                            ($user['is_active'] ? 'Active' : 'Inactive') . "</span>
                                        </td>";
                                    echo "<td>
                                            <button class='btn btn-sm btn-danger' onclick='deleteUser(" . $user['UserID'] . ")'>Delete</button>
                                        </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center'>No users found.</td></tr>";
                            }

                            $conn->close();
                            ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </main>

        <!-- Footer Section -->
        <?php include 'Footer.php'; ?>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>

</body>
</html>
