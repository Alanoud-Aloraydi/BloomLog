<?php
require_once "config.php";

session_start();

// التحقق من أن المستخدم مسجل دخوله
if (!isset($_SESSION['userid'])) {
    header('Location: index.php');
    exit();
}

// Access control: only admins may open this page. A regular logged-in
// user is sent back to their own homepage.
if (($_SESSION['user_role'] ?? 'user') !== 'admin') {
    header('Location: homepage.php');
    exit();
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// جلب بيانات المدير
$user_id = $_SESSION['userid'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE userid = ?");
$stmt->execute([$user_id]);
$admin = $stmt->fetch();

// جلب كل النباتات من الكتالوج
$stmt = $pdo->prepare("SELECT * FROM plantcatalog ORDER BY plantid DESC");
$stmt->execute();
$plants = $stmt->fetchAll();

// إضافة نبات جديد إلى الكتالوج
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_plant'])) {
    $plantName = trim($_POST['plantName']);
    $wateringfrequency = (int)$_POST['wateringfrequency'];
    $mintemperature = (float)$_POST['mintemperature'];
    $maxtemperature = (float)$_POST['maxtemperature'];
    $minhumidity = (float)$_POST['minhumidity'];
    $maxhumidity = (float)$_POST['maxhumidity'];
    $plant_Info = trim($_POST['plant_Info']);
    $plant_summary = trim($_POST['plant_summary']);
    $image_path = trim($_POST['image_path']);

    try {
        $insert_stmt = $pdo->prepare("
            INSERT INTO plantcatalog 
            (plantName, wateringfrequency, mintemperature, maxtemperature, minhumidity, maxhumidity, plant_Info, plant_summary, image_path) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $insert_stmt->execute([
            $plantName, $wateringfrequency, $mintemperature, $maxtemperature, 
            $minhumidity, $maxhumidity, $plant_Info, $plant_summary, $image_path
        ]);
        
        header("Location: admin.php?success=1");
        exit();
        
    } catch (PDOException $e) {
        $error_message = "Error adding plant: " . $e->getMessage();
    }
}

// حذف نبات من الكتالوج
if (isset($_GET['delete_plant'])) {
    $plant_id = (int)$_GET['delete_plant'];
    
    try {
        $delete_stmt = $pdo->prepare("DELETE FROM plantcatalog WHERE plantid = ?");
        $delete_stmt->execute([$plant_id]);
        header("Location: admin.php?success=1");
        exit();
    } catch (PDOException $e) {
        $error_message = "Error deleting plant: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BloomLog - Admin Panel</title>
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
                        <li><a href="homepage.php"><i class="fas fa-home"></i> Back to Home</a></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Admin Panel Section -->
    <section class="container">
        <!-- رسائل النجاح والخطأ -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Operation completed successfully!</div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="dashboard-header">
            <div class="user-info">
                <div class="user-avatar">AD</div>
                <div class="welcome-message">
                    <h2>Welcome, <?php echo htmlspecialchars($admin['name']); ?>!</h2>
                    <p><i class="fas fa-shield-alt"></i> Plant Catalog Management</p>
                </div>
            </div>
            <button class="btn" id="toggle-form-btn">
                <i class="fas fa-plus"></i> Add New Plant
            </button>
        </div>

        <!-- إحصائيات -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($plants); ?></div>
                <div class="stat-label">Total Plants in Catalog</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    <?php
                    $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
                    echo $total_users;
                    ?>
                </div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    <?php
                    $total_user_plants = $pdo->query("SELECT COUNT(*) FROM userplants")->fetchColumn();
                    echo $total_user_plants;
                    ?>
                </div>
                <div class="stat-label">User Plants</div>
            </div>
        </div>

        <!-- نموذج إضافة نبات جديد -->
        <div class="card add-plant-form-container" id="add-plant-form" style="display: none;">
            <div class="form-header">
                <h2><i class="fas fa-plus-circle"></i> Add New Plant to Catalog</h2>
                <button type="button" class="btn btn-secondary" id="close-form-btn">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
            <form method="POST" class="add-plant-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="plantName">Plant Name *</label>
                        <input type="text" id="plantName" name="plantName" placeholder="Snake Plant" required>
                    </div>
                    <div class="form-group">
                        <label for="wateringfrequency">Watering Frequency (days) *</label>
                        <input type="number" id="wateringfrequency" name="wateringfrequency" placeholder="14" min="1" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="mintemperature">Min Temperature (°C) *</label>
                        <input type="number" id="mintemperature" name="mintemperature" placeholder="18.00" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="maxtemperature">Max Temperature (°C) *</label>
                        <input type="number" id="maxtemperature" name="maxtemperature" placeholder="30.00" step="0.01" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="minhumidity">Min Humidity (%) *</label>
                        <input type="number" id="minhumidity" name="minhumidity" placeholder="40.00" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="maxhumidity">Max Humidity (%) *</label>
                        <input type="number" id="maxhumidity" name="maxhumidity" placeholder="60.00" step="0.01" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="plant_Info">Plant Information *</label>
                    <textarea id="plant_Info" name="plant_Info" rows="2" placeholder="Detailed information about the plant..." required></textarea>
                </div>

                <div class="form-group">
                    <label for="plant_summary">Plant Summary *</label>
                    <textarea id="plant_summary" name="plant_summary" rows="2" placeholder="Brief summary of the plant..." required></textarea>
                </div>

                <div class="form-group">
                    <label for="image_path">Image URL *</label>
                    <input type="url" id="image_path" name="image_path" placeholder="https://example.com/plant-image.jpg" required>
                </div>

                <div class="form-actions">
                    <button type="reset" class="btn btn-secondary">Clear Form</button>
                    <button type="submit" name="add_plant" class="btn">Add Plant to Catalog</button>
                </div>
            </form>
        </div>

        <!-- إدارة كتالوج النباتات -->
        <div class="card">
            <h2><i class="fas fa-seedling"></i> Plant Catalog Management</h2>
            <p>Manage the plant catalog available to all users</p>
            
            <?php if (count($plants) > 0): ?>
                <div class="my-plants-list">
                    <?php foreach ($plants as $plant): ?>
                    <div class="my-plant-card card">
                        <div class="plant-image">
                            <img src="<?php echo htmlspecialchars($plant['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($plant['plantName']); ?>"
                                 onerror="this.src='https://via.placeholder.com/300x200/4CAF50/white?text=Plant+Image'">
                        </div>
                        <div class="plant-nickname"><?php echo htmlspecialchars($plant['plantName']); ?></div>
                        <div class="plant-species"><?php echo htmlspecialchars($plant['plant_summary']); ?></div>
                        <div class="plant-details">
                            <div class="detail-item">
                                <span class="detail-label">Watering:</span>
                                <span>Every <?php echo $plant['wateringfrequency']; ?> days</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Temperature:</span>
                                <span><?php echo $plant['mintemperature']; ?>°C - <?php echo $plant['maxtemperature']; ?>°C</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Humidity:</span>
                                <span><?php echo $plant['minhumidity']; ?>% - <?php echo $plant['maxhumidity']; ?>%</span>
                            </div>
                        </div>
                        <p class="plant-info"><strong>Info:</strong> <?php echo htmlspecialchars($plant['plant_Info']); ?></p>
                        <div class="plant-actions">
                            <a href="admin.php?delete_plant=<?php echo $plant['plantid']; ?>" 
                               class="btn btn-accent delete-catalog-btn"
                               onclick="return confirm('Are you sure you want to remove this plant from the catalog?')">
                                <i class="fas fa-trash"></i> Remove from Catalog
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">🌱</div>
                    <h3>No Plants in Catalog</h3>
                    <p>Start by adding plants to the catalog!</p>
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
                </div>
                <div class="footer-section">
                    <h3>Admin Tools</h3>
                    <ul>
                        <li><a href="admin.php"><i class="fas fa-cog"></i> Plant Catalog</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <ul>
                        <li><a href="#"><i class="fas fa-envelope"></i> admin@bloomlog.com</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2025 BloomLog. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Toggle form visibility
        const toggleFormBtn = document.getElementById('toggle-form-btn');
        const addPlantForm = document.getElementById('add-plant-form');
        const closeFormBtn = document.getElementById('close-form-btn');

        toggleFormBtn.addEventListener('click', function() {
            if (addPlantForm.style.display === 'none') {
                addPlantForm.style.display = 'block';
                toggleFormBtn.innerHTML = '<i class="fas fa-minus"></i> Hide Form';
                toggleFormBtn.classList.add('btn-secondary');
                addPlantForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                addPlantForm.style.display = 'none';
                toggleFormBtn.innerHTML = '<i class="fas fa-plus"></i> Add New Plant';
                toggleFormBtn.classList.remove('btn-secondary');
            }
        });

        closeFormBtn.addEventListener('click', function() {
            addPlantForm.style.display = 'none';
            toggleFormBtn.innerHTML = '<i class="fas fa-plus"></i> Add New Plant';
            toggleFormBtn.classList.remove('btn-secondary');
        });
    </script>
</body>
</html>