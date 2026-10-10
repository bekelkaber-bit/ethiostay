<?php
require_once 'includes/config.php';

// Check if user is logged in and has a booking
if (!isset($_SESSION['user_id']) || !isset($_SESSION['current_booking_id'])) {
    header('Location: index.php');
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

// Handle language switching
if (isset($_GET['lang'])) {
    set_language($_GET['lang']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

$booking_id = $_SESSION['current_booking_id'];
$amount = $_SESSION['booking_amount'];

// Get booking details
$booking = $conn->prepare("SELECT b.*, r.room_type, h.name as hotel_name, u.full_name, u.email 
                           FROM bookings b 
                           JOIN rooms r ON b.room_id = r.id 
                           JOIN hotels h ON b.hotel_id = h.id 
                           JOIN users u ON b.user_id = u.id 
                           WHERE b.id = ? AND b.user_id = ?");
$booking->bind_param("ii", $booking_id, $_SESSION['user_id']);
$booking->execute();
$booking_data = $booking->get_result()->fetch_assoc();

if (!$booking_data) {
    header('Location: index.php');
    exit();
}

// Handle payment processing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {
    $payment_method = $_POST['payment_method'];
    $transaction_reference = trim($_POST['transaction_reference']);
    
    // Map payment method to database enum values
    $payment_method_map = [
        'bank' => 'bank_transfer',
        'telebirr' => 'telebirr',
        'cbe_birr' => 'cbe_birr'
    ];
    $payment_method = $payment_method_map[$payment_method] ?? $payment_method;
    
    if (empty($transaction_reference)) {
        $error = 'Please enter your transaction reference number.';
    } elseif (strlen($transaction_reference) < 5) {
        $error = 'Transaction reference must be at least 5 characters.';
    } elseif (strlen($transaction_reference) > 50) {
        $error = 'Transaction reference is too long.';
    } else {
        $transaction_id = 'TXN-' . strtoupper(uniqid());
        $user_reference = $transaction_reference; // Store user's reference for display
        
        $conn->begin_transaction();
        
        try {
            // Insert payment record with system transaction_id and user's reference
            $stmt = $conn->prepare("INSERT INTO payments (booking_id, user_id, amount, payment_method, payment_status, transaction_id, user_transaction_reference) 
                                    VALUES (?, ?, ?, ?, 'pending', ?, ?)");
            $stmt->bind_param("iidsss", $booking_id, $_SESSION['user_id'], $amount, $payment_method, $transaction_id, $transaction_reference);
            $stmt->execute();
            
            // Keep booking status as pending until admin verification
            $stmt = $conn->prepare("UPDATE bookings SET status = 'pending' WHERE id = ?");
            $stmt->bind_param("i", $booking_id);
            $stmt->execute();
            
            $conn->commit();
            
            // Clear session booking data
            unset($_SESSION['current_booking_id']);
            unset($_SESSION['booking_amount']);
            
            $success = true;
            $booking_reference = $transaction_id;
            $transaction_reference = $user_reference; // Make available for success page
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Payment processing failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - EthioStay</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <h1>🏨 <?php echo t('site_title'); ?></h1>
                <nav>
                    <a href="index.php">Home</a>
                    <a href="logout.php"><?php echo t('logout'); ?></a>
                </nav>
                <div class="language-switcher">
                    <a href="?lang=en" class="lang-btn <?php echo $current_lang == 'en' ? 'active' : ''; ?>">EN</a>
                    <a href="?lang=am" class="lang-btn <?php echo $current_lang == 'am' ? 'active' : ''; ?>">አማ</a>
                    <a href="?lang=om" class="lang-btn <?php echo $current_lang == 'om' ? 'active' : ''; ?>">Afaan</a>
                </div>
            </div>
        </div>
    </header>
    
    <main class="container">
        <?php if (isset($success) && $success): ?>
            <div class="payment-success">
                <div class="success-icon">✅</div>
                <h2>Payment Submitted Successfully</h2>
                <p>Your payment has been submitted and is pending verification by our admin team.</p>
                <div class="booking-details">
                    <p><strong>Transaction Reference:</strong> <?php echo $booking_reference; ?></p>
                    <p><strong>Your Transaction ID:</strong> <?php echo htmlspecialchars($transaction_reference); ?></p>
                    <p><strong><?php echo t('hotel'); ?>:</strong> <?php echo htmlspecialchars($booking_data['hotel_name']); ?></p>
                    <p><strong><?php echo t('room'); ?>:</strong> <?php echo htmlspecialchars($booking_data['room_type']); ?></p>
                    <p><strong>Check-in:</strong> <?php echo date('M d, Y', strtotime($booking_data['check_in_date'])); ?></p>
                    <p><strong>Check-out:</strong> <?php echo date('M d, Y', strtotime($booking_data['check_out_date'])); ?></p>
                    <p><strong>Amount Paid:</strong> <?php echo CURRENCY_SYMBOL; ?> <?php echo number_format($amount, 2); ?></p>
                </div>
                <a href="index.php" class="btn btn-primary"><?php echo t('back_home'); ?></a>
            </div>
        <?php else: ?>
            <div class="payment-wrapper">
                <h2><?php echo t('complete_payment'); ?></h2>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="payment-summary">
                    <h3><?php echo t('booking_summary'); ?></h3>
                    <p><strong><?php echo t('hotel'); ?>:</strong> <?php echo htmlspecialchars($booking_data['hotel_name']); ?></p>
                    <p><strong><?php echo t('room'); ?>:</strong> <?php echo htmlspecialchars($booking_data['room_type']); ?></p>
                    <p><strong><?php echo t('guest'); ?>:</strong> <?php echo htmlspecialchars($booking_data['full_name']); ?></p>
                    <p><strong><?php echo t('amount'); ?>:</strong> <?php echo CURRENCY_SYMBOL; ?> <?php echo number_format($amount, 2); ?></p>
                </div>
                
                <form method="POST" action="" class="payment-form">
                    <h3><?php echo t('select_payment'); ?></h3>
                    
                    <div class="payment-methods">
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="telebirr" checked>
                            <div class="payment-option-content">
                                <span class="payment-icon">📱</span>
                                <span class="payment-name"><?php echo t('telebirr'); ?></span>
                                <span class="payment-desc"><?php echo t('mobile_money'); ?></span>
                            </div>
                        </label>
                        
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="cbe_birr">
                            <div class="payment-option-content">
                                <span class="payment-icon">🏦</span>
                                <span class="payment-name"><?php echo t('cbe_birr'); ?></span>
                                <span class="payment-desc"><?php echo t('commercial_bank'); ?></span>
                            </div>
                        </label>
                        
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="bank_transfer">
                            <div class="payment-option-content">
                                <span class="payment-icon">💳</span>
                                <span class="payment-name"><?php echo t('bank_transfer'); ?></span>
                                <span class="payment-desc"><?php echo t('other_banks'); ?></span>
                            </div>
                        </label>
                    </div>
                    
                    <div class="payment-account-info">
                        <div class="account-details">
                            <p><strong><?php echo t('cbe_account'); ?></strong></p>
                            <p><strong><?php echo t('telebirr_account'); ?></strong></p>
                        </div>
                        <p class="payment-instructions"><?php echo t('payment_instructions'); ?></p>
                    </div>
                    
                    <div class="form-group">
                        <label><?php echo t('transaction_reference'); ?> *</label>
                        <input type="text" name="transaction_reference" required placeholder="Enter your transaction ID/reference">
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg"><?php echo t('submit_payment'); ?> <?php echo CURRENCY_SYMBOL; ?> <?php echo number_format($amount, 2); ?></button>
                </form>
            </div>
        <?php endif; ?>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
