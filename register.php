<?php
require_once 'includes/config.php';

// Handle language switching
if (isset($_GET['lang'])) {
    set_language($_GET['lang']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    
    if (empty($full_name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            $error = 'Email already registered. Please login.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, phone, role) VALUES (?, ?, ?, ?, 'customer')");
            $stmt->bind_param("ssss", $full_name, $email, $hashed_password, $phone);
            
            if ($stmt->execute()) {
                $success = 'Registration successful! <a href="login.php">Login here</a>';
            } else {
                $error = 'Something went wrong. Please try again.';
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - EthioStay</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="auth-box">
            <h1>🏨 <?php echo t('site_title'); ?></h1>
            <h2><?php echo t('register_title'); ?></h2>
            
            <div class="language-switcher" style="justify-content: center; margin-bottom: 20px;">
                <a href="?lang=en" class="lang-btn <?php echo $current_lang == 'en' ? 'active' : ''; ?>">EN</a>
                <a href="?lang=am" class="lang-btn <?php echo $current_lang == 'am' ? 'active' : ''; ?>">አማ</a>
                <a href="?lang=om" class="lang-btn <?php echo $current_lang == 'om' ? 'active' : ''; ?>">Afaan</a>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label><?php echo t('full_name'); ?> *</label>
                    <input type="text" name="full_name" required placeholder="John Doe">
                </div>
                
                <div class="form-group">
                    <label><?php echo t('email'); ?> *</label>
                    <input type="email" name="email" required placeholder="you@example.com">
                </div>
                
                <div class="form-group">
                    <label><?php echo t('phone'); ?></label>
                    <input type="tel" name="phone" placeholder="+251-911-123456">
                </div>
                
                <div class="form-group">
                    <label><?php echo t('password'); ?> *</label>
                    <input type="password" name="password" required placeholder="Min 6 characters">
                </div>
                
                <div class="form-group">
                    <label><?php echo t('confirm_password'); ?> *</label>
                    <input type="password" name="confirm_password" required placeholder="Re-enter password">
                </div>
                
                <button type="submit" class="btn btn-primary"><?php echo t('register_button'); ?></button>
            </form>
            
            <div class="social-login">
                <div class="divider">
                    <span>OR</span>
                </div>
                
                <a href="<?php echo 'https://accounts.google.com/o/oauth2/v2/auth?scope=openid+profile+email&redirect_uri=' . urlencode(GOOGLE_REDIRECT_URI) . '&response_type=code&client_id=' . GOOGLE_CLIENT_ID; ?>" 
                   class="btn btn-google">
                    <span class="google-icon">G</span>
                    <?php echo t('sign_up_google'); ?>
                </a>
            </div>
            
            <p class="auth-link"><?php echo t('have_account'); ?> <a href="login.php"><?php echo t('login'); ?></a></p>
        </div>
    </div>
</body>
</html>