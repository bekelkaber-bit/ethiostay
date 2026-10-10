<?php
require_once 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Verify user exists in database
$user_check = $conn->prepare("SELECT id FROM users WHERE id = ?");
$user_check->bind_param("i", $_SESSION['user_id']);
$user_check->execute();
$user_result = $user_check->get_result();

if ($user_result->num_rows === 0) {
    // User doesn't exist, clear session and redirect to login
    session_destroy();
    header('Location: login.php');
    exit();
}

$room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : '';
$check_out = isset($_GET['check_out']) ? $_GET['check_out'] : '';
$error = '';
$success = '';

// Get room details
$room = $conn->prepare("SELECT r.*, h.name as hotel_name, h.id as hotel_id, h.city 
                         FROM rooms r 
                         JOIN hotels h ON r.hotel_id = h.id 
                         WHERE r.id = ?");
$room->bind_param("i", $room_id);
$room->execute();
$room_data = $room->get_result()->fetch_assoc();

if (!$room_data) {
    header('Location: index.php');
    exit();
}

// Check availability for selected dates
if ($check_in && $check_out) {
    $check_avail = $conn->prepare("SELECT COUNT(*) as booked 
                                    FROM bookings 
                                    WHERE room_id = ? 
                                    AND status IN ('pending', 'confirmed')
                                    AND ((check_in_date <= ? AND check_out_date > ?) 
                                         OR (check_in_date < ? AND check_out_date >= ?))");
    $check_avail->bind_param("issss", $room_id, $check_out, $check_in, $check_out, $check_in);
    $check_avail->execute();
    $avail_result = $check_avail->get_result()->fetch_assoc();
    $is_available = $avail_result['booked'] == 0;
} else {
    $is_available = true;
}

// Get services for this hotel
$services = $conn->prepare("SELECT * FROM services WHERE hotel_id = ?");
$services->bind_param("i", $room_data['hotel_id']);
$services->execute();
$services_list = $services->get_result();

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book'])) {
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $selected_services = isset($_POST['services']) ? $_POST['services'] : [];
    $user_id = $_SESSION['user_id'];
    
    // Validate dates
    if (empty($check_in) || empty($check_out)) {
        $error = 'Please select check-in and check-out dates.';
    } elseif (strtotime($check_out) <= strtotime($check_in)) {
        $error = 'Check-out date must be after check-in date.';
    } else {
        // Calculate total price
        $nights = (strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24);
        $room_total = $nights * $room_data['price_per_night'];
        $services_total = 0;
        
        // Calculate service costs
        $service_details = [];
        if (!empty($selected_services)) {
            $service_ids = implode(',', array_map('intval', $selected_services));
            $service_query = $conn->query("SELECT id, service_name, price FROM services WHERE id IN ($service_ids)");
            while ($s = $service_query->fetch_assoc()) {
                $services_total += $s['price'];
                $service_details[] = $s;
            }
        }
        
        $grand_total = $room_total + $services_total;
        
        // Insert booking
        $conn->begin_transaction();
        
        try {
            // Insert into bookings
            $stmt = $conn->prepare("INSERT INTO bookings (user_id, room_id, hotel_id, check_in_date, check_out_date, total_room_price, total_services_price, grand_total, status) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("iiisdddd", $user_id, $room_id, $room_data['hotel_id'], $check_in, $check_out, $room_total, $services_total, $grand_total);
            $stmt->execute();
            $booking_id = $conn->insert_id;
            
            // Insert booking services
            foreach ($service_details as $service) {
                $stmt = $conn->prepare("INSERT INTO booking_services (booking_id, service_id, quantity, price) VALUES (?, ?, 1, ?)");
                $stmt->bind_param("iid", $booking_id, $service['id'], $service['price']);
                $stmt->execute();
            }
            
            $conn->commit();
            
            // Store booking ID for payment
            $_SESSION['current_booking_id'] = $booking_id;
            $_SESSION['booking_amount'] = $grand_total;
            
            header("Location: payment.php");
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Booking failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Room - EthioStay</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <h1>🏨 EthioStay</h1>
                <nav>
                    <a href="index.php">Home</a>
                    <a href="logout.php">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    
    <main class="container">
        <div class="booking-wrapper">
            <h2>Book Your Stay</h2>
            
            <div class="booking-summary">
                <h3><?php echo htmlspecialchars($room_data['hotel_name']); ?></h3>
                <p><strong>Room:</strong> <?php echo htmlspecialchars($room_data['room_type']); ?></p>
                <p><strong>Price:</strong> <?php echo CURRENCY_SYMBOL; ?> <?php echo number_format($room_data['price_per_night'], 2); ?> / night</p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($room_data['city']); ?></p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" class="booking-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>Check-in Date *</label>
                        <input type="date" name="check_in" value="<?php echo $check_in; ?>" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Check-out Date *</label>
                        <input type="date" name="check_out" value="<?php echo $check_out; ?>" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Additional Services</label>
                    <div class="services-list">
                        <?php if ($services_list->num_rows > 0): ?>
                            <?php while($service = $services_list->fetch_assoc()): ?>
                                <label class="service-checkbox">
                                    <input type="checkbox" name="services[]" value="<?php echo $service['id']; ?>">
                                    <span><?php echo htmlspecialchars($service['service_name']); ?></span>
                                    <span class="service-price">+<?php echo CURRENCY_SYMBOL; ?> <?php echo number_format($service['price'], 2); ?></span>
                                </label>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>No additional services available.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <button type="submit" name="book" class="btn btn-primary btn-lg">Proceed to Payment</button>
            </form>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
