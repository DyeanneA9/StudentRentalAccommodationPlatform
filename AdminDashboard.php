<?php
include("Auth.php");
include("NavBar.php");
include("config.php");

$UserID = $_SESSION['UserID'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="wrapper">
        <main class="content">
            <div class="container mt-5">
                <!-- Page Title -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3">Welcome, Admin </h1>
                </div>

                <!-- Pending Approvals Section -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Pending Approvals</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>User Type</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php

                                    // Query to fetch pending approvals
                                    $sql = "SELECT UserID, name, email, user_type FROM users WHERE is_approved = 0 AND (user_type = 'student' OR user_type = 'homeowner')";
                                    $result = $conn->query($sql);

                                    // Debug: Check if the query executed successfully
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
                </div>
            </div>
        </main>

        <!-- Footer Section-->
        <?php include 'Footer.php'; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="script.js"></script>
    
</body>
</html>