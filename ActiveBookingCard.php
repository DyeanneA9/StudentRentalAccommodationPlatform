<div class="col-md-4 mb-4">
    <div class="card property-card shadow-sm">
        <?php
        // Decode the Photo field to get the first image
        $photos = json_decode($activeBooking['Photo'], true);
        $firstPhoto = is_array($photos) && count($photos) > 0 ? $photos[0] : 'uploads/default-image.jpg';
        
        // Check if the current date matches the Move-Out Date
        $currentDate = new DateTime();
        $moveOutDate = new DateTime($activeBooking['MoveOutDate']);
        $isMoveOutDateToday = ($currentDate->format('Y-m-d') === $moveOutDate->format('Y-m-d'));
        ?>

        <img src="<?php echo htmlspecialchars($firstPhoto); ?>" alt="Property Image" class="property-image" style="height: 200px; object-fit: cover;">
        
        <div class="card-body">
            <!-- Property Details -->
            <h5 class="property-title"><?php echo htmlspecialchars($activeBooking['PropertyType']); ?></h5>
            <p class="property-address"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($activeBooking['PropertyAddress']); ?></p>
            <p class="property-price">RM <?php echo number_format($activeBooking['PropertyPrice'], 2); ?></p>
            
            <!-- New Fields: Move-In Date, Move-Out Date, and Monthly Rent -->
            <p class="compact-text"><strong>Move-In Date:</strong> <?php echo htmlspecialchars($activeBooking['MoveInDate']); ?></p>
            <p class="compact-text"><strong>Move-Out Date:</strong> <?php echo htmlspecialchars($activeBooking['MoveOutDate']); ?></p>
            <p class="compact-text"><strong>Monthly Rent:</strong> RM <?php echo number_format($activeBooking['PropertyPrice'], 2); ?></p><br>

            <p class="compact-text"><strong>Homeowner Bank Account: </strong><?php echo htmlspecialchars($activeBooking['bankNumber']); ?></p>
            <p class="compact-text"><strong>Homeowner Bank Name: </strong><?php echo htmlspecialchars($activeBooking['bankName']); ?></p><br>
            
            <!-- Buttons -->
            <div class="d-flex justify-content-between">
                <a href="PropertyDetails.php?id=<?php echo $activeBooking['PropertyID']; ?>" class="btn btn-primary">View Property</a>
                <a href="RatingForm.php?id=<?php echo $activeBooking['PropertyID']; ?>&rate=true" class="btn btn-success">Rate Property</a>
            </div>

        </div>
    </div>
</div>
