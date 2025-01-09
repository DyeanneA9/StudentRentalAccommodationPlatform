<?php
include("config.php");
include("Navigation.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Account</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Register Form Section -->
    <div class="wrapper">
        <main class="content">
            <div class="register-container">
            <!-- Role Selection -->
            <ul class="nav nav-tabs justify-content-center mb-4" id="roleTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="student-tab" data-bs-toggle="tab" data-bs-target="#student" type="button" role="tab" aria-controls="student" aria-selected="true">Student</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="homeowner-tab" data-bs-toggle="tab" data-bs-target="#homeowner" type="button" role="tab" aria-controls="homeowner" aria-selected="false">Homeowner</button>
                </li>
            </ul>

            <!-- Registration Form -->
            <div class="tab-content" id="roleTabsContent">
                <!-- Student Form -->
                <div class="tab-pane fade show active" id="student" role="tabpanel" aria-labelledby="student-tab">
                    <form action="register_student.php" method="POST" enctype="multipart/form-data">
                        <!-- Full Name and Gender -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="studentName" class="form-label">Full Name*</label>
                                <input type="text" class="form-control" id="studentName" name="name" placeholder="Enter your full name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gender*</label>
                                <div class="d-flex gap-5 align-items-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" id="genderMale" name="studentGender" value="male" required>
                                        <label class="form-check-label" for="genderMale">Male</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" id="genderFemale" name="studentGender" value="female" required>
                                        <label class="form-check-label" for="genderFemale">Female</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- University and Personal Email -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="universitySelect" class="form-label">University*</label>
                                <select class="form-select" id="universitySelect" name="universitySelect" required>
                                    <option value="" disabled selected>Select your university</option>
                                    <option value="UMS">Universiti Malaysia Sabah (UMS)</option>
                                    <option value="NBUC">North Borneo University College (NBUC)</option>
                                    <option value="UITM">Universiti Teknologi MARA (UiTM)</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="studentEmail" class="form-label">Personal Email*</label>
                                <input type="email" class="form-control" id="studentEmail" name="studentEmail" placeholder="Enter your email" required>
                            </div>
                        </div>

                        <!-- Phone Number and Password -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="studentPhone" class="form-label">Phone Number*</label>
                                <div class="input-group">
                                    <span class="input-group-text">+60</span>
                                    <input type="text" class="form-control" id="studentPhone" name="studentPhone" placeholder="Enter your phone number" required pattern="[0-9]{9,10}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="studentPassword" class="form-label">Password*</label>
                                <input type="password" class="form-control" id="studentPassword" name="studentPassword" placeholder="Enter your password" required>
                            </div>
                        </div>

                        <!-- Security Questions -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="security_question_1" class="form-label">Security Question*</label>
                                <select class="form-select" id="security_question_1" name="security_question_1" required>
                                    <option value="" disabled selected>Select a security question</option>
                                    <option value="What is the name of your first pet?">What is the name of your first pet?</option>
                                    <option value="What is your birth date?">What is your birth date?</option>
                                    <option value="Who was your favorite teacher?">Who was your favorite teacher?</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="security_answer_1" class="form-label">Answer*</label>
                                <input type="text" class="form-control" id="security_answer_1" name="security_answer_1" placeholder="Enter your security answer" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="security_question_2" class="form-label">Alternative Security Question*</label>
                                <select class="form-select" id="security_question_2" name="security_question_2" required>
                                    <option value="" disabled selected>Select an alternative security question</option>
                                    <option value="What is the name of your childhood best friend?">What is the name of your childhood best friend?</option>
                                    <option value="In what city were you born?">In what city were you born?</option>
                                    <option value="What is your favorite book?">What is your favorite book?</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="security_answer_2" class="form-label">Answer*</label>
                                <input type="text" class="form-control" id="security_answer_2" name="security_answer_2" placeholder="Enter your alternative security answer" required>
                            </div>
                        </div>

                        <!-- Upload Student Card -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="studentIDUpload" class="form-label">Upload Your Student Card*</label>
                                <input type="file" class="form-control" id="studentIDUpload" name="studentIDUpload" accept="image/*" required>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100">Register as Student</button>
                    </form>
                </div>


                <!-- Homeowner Form -->
                <div class="tab-pane fade" id="homeowner" role="tabpanel" aria-labelledby="homeowner-tab">
                    <form action="register_homeowner.php" method="POST">
                        <!-- Full Name and Gender -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="homeownerName" class="form-label">Full Name*</label>
                                <input type="text" class="form-control" id="homeownerName" name="homeownerName" placeholder="Enter your full name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gender*</label>
                                <div class="d-flex gap-5 align-items-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" id="genderMale" name="homeownerGender" value="male" required>
                                        <label class="form-check-label" for="genderMale">Male</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" id="genderFemale" name="homeownerGender" value="female" required>
                                        <label class="form-check-label" for="genderFemale">Female</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Email and Phone Number -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="homeownerEmail" class="form-label">Email*</label>
                                <input type="email" class="form-control" id="homeownerEmail" name="homeownerEmail" placeholder="Enter your email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="homeownerPhone" class="form-label">Phone Number*</label>
                                <div class="input-group">
                                    <span class="input-group-text">+60</span>
                                    <input type="text" class="form-control" id="homeownerPhone" name="homeownerPhone" placeholder="Enter your phone number" required pattern="[0-9]{9,10}">
                                </div>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="homeownerPassword" class="form-label">Password*</label>
                                <input type="password" class="form-control" id="homeownerPassword" name="homeownerPassword" placeholder="Enter your password" required>
                            </div>
                        </div>

                        <!-- Security Questions -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="security_question_1" class="form-label">Security Question*</label>
                                <select class="form-select" id="security_question_1" name="security_question_1" required>
                                    <option value="" disabled selected>Select a security question</option>
                                    <option value="What is the name of your first pet?">What is the name of your first pet?</option>
                                    <option value="What is your birth date?">What is your birth date?</option>
                                    <option value="Who was your favorite teacher?">Who was your favorite teacher?</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="security_answer_1" class="form-label">Answer*</label>
                                <input type="text" class="form-control" id="security_answer_1" name="security_answer_1" placeholder="Enter your security answer" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="security_question_2" class="form-label">Alternative Security Question*</label>
                                <select class="form-select" id="security_question_2" name="security_question_2" required>
                                    <option value="" disabled selected>Select an alternative security question</option>
                                    <option value="What is the name of your childhood best friend?">What is the name of your childhood best friend?</option>
                                    <option value="In what city were you born?">In what city were you born?</option>
                                    <option value="What is your favorite book?">What is your favorite book?</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="security_answer_2" class="form-label">Answer*</label>
                                <input type="text" class="form-control" id="security_answer_2" name="security_answer_2" placeholder="Enter your alternative security answer" required>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100">Register as Homeowner</button>
                    </form>
                </div>

                 <!-- Success Modal -->
                <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="successModalLabel">Registration Successful</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                Your account has been created successfully. Please wait for admin approval before logging in.
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="redirectToLogin()">OK</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error Modal -->
                <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="errorModalLabel">Registration Failed</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                There was an error with your registration. Please try again.
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer Section -->
        <footer class="bg-dark text-center text-white py-3 mt-auto">
            <p class="mb-0">StudentRentalAccommodation.com</p>
        </footer>
    </div>

    <!-- Bootstrap JavaScript (requires Popper.js) -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    
    <script src="script.js"></script>

</body>
</html>
