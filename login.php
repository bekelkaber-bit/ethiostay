<?php
require_once 'includes/config.php';

// Handle language switching
if (isset($_GET['lang'])) {
    set_language($_GET['lang']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password';
    } else {
        $stmt = $conn->prepare("SELECT id, full_name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['full_name'];
                $_SESSION['user_email'] = $row['email'];
                $_SESSION['role'] = $row['role'];
                
                if ($row['role'] === 'admin') {
                    header('Location: admin/dashboard.php');
                } else {
                    header('Location: index.php');
                }
                exit();
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'Email not found';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EthioStay</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="auth-box">
            <h1>🏨 <?php echo t('site_title'); ?></h1>
            <h2><?php echo t('login_title'); ?></h2>
            
            <div class="language-switcher" style="justify-content: center; margin-bottom: 20px;">
                <a href="?lang=en" class="lang-btn <?php echo $current_lang == 'en' ? 'active' : ''; ?>">EN</a>
                <a href="?lang=am" class="lang-btn <?php echo $current_lang == 'am' ? 'active' : ''; ?>">አማ</a>
                <a href="?lang=om" class="lang-btn <?php echo $current_lang == 'om' ? 'active' : ''; ?>">Afaan</a>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['google_error'])): ?>
                <div class="alert alert-error"><?php echo $_SESSION['google_error']; unset($_SESSION['google_error']); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label><?php echo t('email'); ?></label>
                    <input type="email" name="email" required placeholder="you@example.com">
                </div>
                
                <div class="form-group">
                    <label><?php echo t('password'); ?></label>
                    <input type="password" name="password" required placeholder="Enter your password">
                </div>
                
                <button type="submit" class="btn btn-primary"><?php echo t('login_button'); ?></button>
            </form>
            
            <div class="social-login">
                <div class="divider">
                    <span>OR</span>
                </div>
                
                <a href="<?php echo 'https://accounts.google.com/o/oauth2/v2/auth?scope=openid+profile+email&redirect_uri=' . urlencode(GOOGLE_REDIRECT_URI) . '&response_type=code&client_id=' . GOOGLE_CLIENT_ID; ?>" 
                   class="btn btn-google">
                    <span class="google-icon">G</span>
                    <?php echo t('sign_in_google'); ?>
                </a>
            </div>
            
            <p class="auth-link"><?php echo t('no_account'); ?> <a href="register.php"><?php echo t('register'); ?></a></p>
            <p class="auth-link"><small>Demo Admin: admin@ethiostay.com / Admin@123</small></p>
            <p class="auth-link"><small>Demo Customer: customer@test.com / Customer@123</small></p>
        </div>
    </div>
</body>
</html>