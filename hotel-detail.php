<?php
require_once 'includes/config.php';

$hotel_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$hotel_id) {
    header('Location: index.php');
    exit();
}

// Get hotel details
$hotel = $conn->prepare("SELECT * FROM hotels WHERE id = ?");
$hotel->bind_param("i", $hotel_id);
$hotel->execute();
$hotel_data = $hotel->get_result()->fetch_assoc();

if (!$hotel_data) {
    header('Location: index.php');
    exit();
}

// Get rooms for this hotel
$rooms = $conn->prepare("SELECT r.*, 
                        (SELECT COUNT(*) FROM bookings b WHERE b.room_id = r.id AND b.status IN ('pending', 'confirmed') 
                         AND ((b.check_in_date <= CURDATE() AND b.check_out_date > CURDATE()) 
                              OR (b.check_in_date < CURDATE() AND b.check_out_date >= CURDATE()))) as current_bookings
                        FROM rooms r 
                        WHERE r.hotel_id = ? AND r.is_available = 1");
$rooms->bind_param("i", $hotel_id);
$rooms->execute();
$rooms_result = $rooms->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($hotel_data['name']); ?> - EthioStay</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <h1>🏨 EthioStay</h1>
                <nav>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="admin/dashboard.php">Dashboard</a>
                        <?php endif; ?>
                        <a href="logout.php">Logout</a>
                    <?php else: ?>
                        <a href="login.php">Login</a>
                        <a href="register.php">Register</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>
    
    <main class="container">
        <div class="hotel-detail-wrapper">
            <div class="hotel-header">
                <h2><?php echo htmlspecialchars($hotel_data['name']); ?></h2>
                <div class="hotel-meta">
                    <span class="hotel-rating">⭐ <?php echo $hotel_data['star_rating']; ?></span>
                    <span class="hotel-location">📍 <?php echo htmlspecialchars($hotel_data['city']); ?></span>
                </div>
            </div>
            
            <div class="hotel-description">
                <h3>About This Hotel</h3>
                <p><?php echo nl2br(htmlspecialchars($hotel_data['description'])); ?></p>
                
                <div class="hotel-contact">
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($hotel_data['address']); ?></p>
                    <?php if ($hotel_data['phone']): ?>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($hotel_data['phone']); ?></p>
                    <?php endif; ?>
                    <?php if ($hotel_data['email']): ?>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($hotel_data['email']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="rooms-section">
                <h3>Available Rooms</h3>
                
                <?php if ($rooms_result->num_rows > 0): ?>
                    <div class="rooms-grid">
                        <?php while($room = $rooms_result->fetch_assoc()): 
                            $is_available = $room['max_occupancy'] > $room['current_bookings'];
                        ?>
                            <div class="room-card">
                                <div class="room-header">
                                    <h4><?php echo htmlspecialchars($room['room_type']); ?></h4>
                                    <span class="room-price"><?php echo CURRENCY_SYMBOL; ?> <?php echo number_format($room['price_per_night'], 2); ?>/night</span>
                                </div>
                                
                                <div class="room-details">
                                    <p><strong>Capacity:</strong> <?php echo $room['max_occupancy']; ?> guests</p>
                                    <p><strong>Size:</strong> <?php echo $room['room_size']; ?> m²</p>
                                    <p><strong>Beds:</strong> <?php echo htmlspecialchars($room['bed_type']); ?></p>
                                </div>
                                
                                <div class="room-amenities">
                                    <p><strong>Amenities:</strong></p>
                                    <ul>
                                        <?php 
                                        $amenities = explode(',', $room['amenities']);
                                        foreach ($amenities as $amenity): ?>
                                            <li><?php echo htmlspecialchars(trim($amenity)); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                
                                <div class="room-availability">
                                    <?php if ($is_available): ?>
                                        <span class="available-badge">Available</span>
                                        <?php if (isset($_SESSION['user_id'])): ?>
                                            <a href="booking.php?room_id=<?php echo $room['id']; ?>" class="btn btn-primary">Book Now</a>
                                        <?php else: ?>
                                            <a href="login.php" class="btn btn-primary">Login to Book</a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="booked-badge">Fully Booked</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="no-rooms">No rooms available at this hotel.</p>
                <?php endif; ?>
            </div>
            
            <div class="back-link">
                <a href="index.php" class="btn btn-secondary">← Back to Hotels</a>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
