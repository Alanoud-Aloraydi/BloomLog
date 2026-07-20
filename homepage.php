<?php
require_once "config.php";

session_start();

// التحقق من تسجيل الدخول - استخدام userid بدل user_id
if (!isset($_SESSION['userid'])) {
    header('Location: index.php');
    exit();
}

// إعدادات الوقت
date_default_timezone_set('Asia/Riyadh');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// جلب بيانات المستخدم - استخدام userid
$user_id = $_SESSION['userid'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE userid = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: index.php');
    exit();
}

// باقي الكود يبقى كما هو...

// جلب نباتات المستخدم
$sql = "SELECT up.*, pc.plantName, pc.image_path, pc.wateringfrequency, pc.plant_summary
        FROM userplants up 
        JOIN plantcatalog pc ON up.plant_catalog_id = pc.plantid 
        WHERE up.user_id = ? 
        ORDER BY up.date_added DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$user_plants = $stmt->fetchAll();

// حساب الإحصائيات
$total_plants = count($user_plants);

// حساب النباتات التي تحتاج الري
$needs_watering = 0;
foreach ($user_plants as $plant) {
    $today = new DateTime();
    $next_watered = new DateTime($plant['next_watered_date']);
    if ($next_watered <= $today) {
        $needs_watering++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BloomLog - My Garden</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <span class="logo-icon"><img src="image/logo.PNG" alt="bloomlog logo" width="130" height="100"></span>
                    <h1>BloomLog</h1>
                </div>
                <nav>
                    <ul>
                        <li><a href="homepage.php"><i class="fas fa-home"></i> Home</a></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Homepage Section -->
    <section class="container">
        <div class="dashboard-header">
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($user['name'], 0, 2)); ?></div>
                <div class="welcome-message">
                    <h2>Welcome, <?php echo htmlspecialchars(explode(' ', $user['name'])[0]); ?>!</h2>
                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($user['city']); ?></p>
                </div>
            </div>
            <a href="add-plant.php" class="btn" id="add-plant-btn"><i class="fas fa-plus"></i> Add New Plant</a>
        </div>

        <!-- Statistics -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_plants; ?></div>
                <div class="stat-label">Total Plants</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $needs_watering; ?></div>
                <div class="stat-label">Need Watering</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($user_plants) > 0 ? round(($needs_watering / count($user_plants)) * 100) : 0; ?>%</div>
                <div class="stat-label">Care Needed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo date('d'); ?></div>
                <div class="stat-label">Days This Month</div>
            </div>
        </div>

        <div class="card">
            <h2>My Plants</h2>
            <?php if (count($user_plants) > 0): ?>
                <div class="my-plants-list">
                    <?php foreach ($user_plants as $plant): 
                        // حساب حالة الري
                        $today = new DateTime();
                        $last_watered = new DateTime($plant['last_watered_date']);
                        $next_watered = new DateTime($plant['next_watered_date']);
                        $days_remaining = $today->diff($next_watered)->days;
                        $needs_water = $next_watered <= $today;
                        
                        // حساب نسبة التقدم (guard against divide-by-zero when
                        // last and next watering dates are the same)
                        $total_days = $last_watered->diff($next_watered)->days;
                        $days_passed = $last_watered->diff($today)->days;
                        $progress = $total_days > 0
                            ? min(100, max(0, ($days_passed / $total_days) * 100))
                            : 100;
                    ?>
                    <div class="my-plant-card card">
                        <div class="plant-image">
                            <img src="<?php echo htmlspecialchars($plant['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($plant['plantName']); ?>"
                                 onerror="this.src='https://via.placeholder.com/300x200/4CAF50/white?text=Plant+Image'">
                        </div>
                        <div class="plant-nickname"><?php echo htmlspecialchars($plant['nickname']); ?></div>
                        <div class="plant-species"><?php echo htmlspecialchars($plant['plantName']); ?></div>
                        <div class="plant-details">
                            <div class="detail-item">
                                <span class="detail-label">Watering Schedule:</span>
                                <span>Every <?php echo $plant['wateringfrequency']; ?> days</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Last Watered:</span>
                                <span><?php echo date('M j, Y', strtotime($plant['last_watered_date'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Status:</span>
                                <span class="watering-status <?php echo $needs_water ? 'needs-water' : 'watered'; ?>">
                                    <?php echo $needs_water ? 'Needs Watering' : 'Watered'; ?>
                                </span>
                            </div>
                        </div>
                        <div class="watering-progress">
                            <div class="watering-progress-bar" style="width: <?php echo $progress; ?>%"></div>
                        </div>
                        <p><strong>Notes:</strong> <?php echo $plant['notes'] ? htmlspecialchars($plant['notes']) : 'No notes'; ?></p>
                        <div class="plant-actions">
                            <a href="view-plant.php?id=<?php echo $plant['user_plant_id']; ?>" class="btn">View Details</a>
                            <button class="btn btn-accent delete-plant-btn" data-plantid="<?php echo $plant['user_plant_id']; ?>">Delete</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">🌱</div>
                    <h3>No Plants Yet</h3>
                    <p>Start by adding your first plant to your garden!</p>
                    <a href="add-plant.php" class="btn">Add Your First Plant</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>BloomLog</h3>
                    <p>Your personal plant management assistant.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="homepage.php"><i class="fas fa-home"></i> Home</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <ul>
                        <li><a href="#"><i class="fas fa-envelope"></i> info@bloomlog.com</a></li>
                        <li><a href="#"><i class="fas fa-phone"></i> (123) 456-7890</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2025 BloomLog. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Delete functionality
        document.querySelectorAll('.delete-plant-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const plantId = this.getAttribute('data-plantid');
                
                if (confirm('Are you sure you want to delete this plant?')) {
                    fetch('remove_plant.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'plant_id=' + plantId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.closest('.my-plant-card').remove();
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error deleting plant');
                    });
                }
            });
        });
    </script>
</body>
</html>