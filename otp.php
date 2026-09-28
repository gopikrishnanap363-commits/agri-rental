<?php
/**
 * Agricultural Equipment Rental Management Portal
 * Screen 3 — OTP Verification Screen
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$mobile = $_SESSION['auth_mobile'] ?? '';
$intendedRole = $_SESSION['auth_role'] ?? 'farmer';

// If no mobile in session, send back to login screen 2
if (empty($mobile)) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$pdo = getDbConnection();
$error = '';
$success = '';

// Handle Resend OTP Action
if (isset($_GET['action']) && $_GET['action'] === 'resend') {
    $newOtp = TEST_OTP_MODE ? '123456' : str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    $newHash = password_hash($newOtp, PASSWORD_DEFAULT);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    $stmt = $pdo->prepare("INSERT INTO `otp_verifications` (`mobile`, `otp_code`, `otp_hash`, `expires_at`) VALUES (:m, :code, :h, :exp)");
    $stmt->execute([':m' => $mobile, ':code' => $newOtp, ':h' => $newHash, ':exp' => $expiresAt]);

    $_SESSION['latest_otp'] = $newOtp;
    $success = 'A fresh 6-digit OTP has been sent to +91 ' . substr($mobile, 0, 2) . '******' . substr($mobile, -2);
}

// Handle OTP Verification Form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please retry.';
    } else {
        // Collect 6 digits
        $enteredOtp = '';
        if (!empty($_POST['full_otp_code'])) {
            $enteredOtp = trim($_POST['full_otp_code']);
        } elseif (isset($_POST['otp']) && is_array($_POST['otp'])) {
            $enteredOtp = implode('', $_POST['otp']);
        }

        if (strlen($enteredOtp) !== 6 || !ctype_digit($enteredOtp)) {
            $error = 'Please enter a complete 6-digit OTP code.';
        } else {
            // Fetch most recent valid OTP for this mobile
            $stmt = $pdo->prepare("SELECT * FROM `otp_verifications` 
                                   WHERE `mobile` = :mobile 
                                   AND `verified_at` IS NULL 
                                   ORDER BY `created_at` DESC LIMIT 1");
            $stmt->execute([':mobile' => $mobile]);
            $otpRecord = $stmt->fetch();

            if (!$otpRecord) {
                $error = 'No active OTP request found. Please click Resend OTP.';
            } elseif (strtotime($otpRecord['expires_at']) < time()) {
                $error = 'OTP expired. Please request a new OTP.';
            } elseif ((int)$otpRecord['attempts'] >= 5) {
                $error = 'Too many incorrect attempts. Please request a new OTP.';
            } else {
                // Verify OTP (Check both hash and dev code)
                $isValid = password_verify($enteredOtp, $otpRecord['otp_hash']) || ($enteredOtp === $otpRecord['otp_code']) || ($enteredOtp === DEMO_OTP_CODE);

                if (!$isValid) {
                    // Increment attempt count
                    $pdo->prepare("UPDATE `otp_verifications` SET `attempts` = `attempts` + 1 WHERE `id` = :id")
                        ->execute([':id' => $otpRecord['id']]);
                    $error = 'Invalid OTP. Please check the code and try again.';
                } else {
                    // Mark OTP as verified
                    $pdo->prepare("UPDATE `otp_verifications` SET `verified_at` = NOW() WHERE `id` = :id")
                        ->execute([':id' => $otpRecord['id']]);

                    // Check if user exists in database
                    $uStmt = $pdo->prepare("SELECT * FROM `users` WHERE `mobile` = :mobile LIMIT 1");
                    $uStmt->execute([':mobile' => $mobile]);
                    $user = $uStmt->fetch();

                    if ($user) {
                        // User exists, log them in
                        loginUser($user);
                        unset($_SESSION['auth_mobile'], $_SESSION['auth_role'], $_SESSION['latest_otp']);
                        setFlash('success', 'Logged in successfully! Welcome, ' . $user['name'] . '.');

                        if ($user['role'] === 'admin') header('Location: ' . BASE_URL . '/admin/dashboard.php');
                        elseif ($user['role'] === 'owner') header('Location: ' . BASE_URL . '/owner/dashboard.php');
                        else header('Location: ' . BASE_URL . '/farmer/dashboard.php');
                        exit;
                    } else {
                        // Brand new user -> proceed to Profile Setup / Role Selection
                        header('Location: ' . BASE_URL . '/setup-role.php');
                        exit;
                    }
                }
            }
        }
    }
}

// Display masked mobile number
$maskedMobile = '+91 ' . substr($mobile, 0, 2) . ' ' . str_repeat('*', 4) . ' ' . substr($mobile, -4);
$testCode = $_SESSION['latest_otp'] ?? '123456';

$pageTitle = 'Screen 3 - OTP Verification - ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 col-xl-5">
            <div class="card card-agri border-0 shadow-lg p-4 p-md-5 bg-white text-center">
                
                <!-- Icon & Title -->
                <div class="brand-icon mx-auto mb-3" style="width: 60px; height: 60px; font-size: 1.6rem;">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill fw-bold mb-2">Step 2 of 2</span>
                <h3 class="fw-bold text-dark mb-1">Verify Mobile Number</h3>
                <p class="text-muted small mb-3">
                    OTP sent to <span class="fw-bold text-dark"><?= e($maskedMobile) ?></span>
                </p>

                <!-- Dev Test OTP Callout Banner -->
                <?php if (TEST_OTP_MODE): ?>
                    <div class="alert alert-warning border-0 rounded-3 text-start small mb-4 py-2 px-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <i class="fa-solid fa-code me-1 text-warning"></i>
                                <strong>Test OTP Code:</strong> <span class="badge bg-dark text-warning fs-6 px-2 py-1"><?= e($testCode) ?></span>
                            </div>
                            <button type="button" class="btn btn-sm btn-dark rounded-pill px-3" onclick="autoFillOtp('<?= e($testCode) ?>')">
                                Auto-Fill
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger border-0 rounded-3 text-start small mb-4">
                        <i class="fa-solid fa-circle-exclamation me-1"></i><?= e($error) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success border-0 rounded-3 text-start small mb-4">
                        <i class="fa-solid fa-circle-check me-1"></i><?= e($success) ?>
                    </div>
                <?php endif; ?>

                <!-- Screen 3: OTP Input Form -->
                <form action="<?= BASE_URL ?>/otp.php" method="POST" id="otpForm">
                    <?= csrfField() ?>
                    <input type="hidden" name="full_otp_code" id="full_otp_code" value="">

                    <div class="otp-container">
                        <input type="text" maxlength="1" class="otp-box" pattern="[0-9]" inputmode="numeric" required autofocus>
                        <input type="text" maxlength="1" class="otp-box" pattern="[0-9]" inputmode="numeric" required>
                        <input type="text" maxlength="1" class="otp-box" pattern="[0-9]" inputmode="numeric" required>
                        <input type="text" maxlength="1" class="otp-box" pattern="[0-9]" inputmode="numeric" required>
                        <input type="text" maxlength="1" class="otp-box" pattern="[0-9]" inputmode="numeric" required>
                        <input type="text" maxlength="1" class="otp-box" pattern="[0-9]" inputmode="numeric" required>
                    </div>

                    <!-- Countdown Timer -->
                    <div class="text-muted small mb-4">
                        <i class="fa-regular fa-clock me-1"></i>Expires in: <span id="timer" class="fw-bold text-dark">02:00</span>
                    </div>

                    <button type="submit" class="btn btn-agri-primary w-100 py-3 rounded-3 fw-bold fs-6 mb-3">
                        <i class="fa-solid fa-check-circle me-2"></i>Verify OTP
                    </button>
                </form>

                <!-- Secondary Actions -->
                <div class="d-flex justify-content-between align-items-center pt-3 border-top small">
                    <a href="<?= BASE_URL ?>/otp.php?action=resend" class="text-decoration-none fw-semibold text-success">
                        <i class="fa-solid fa-rotate-right me-1"></i>Resend OTP
                    </a>
                    <a href="<?= BASE_URL ?>/login.php" class="text-decoration-none text-muted">
                        <i class="fa-solid fa-pen-to-square me-1"></i>Change Mobile Number
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
// Auto-fill OTP helper for quick testing
function autoFillOtp(code) {
    const boxes = document.querySelectorAll('.otp-box');
    code.split('').forEach((digit, i) => {
        if (boxes[i]) boxes[i].value = digit;
    });
    document.getElementById('full_otp_code').value = code;
    boxes[boxes.length - 1].focus();
}

// 2-minute countdown timer
let timeLeft = 120;
const timerDisplay = document.getElementById('timer');
const countdown = setInterval(function () {
    timeLeft--;
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    if (timerDisplay) {
        timerDisplay.textContent = `${minutes < 10 ? '0' : ''}${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
    }
    if (timeLeft <= 0) {
        clearInterval(countdown);
        if (timerDisplay) timerDisplay.innerHTML = '<span class="text-danger fw-bold">Expired</span>';
    }
}, 1000);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
