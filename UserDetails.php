<?php
include("Auth.php");
include("config.php");
include("NavBar.php");

if (isset($_GET['UserID'])) {
    $UserID = intval($_GET['UserID']);

    // Fetch user details from the database
    $stmt = $conn->prepare("SELECT * FROM users WHERE UserID = ?");
    $stmt->bind_param("i", $UserID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        die("User not found.");
    }
    $stmt->close();
} else {
    die("Invalid request.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="wrapper">
        <main class="content">
            <div class="container mt-5">
                <!-- Back Button -->
                <a href="ManageUsers.php?UserID=<?php echo htmlspecialchars($user['UserID']); ?>" class="btn btn-secondary mb-3">BACK</a>
                <div class="row">
                    <!-- Column 1: Profile and Student ID -->
                    <div class="col-md-4 text-center">
                        <div class="card">
                            <div class="card-body">
                                <!-- Profile Image -->
                                <h6 class="text-muted">Profile Picture</h6>
                                <?php if (!empty($user['profile_picture'])): ?>
                                    <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="img-fluid rounded-circle mb-3" style="max-width: 150px; max-height: 150px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="default-profile.png" alt="Default Profile" class="img-fluid rounded-circle mb-3" style="max-width: 150px; max-height: 150px; object-fit: cover;">
                                <?php endif; ?>

                                <h5 class="card-title mt-3"><?php echo htmlspecialchars($user['name']); ?></h5>
                                <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>

                            <?php if ($user['user_type'] === 'student'): ?>
                                <div class="card-body">
                                    <h6 class="text-muted">Student Card</h6>
                                    <?php if (!empty($user['student_id'])): ?>
                                        <img src="<?php echo htmlspecialchars($user['student_id']); ?>" alt="Student Card" class="img-fluid mb-3" style="max-width: 300px; max-height: 300px;">
                                    <?php else: ?>
                                        <img src="default-card.png" alt="Default Student Card" class="img-fluid mb-3" style="max-width: 300px; max-height: 300px;">
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Conditional Display for User Details -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">User Details</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($user['role'] === 'admin' || $user['user_type'] === 'homeowner'): ?>
                                    <!-- Show limited details for Admin or Homeowner -->
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label for="name" class="form-label">Full Name</label>
                                            <input type="text" class="form-control" id="name" value="<?php echo htmlspecialchars($user['name']); ?>" readonly style="background-color: #f9f9f9;">
                                        </div>
                                        <div class="col-md-12">
                                            <label for="email" class="form-label">Personal Email</label>
                                            <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly style="background-color: #f9f9f9;">
                                        </div>
                                        <div class="col-md-12">
                                            <label for="studentPhone" class="form-label">Phone Number</label>
                                            <input type="text" class="form-control" id="studentPhone" value="<?php echo htmlspecialchars($user['phone_number']); ?>" readonly style="background-color: #f9f9f9;">
                                        </div>
                                        <div class="col-md-12">
                                            <label for="studentPassword" class="form-label">Password (Hashed)</label>
                                            <input type="password" class="form-control" id="studentPassword" value="<?php echo htmlspecialchars($user['password']); ?>" readonly style="background-color: #f9f9f9;">
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Show full details for other roles -->
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="name" class="form-label">Full Name</label>
                                            <input type="text" class="form-control" id="name" value="<?php echo htmlspecialchars($user['name']); ?>" readonly style="background-color: #f9f9f9;">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="universitySelect" class="form-label">University</label>
                                            <input type="text" class="form-control" id="universitySelect" value="<?php echo htmlspecialchars($user['university']); ?>" readonly style="background-color: #f9f9f9;">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="email" class="form-label">Personal Email</label>
                                            <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly style="background-color: #f9f9f9;">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="studentPhone" class="form-label">Phone Number</label>
                                            <input type="text" class="form-control" id="studentPhone" value="<?php echo htmlspecialchars($user['phone_number']); ?>" readonly style="background-color: #f9f9f9;">
                                        </div>
                                        <div class="col-md-12">
                                            <label for="studentPassword" class="form-label">Password (Hashed)</label>
                                            <input type="password" class="form-control" id="studentPassword" value="<?php echo htmlspecialchars($user['password']); ?>" readonly style="background-color: #f9f9f9;">
                                        </div>
                                        <div class="col-md-12">
                                            <label for="security_question_1" class="form-label">Security Question</label>
                                            <input type="text" class="form-control" id="security_question_1" value="<?php echo htmlspecialchars($user['security_question']); ?>" readonly style="background-color: #f9f9f9;">
                                            <label for="security_answer_1" class="form-label">Answer</label>
                                            <input type="text" class="form-control" id="security_answer_1" value="<?php echo htmlspecialchars($user['security_answer']); ?>" readonly style="background-color: #f9f9f9;">
                                        </div>
                                        <div class="col-md-12">
                                            <label for="security_question_2" class="form-label">Alternative Security Question</label>
                                            <input type="text" class="form-control" id="security_question_2" value="<?php echo htmlspecialchars($user['alternative_question']); ?>" readonly style="background-color: #f9f9f9;">
                                            <label for="security_answer_2" class="form-label">Answer</label>
                                            <input type="text" class="form-control" id="security_answer_2" value="<?php echo htmlspecialchars($user['alternative_answer']); ?>" readonly style="background-color: #f9f9f9;">
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <!-- EDIT Button -->
                            <a href="EditUser.php?UserID=<?php echo htmlspecialchars($user['UserID']); ?>" class="btn btn-secondary">EDIT</a>
                        </div>
                    </div>
                </div>
            </div>  
        </main>

        <!-- Footer Section -->
        <?php include 'Footer.php'; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

</body>
</html>