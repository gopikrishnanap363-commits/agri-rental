<?php
/**
 * Agricultural Equipment Rental Management Portal
 * Automatic Database Connection & Setup Wizard
 */

require_once __DIR__ . '/config/database.php';

$message = '';
$messageType = '';
$isConfigured = false;

$host = $_POST['db_host'] ?? DB_HOST;
$user = $_POST['db_user'] ?? DB_USER;
$pass = $_POST['db_pass'] ?? DB_PASS;
$dbname = $_POST['db_name'] ?? DB_NAME;

// Handle 1-Click Connect & Setup Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'connect_db') {
    try {
        // 1. Connect to MySQL server (without selecting DB first)
        $dsn = "mysql:host={$host};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];
        $pdo = new PDO($dsn, $user, $pass, $options);

        // 2. Create database if it does not exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$dbname}`");

        // 3. Import SQL Schema & Seed Data
        $sqlPath = __DIR__ . '/database/agriculture_rental.sql';
        if (file_exists($sqlPath)) {
            $sqlContent = file_get_contents($sqlPath);
            
            // Execute batch SQL statements
            $pdo->exec($sqlContent);

            $message = "<strong>Success!</strong> Database `{$dbname}` was connected, schema created, and all tables & sample data have been seeded successfully.";
            $messageType = 'success';
            $isConfigured = true;
        } else {
            $message = "Database created, but `database/agriculture_rental.sql` file was not found.";
            $messageType = 'warning';
        }

    } catch (PDOException $e) {
        $message = "<strong>Connection Failed:</strong> " . htmlspecialchars($e->getMessage());
        $messageType = 'danger';
    }
} else {
    // Check if database is already connected
    try {
        $testPdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $tableCount = $testPdo->query("SHOW TABLES")->rowCount();
        if ($tableCount >= 5) {
            $message = "<strong>Database is Connected!</strong> Your MySQL database `{$dbname}` has {$tableCount} active tables and is ready to use.";
            $messageType = 'success';
            $isConfigured = true;
        }
    } catch (Exception $e) {
        // Not connected yet
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Assistant - <?= htmlspecialchars(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f4f7f6; color: #1e293b; min-height: 100vh; display: flex; align-items: center; }
        .setup-card { max-width: 650px; margin: 40px auto; border-radius: 20px; border: none; box-shadow: 0 15px 35px rgba(0,0,0,0.06); }
        .brand-icon { width: 50px; height: 50px; background: #1b5e20; color: #fff; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
    </style>
</head>
<body>

<div class="container">
    <div class="card setup-card p-4 p-md-5 bg-white">
        
        <div class="text-center mb-4">
            <div class="brand-icon mx-auto mb-3">
                <i class="fa-solid fa-database"></i>
            </div>
            <h3 class="fw-bold text-dark mb-1">Database Connection Assistant</h3>
            <p class="text-muted small">AgriRental Portal • MySQL Database Installer</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $messageType ?> border-0 rounded-4 p-3 mb-4 shadow-sm">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid <?= $messageType === 'success' ? 'fa-circle-check text-success' : 'fa-triangle-exclamation text-danger' ?> fs-4"></i>
                    <div><?= $message ?></div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($isConfigured): ?>
            <div class="text-center py-3">
                <h5 class="fw-bold text-success mb-3"><i class="fa-solid fa-seedling me-2"></i>Everything is Ready!</h5>
                <p class="text-muted small mb-4">The database contains pre-seeded accounts for Farmers, Equipment Owners, and Admin, along with 10 machinery categories and sample bookings.</p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="index.php" class="btn btn-success px-4 py-3 rounded-pill fw-bold">
                        <i class="fa-solid fa-house me-2"></i>Open Agricultural Portal (index.php)
                    </a>
                    <a href="login.php" class="btn btn-outline-dark px-4 py-3 rounded-pill fw-bold">
                        <i class="fa-solid fa-arrow-right-to-bracket me-2"></i>Login Screen
                    </a>
                </div>
            </div>
        <?php else: ?>
            <form action="setup_database.php" method="POST">
                <input type="hidden" name="action" value="connect_db">

                <div class="mb-3">
                    <label class="form-label small fw-bold">Database Host</label>
                    <input type="text" name="db_host" class="form-control rounded-3" value="<?= htmlspecialchars($host) ?>" required>
                    <div class="form-text small">Default: <code>localhost</code> (or <code>127.0.0.1</code>)</div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Username</label>
                        <input type="text" name="db_user" class="form-control rounded-3" value="<?= htmlspecialchars($user) ?>" required>
                        <div class="form-text small">XAMPP default is <code>root</code></div>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Password</label>
                        <input type="password" name="db_pass" class="form-control rounded-3" placeholder="(Leave blank for default XAMPP)" value="<?= htmlspecialchars($pass) ?>">
                        <div class="form-text small">XAMPP default is blank</div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold">Database Name</label>
                    <input type="text" name="db_name" class="form-control rounded-3" value="<?= htmlspecialchars($dbname) ?>" required>
                    <div class="form-text small">Will be automatically created if it doesn't exist</div>
                </div>

                <button type="submit" class="btn btn-success w-100 py-3 rounded-3 fw-bold fs-6 shadow-sm mb-3">
                    <i class="fa-solid fa-bolt me-2"></i>1-Click Connect & Initialize Database
                </button>
            </form>

            <div class="p-3 bg-light rounded-3 border small text-muted">
                <strong><i class="fa-solid fa-circle-info text-primary me-1"></i>Reminder for XAMPP:</strong>
                Ensure the <strong>MySQL</strong> service is started in your <strong>XAMPP Control Panel</strong> before clicking Connect.
            </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
