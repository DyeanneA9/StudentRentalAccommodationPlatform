<?php
// Check if $expiredBookingResult is set and not null
if (!isset($expiredBookingResult) || !$expiredBookingResult) {
    echo "<p>No expired bookings available.</p>";
    return;
}

// Iterate through expired bookings
while ($expiredBooking = $expiredBookingResult->fetch_assoc()):
?>
    <div class="col-md-4 mb-4">
        <div class="card property-card shadow-sm">
            <?php
            // Decode the Photo field to get the first image
            $photos = json_decode($expiredBooking['Photo'], true);
            $firstPhoto = is_array($photos) && count($photos) > 0 ? $photos[0] : 'uploads/default-image.jpg';
            ?>

            <img src="<?php echo htmlspecialchars($firstPhoto); ?>" alt="Property Image" class="property-image" style="height: 200px; object-fit: cover;">
            
            <div class="card-body">
                <!-- Property Details -->
                <h5 class="property-title"><?php echo htmlspecialchars($expiredBooking['PropertyType']); ?></h5>
                <p class="property-address"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($expiredBooking['PropertyAddress']); ?></p>
                <p class="property-price">RM <?php echo number_format($expiredBooking['PropertyPrice'], 2); ?></p>
                
                <!-- New Fields: Move-In Date, Move-Out Date, and Monthly Rent -->
                <p class="compact-text"><strong>Move-In Date:</strong> <?php echo htmlspecialchars($expiredBooking['MoveInDate']); ?></p>
                <p class="compact-text"><strong>Move-Out Date:</strong> <?php echo htmlspecialchars($expiredBooking['MoveOutDate']); ?></p>
                <p class="compact-text"><strong>Monthly Rent:</strong> RM <?php echo number_format($expiredBooking['MonthlyRent'], 2); ?></p><br>
                
                <!-- Buttons -->
                <div class="d-flex justify-content-between">
                    <a href="PropertyDetails.php?id=<?php echo $expiredBooking['PropertyID']; ?>" class="btn btn-primary">View Property</a>
                    <a href="PropertyDetails.php?id=<?php echo $expiredBooking['PropertyID']; ?>&rate=true" class="btn btn-success">Rate Property</a>
                </div>
            </div>
        </div>
    </div>
<?php endwhile; ?>
