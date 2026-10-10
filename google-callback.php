<?php
require_once 'includes/config.php';

// Check if code is present from Google OAuth
if (!isset($_GET['code'])) {
    header('Location: login.php');
    exit();
}

// Exchange authorization code for access token
$code = $_GET['code'];
$token_url = 'https://oauth2.googleapis.com/token';

$post_data = [
    'code' => $code,
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'grant_type' => 'authorization_code'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $token_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$token_data = json_decode($response, true);

if (!isset($token_data['access_token'])) {
    $_SESSION['google_error'] = 'Failed to authenticate with Google. Please try again.';
    header('Location: login.php');
    exit();
}

// Get user info from Google
$user_info_url = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $token_data['access_token'];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $user_info_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$user_info_response = curl_exec($ch);
curl_close($ch);

$user_info = json_decode($user_info_response, true);

if (!isset($user_info['id'])) {
    $_SESSION['google_error'] = 'Failed to get user information from Google. Please try again.';
    header('Location: login.php');
    exit();
}

$google_id = $user_info['id'];
$email = $user_info['email'];
$full_name = $user_info['name'];
$profile_picture = isset($user_info['picture']) ? $user_info['picture'] : '';

// Check if user exists with this Google ID
$stmt = $conn->prepare("SELECT id, full_name, email, role FROM users WHERE google_id = ?");
$stmt->bind_param("s", $google_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // User exists, log them in
    $user = $result->fetch_assoc();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['profile_picture'] = $profile_picture;
    
    if ($user['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit();
} else {
    // Check if user exists with this email (without Google ID)
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $email_result = $stmt->get_result();
    
    if ($email_result->num_rows > 0) {
        // Email exists but no Google ID - link the account
        $stmt = $conn->prepare("UPDATE users SET google_id = ?, profile_picture = ? WHERE email = ?");
        $stmt->bind_param("sss", $google_id, $profile_picture, $email);
        $stmt->execute();
        
        $user = $email_result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $full_name;
        $_SESSION['user_email'] = $email;
        $_SESSION['role'] = 'customer';
        $_SESSION['profile_picture'] = $profile_picture;
        
        header('Location: index.php');
        exit();
    } else {
        // New user - create account
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, google_id, profile_picture, role) VALUES (?, ?, ?, ?, 'customer')");
        $stmt->bind_param("ssss", $full_name, $email, $google_id, $profile_picture);
        
        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $full_name;
            $_SESSION['user_email'] = $email;
            $_SESSION['role'] = 'customer';
            $_SESSION['profile_picture'] = $profile_picture;
            
            header('Location: index.php');
            exit();
        } else {
            $_SESSION['google_error'] = 'Failed to create account. Please try again.';
            header('Location: register.php');
            exit();
        }
    }
}
?>
