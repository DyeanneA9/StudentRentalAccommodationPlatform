
/* --- REGISTER POP UP MESSAGE --- */
document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);

    if (urlParams.has('success')) {
        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
    } else if (urlParams.has('error')) {
        const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
        errorModal.show();
    }
});

function redirectToLogin() {
    window.location.href = 'Login.php';
}


/* --- CLEAR BUTTON FOR SEARCH AND FILTER --- */
document.querySelector('.btn-light').addEventListener('click', function () {
    const inputs = document.querySelectorAll('.form-control, .form-select');
    inputs.forEach(input => input.value = '');
});


/* --- HANDLE SAVE and DELETE PROPERTIES BUTTON  --- */
document.querySelectorAll('.save-property-btn').forEach(button => {
    button.addEventListener('click', function() {
        const propertyID = this.getAttribute('data-property-id');

        // Send the propertyId to the backend using the Fetch API
        fetch('/SaveButton_action.php', { // Make sure this is your correct backend URL
            method: 'POST',
            body: JSON.stringify({ propertyID: propertyID }),
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Change the icon to filled on successful save
                this.querySelector('i').classList.remove('bi-bookmark');
                this.querySelector('i').classList.add('bi-bookmark-fill');
            } else {
                alert("Failed to save property. Please try again.");
            }
        })
        .catch(error => console.error('Error:', error));
    });
});

/* --- ADD ADMIN --- */
function toggleAdminForm() {
    const form = document.getElementById('addAdminForm');
    if (form.style.display === 'none') {
        form.style.display = 'block'; 
    } else {
        form.style.display = 'none';
    }
}

/* --- DELETE USER --- */
function deleteUser(userID) {
    if (confirm("Are you sure you want to delete this user?")) {
        fetch('Delete_user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ UserID: userID })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.success ? "User deleted successfully." : "Failed to delete user.");
            if (data.success) location.reload(); // Reload the page if successful
        })
        .catch(() => alert("An error occurred. Please try again."));
    }
}

/* --- LOGIN PASSWORD VISIBILITY --- */
function togglePasswordVisibility() {
    const passwordField = document.getElementById("password");
    const passwordIcon = document.getElementById("passwordIcon");
    if (passwordField.type === "password") {
        passwordField.type = "text"; // Show password
        passwordIcon.classList.remove("bi-eye"); // Switch to eye-slash icon
        passwordIcon.classList.add("bi-eye-slash");
    } else {
        passwordField.type = "password"; // Hide password
        passwordIcon.classList.remove("bi-eye-slash"); // Switch to eye icon
        passwordIcon.classList.add("bi-eye");
    }
}


/* --- BOOKING FORM --- */
function toggleForm() {
    var form = document.getElementById("bookingForm");
    if (form.style.display === "none") {
        form.style.display = "block";
    } else {
        form.style.display = "none"; 
    }
}

/* --- ENDDATE BOOKING FORM --- */
function generateEndDate() {
    var StartDateInput = document.getElementById("StartDate");
    var EndDateInput = document.getElementById("EndDate");
    
    // Get the start date value
    var StartDate = new Date(StartDateInput.value);
    
    // Add one year to the start date
    StartDate.setFullYear(StartDate.getFullYear() + 1);
    
    // Format the new date as YYYY-MM-DD
    var year = StartDate.getFullYear();
    var month = (StartDate.getMonth() + 1).toString().padStart(2, '0');
    var day = StartDate.getDate().toString().padStart(2, '0');
    
    // Set the end date value
    EndDateInput.value = `${year}-${month}-${day}`;
}


/* --- PREVIEW IMAGES ON EDITPROPERTY PHP --- */
document.querySelector('input[name="propertyImage[]"]').addEventListener('change', function() {
    if (this.files.length > 0) {
        const fileList = Array.from(this.files).map(file => file.name).join(', ');
        document.getElementById('imagePreview').textContent = fileList;
    } else {
        document.getElementById('imagePreview').textContent = 'No file chosen';
    }
});

/* --- UPDATED SUCCESSFULLY ON EDITPROPERTY PHP --- */ 
// Check if the URL has a success parameter
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has('success')) {
    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
    successModal.show();
}

/* --- DELETE PROPERTY IMAGE ON EDITPROPERTY PHP --- */
function deleteImage(imagePath) {
    if (confirm("Are you sure you want to delete this image?")) {
        // AJAX request to delete the image from the database
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "deleteImage.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function() {
            if (xhr.status === 200) {
                alert("Image deleted successfully.");
                // Remove the image from the DOM
                location.reload(); // Reload the page to update the image list
            } else {
                alert("Failed to delete the image.");
            }
        };
        xhr.send("imagePath=" + encodeURIComponent(imagePath));
    }
}

