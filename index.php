<?php
require_once 'includes/config.php';
include 'includes/header.php';

// Handle language switching
if (isset($_GET['lang'])) {
    set_language($_GET['lang']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$city = isset($_GET['city']) ? trim($_GET['city']) : '';

$sql = "SELECT h.*, 
        (SELECT MIN(price_per_night) FROM rooms WHERE hotel_id = h.id) as min_price,
        (SELECT COUNT(*) FROM rooms WHERE hotel_id = h.id) as room_count,
        (SELECT COUNT(*) FROM rooms r LEFT JOIN bookings b ON r.id = b.room_id WHERE r.hotel_id = h.id AND b.status IN ('pending', 'confirmed')) as booked_count
        FROM hotels h WHERE 1=1";

if ($search) {
    $sql .= " AND (h.name LIKE '%$search%' OR h.city LIKE '%$search%' OR h.description LIKE '%$search%')";
}
if ($city) {
    $sql .= " AND h.city LIKE '%$city%'";
}
$sql .= " ORDER BY h.created_at DESC";

$hotels = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EthioStay - Find Hotels in Ethiopia</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <h1>🏨 <?php echo t('site_title'); ?></h1>
                <nav>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <span><?php echo t('welcome'); ?>, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="admin/dashboard.php"><?php echo t('dashboard'); ?></a>
                        <?php endif; ?>
                        <a href="logout.php"><?php echo t('logout'); ?></a>
                    <?php else: ?>
                        <a href="login.php"><?php echo t('login'); ?></a>
                        <a href="register.php"><?php echo t('register'); ?></a>
                    <?php endif; ?>
                </nav>
                <div class="language-switcher">
                    <a href="?lang=en" class="lang-btn <?php echo $current_lang == 'en' ? 'active' : ''; ?>">EN</a>
                    <a href="?lang=am" class="lang-btn <?php echo $current_lang == 'am' ? 'active' : ''; ?>">አማ</a>
                    <a href="?lang=om" class="lang-btn <?php echo $current_lang == 'om' ? 'active' : ''; ?>">Afaan</a>
                </div>
            </div>
        </div>
    </header>
    
    <section class="hero">
        <div class="container">
            <h2><?php echo t('hero_title'); ?></h2>
            <p><?php echo t('hero_subtitle'); ?></p>
            
            <form method="GET" action="" class="search-form">
                <input type="text" name="search" placeholder="<?php echo t('search_placeholder'); ?>" value="<?php echo htmlspecialchars($search); ?>">
                <input type="text" name="city" placeholder="<?php echo t('city_placeholder'); ?>" value="<?php echo htmlspecialchars($city); ?>">
                <button type="submit" class="btn btn-primary"><?php echo t('search_button'); ?></button>
            </form>
        </div>
    </section>
    
    <main class="container">
        <h2><?php echo t('available_hotels'); ?></h2>
        
        <?php if ($hotels && $hotels->num_rows > 0): ?>
            <div class="hotel-grid">
                <?php while($hotel = $hotels->fetch_assoc()): 
                    $availability = $hotel['room_count'] - $hotel['booked_count'];
                    $availability_status = $availability > 0 ? t('available') : t('fully_booked');
                    $status_class = $availability > 0 ? 'available' : 'booked';
                ?>
                    <div class="hotel-card">
                        <div class="hotel-image">
                            <span class="availability-badge <?php echo $status_class; ?>">
                                <?php echo $availability_status; ?> (<?php echo $availability; ?> <?php echo t('rooms'); ?>)
                            </span>
                            <div class="hotel-rating">⭐ <?php echo $hotel['star_rating']; ?></div>
                        </div>
                        <div class="hotel-info">
                            <h3><?php echo htmlspecialchars($hotel['name']); ?></h3>
                            <p class="hotel-location">📍 <?php echo htmlspecialchars($hotel['city']); ?></p>
                            <p class="hotel-desc"><?php echo substr(htmlspecialchars($hotel['description']), 0, 120); ?>...</p>
                            <div class="hotel-footer">
                                <span class="hotel-price"><?php echo t('from'); ?> <?php echo CURRENCY_SYMBOL; ?> <?php echo number_format($hotel['min_price'], 2); ?><?php echo t('per_night'); ?></span>
                                <a href="hotel-detail.php?id=<?php echo $hotel['id']; ?>" class="btn btn-secondary"><?php echo t('view_rooms'); ?></a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="no-results"><?php echo t('no_results'); ?></p>
        <?php endif; ?>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>