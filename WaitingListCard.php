<?php while ($waitingProperty = $waitingListResult->fetch_assoc()): ?>
    <div class="col-md-4 mb-4">
        <div class="card property-card shadow-sm">
            <?php
            // Decode the Photo field to get the first image
            $photos = json_decode($waitingProperty['Photo'], true);
            $firstPhoto = is_array($photos) && count($photos) > 0 ? $photos[0] : 'uploads/default-image.jpg';
            ?>

            <img src="<?php echo htmlspecialchars($firstPhoto); ?>" alt="Property Image" class="property-image" style="height: 200px; object-fit: cover;">
            
            <div class="card-body">
                <!-- Property Details -->
                <h5 class="property-title"><?php echo htmlspecialchars($waitingProperty['PropertyType']); ?></h5>
                <p class="property-address"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($waitingProperty['PropertyAddress']); ?></p>
                <p class="property-price">RM <?php echo number_format($waitingProperty['PropertyPrice'], 2); ?></p>
                
                <!-- New Fields: Move-In Date, Move-Out Date, and Monthly Rent -->
                <p class="compact-text"><strong>Move-In Date:</strong> <?php echo htmlspecialchars($waitingProperty['MoveInDate'] ?? 'N/A'); ?></p>
                <p class="compact-text"><strong>Move-Out Date:</strong> <?php echo htmlspecialchars($waitingProperty['MoveOutDate'] ?? 'N/A'); ?></p>
                <p class="compact-text"><strong>Monthly Rent Per Person:</strong> RM <?php echo number_format($waitingProperty['MonthlyRentPerPerson']); ?></p><br>
                
                <!-- Buttons -->
                <div class="d-flex justify-content-between">
                    <a href="PropertyDetails.php?id=<?php echo $waitingProperty['PropertyID']; ?>" class="btn btn-primary">View Property</a>
                </div>
            </div>
        </div>
    </div>
<?php endwhile; ?>