function approveUser(userID) {
    if (confirm("Are you sure you want to approve this user?")) {
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "ApproveUser.php";

        const userIDInput = document.createElement("input");
        userIDInput.type = "hidden";
        userIDInput.name = "UserID";
        userIDInput.value = userID;

        form.appendChild(userIDInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function rejectUser(userID) {
    if (confirm("Are you sure you want to reject this user?")) {
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "RejectUser.php";

        const userIDInput = document.createElement("input");
        userIDInput.type = "hidden";
        userIDInput.name = "UserID";
        userIDInput.value = userID;

        form.appendChild(userIDInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function approveProperty(propertyID) {
    if (confirm("Are you sure you want to approve this property?")) {
        // Make the POST request to approve the property
        performPropertyAction(propertyID, 'approve');
    }
}

/*function rejectProperty(propertyID) {
    if (confirm("Are you sure you want to reject this property?")) {
        // Make the POST request to reject the property
        performPropertyAction(propertyID, 'reject');
    }
}*/

function openRejectModal(propertyID) {
    document.getElementById('rejectPropertyID').value = propertyID;
    const rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
    rejectModal.show();
}

function performPropertyAction(propertyID, action) {
    // Send the request to the appropriate PHP script
    let url = action === 'approve' ? 'ApproveProperty.php' : 'RejectProperty.php';

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `PropertyID=${propertyID}`
    })
    .then(response => response.text()) // Expecting a redirect or success message
    .then(data => {
        // After successful approval/rejection, reload or redirect
        window.location.href = "PropertyApproval.php?status=success&message=Property%20" + action + "%20successfully.";
    })
    .catch(error => {
        console.error('Error:', error);
    });
}


function togglePendingApprovals() {
    var section = document.getElementById('pendingApprovalsSection');
    if (section.style.display === 'none' || section.style.display === '') {
        section.style.display = 'block';
    } else {
        section.style.display = 'none';
    }
}

function togglePendingPropertyApprovals() {
    const propertySection = document.getElementById("pendingPropertyApprovalsSection");
    const userSection = document.getElementById("pendingApprovalsSection");
    propertySection.style.display = propertySection.style.display === "none" ? "block" : "none";
    userSection.style.display = "none";
}

/* ---- UPDATE PROFILE PICTURE ---- */
const editProfileBtn = document.getElementById('editProfileBtn');
const editProfileForm = document.getElementById('editProfileForm');
const cancelEditBtn = document.getElementById('cancelEditBtn');

// Show the form when Edit Profile button is clicked
editProfileBtn.addEventListener('click', () => {
    editProfileForm.style.display = 'block';
    editProfileBtn.style.display = 'none'; // Hide the button while editing
});

// Hide the form and show the Edit Profile button when Cancel is clicked
cancelEditBtn.addEventListener('click', () => {
    editProfileForm.style.display = 'none';
    editProfileBtn.style.display = 'block';
});

// Furnishing Details
function updateFurnishingDetails(furnishingType) {
    const furnishingDescription = document.getElementById("furnishingDescription");

    // Define descriptions for each furnishing type
    const furnishingDetails = {
        "Fully Furnished": `
            <ul>
                <li>Bed and Mattress</li>
                <li>Wardrobe</li>
                <li>Sofa</li>
                <li>Dining Table and Chairs</li>
                <li>Refrigerator</li>
                <li>Gas Stove</li>
                <li>Washing Machine</li>
                <li>Air Conditioner/Fan</li>
            </ul>
        `,
        "Partially Furnished": `
            <ul>
                <li>Bed and Mattress</li>
                <li>Wardrobe</li>
                <li>Dining Table</li>
                <li>Gas Stove</li>
                <li>Air Conditioner/Fan</li>
            </ul>
        `,
        "Not Furnished": `<p>No furnishings provided.</p>`
    };

    // Update the description box content
    furnishingDescription.innerHTML = furnishingDetails[furnishingType] || "<p>Select a furnishing type to view details.</p>";
}

setTimeout(function() {
    var alert = document.querySelector('.alert');
    if (alert) {
        alert.classList.remove('show');
    }
}, 5000); // Close alert after 5 seconds

/* ------ Book Button ------ */
document.getElementById("bookPropertyBtn").addEventListener("click", function() {
    var form = document.getElementById("bookingForm");
    if (form.style.display === "none" || form.style.display === "") {
        form.style.display = "block";
    } else {
        form.style.display = "none";
    }
});

function calculateRentPerPerson() {
    // Get the values of Monthly Rent and Total Tenants
    const monthlyRent = parseFloat(document.getElementById('monthlyRent').value) || 0;
    const totalTenants = parseInt(document.getElementById('totalTenants').value) || 0;

    // Calculate Monthly Rent Per Person
    const rentPerPerson = totalTenants > 0 ? (monthlyRent / totalTenants).toFixed(2) : 0;

    // Update the Monthly Rent Per Person field
    document.getElementById('monthlyRentPerPerson').value = rentPerPerson;
}

document.addEventListener("DOMContentLoaded", function () {
    var triggerTabList = [].slice.call(document.querySelectorAll('.nav-tabs .nav-link'));
    triggerTabList.forEach(function (triggerEl) {
        var tabTrigger = new bootstrap.Tab(triggerEl);

        triggerEl.addEventListener('click', function (event) {
            event.preventDefault();
            tabTrigger.show();
        });
    });
});
