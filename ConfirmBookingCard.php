<?php
// Ensure $booking is defined
if (!isset($booking)) {
    echo "<p>Error: Booking data not available.</p>";
    return;
}

// Decode photo array
$imageArray = json_decode($booking['Photo'], true);
$firstImage = $imageArray[0] ?? 'uploads/default-image.jpg';
?>

<div class="col-md-4 col-12 mb-4">
    <div class="card property-card shadow-sm">
        <img src="<?php echo htmlspecialchars($firstImage); ?>" alt="Property Image" class="property-image" style="height: 200px; object-fit: cover;">
        <div class="property-info p-3">
            <h5 class="property-title"><?php echo htmlspecialchars($booking['PropertyType']); ?></h5>
            <p class="property-address"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($booking['PropertyAddress']); ?></p>
            <p class="property-price">RM <?php echo htmlspecialchars($booking['PropertyPrice']); ?></p>

            <p class="compact-text"><strong>Tenant:</strong> <?php echo htmlspecialchars($booking['TenantName']); ?></p>
            <p class="compact-text"><strong>Phone:</strong> <?php echo htmlspecialchars($booking['TenantPhone']); ?></p>
            <p class="compact-text"><strong>Booking Type:</strong> <?php echo htmlspecialchars($booking['BookingType']); ?></p>
            <p class="compact-text"><strong>Move-In Date:</strong> <?php echo htmlspecialchars(date('d M Y', strtotime($booking['MoveInDate']))); ?></p>
            <p class="compact-text"><strong>Move-Out Date:</strong> <?php echo htmlspecialchars(date('d M Y', strtotime($booking['MoveOutDate']))); ?></p>
            
            <div class="property-actions d-flex justify-content-between gap-2 mt-3">
                <a href="ApproveBooking.php?id=<?php echo $booking['BookingID']; ?>" class="btn btn-success flex-fill">Approve</a>
                <a href="CancelPendingBooking.php?id=<?php echo $booking['BookingID']; ?>" class="btn btn-danger flex-fill" onclick="return confirm('Are you sure you want to cancel this booking?');">Cancel</a>
            </div>

        </div>
    </div>
</div>
