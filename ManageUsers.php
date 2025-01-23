<?php
include("config.php");
include("Authenticate.php");
include("Navigation.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="wrapper">
        <main class="content">
            <div class="container mt-5">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3">Manage Users</h1>
                    <!--<button class="btn btn-primary" onclick="toggleAdminForm()">Add New Admin</button>-->
                </div>

                <!-- Admin Form 
                <div id="addAdminForm" class="card p-3 mb-4" style="display: none;">
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
                                <div class="input-group">
                                    <input type="password" class="form-control" id="adminPassword" name="adminPassword" placeholder="Password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-success me-2">Save Admin</button>
                            <button type="button" class="btn btn-secondary" onclick="toggleAdminForm()">Cancel</button>
                        </div>
                    </form>
                </div>-->

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

                            if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
                                $searchQuery = trim($_GET['search']);

                                //search users by name or email
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
                                    while ($row = $result->fetch_assoc()) {
                                        $users[] = $row;
                                    }
                                    $stmt->close();
                                } else {
                                    echo "<tr><td colspan='6' class='text-center'>Error: " . $conn->error . "</td></tr>";
                                }
                            } else {
                                $sql = "SELECT UserID, name, email, role, user_type, is_active FROM users WHERE role != 'super_admin'";
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
                                    echo "<td>" . ($user['is_active'] ? 'Active' : 'Inactive') . "</td>";
                                    echo "<td><button class='btn btn-sm btn-danger' onclick='deleteUser(" . htmlspecialchars($user['UserID']) . ")'>Delete</button></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center'>No users found.</td></tr>";
                            }
                            $conn->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
        <?php include 'Footer.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
    <script>
    document.getElementById("togglePassword").addEventListener("click", function () {
        const passwordField = document.getElementById("adminPassword");
        const passwordFieldType = passwordField.getAttribute("type");
        if (passwordFieldType === "password") {
            passwordField.setAttribute("type", "text");
            this.innerHTML = '<i class="fas fa-eye-slash"></i>';
        } else {
            passwordField.setAttribute("type", "password");
            this.innerHTML = '<i class="fas fa-eye"></i>';
        }
    });

    function deleteUser(userID) {
        if (!userID) {
            alert("Error: User ID is undefined.");
            return;
        }
        if (confirm("Are you sure you want to delete this user?")) {
            fetch("Delete_user.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ UserID: userID }),
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        alert(data.message);
                        location.reload(); // Reload the page after deletion
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch((error) => {
                    console.error("Error:", error);
                    alert("An error occurred while deleting the user.");
                });
        }
    }
    </script>
</body>
</html>
