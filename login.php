<?php
/**
 * Agricultural Equipment Rental Management Portal
 * Screen 2 — Three-Role Portal Login Interface
 * 1. FARMER / USER LOGIN
 * 2. EQUIPMENT OWNER LOGIN
 * 3. ADMIN LOGIN
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// If already logged in, redirect to respective dashboard
if (isLoggedIn()) {
    $user = getCurrentUser();
    if ($user['role'] === 'admin') header('Location: ' . BASE_URL . '/admin/dashboard.php');
    elseif ($user['role'] === 'owner') header('Location: ' . BASE_URL . '/owner/dashboard.php');
    else header('Location: ' . BASE_URL . '/farmer/dashboard.php');
    exit;
}

$pdo = getDbConnection();
$activeRole = cleanInput($_GET['role'] ?? 'farmer');
if (!in_array($activeRole, ['farmer', 'owner', 'admin'])) {
    $activeRole = 'farmer';
}

$error = '';
$success = '';

// Handle Password Login (For Admin or Users preferring password)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'password_login') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security session. Please refresh and try again.';
    } else {
        $loginInput = cleanInput($_POST['login_input'] ?? '');
        $password = $_POST['password'] ?? '';
        $roleSelected = cleanInput($_POST['intended_role'] ?? 'farmer');

        if (empty($loginInput) || empty($password)) {
            $error = 'Please enter both mobile/email and password.';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM `users` WHERE (`mobile` = :input1 OR `email` = :input2) LIMIT 1");
            $stmt->execute([':input1' => $loginInput, ':input2' => $loginInput]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                if ($user['status'] !== 'active') {
                    $error = 'Your account has been deactivated. Please contact support.';
                } elseif ($roleSelected === 'admin' && $user['role'] !== 'admin') {
                    http_response_code(403);
                    $error = 'Access Denied: Only authorized administrators may log into the Admin Command Center.';
                } else {
                    loginUser($user);
                    setFlash('success', 'Welcome back, ' . $user['name'] . '!');
                    
                    if ($user['role'] === 'admin') header('Location: ' . BASE_URL . '/admin/dashboard.php');
                    elseif ($user['role'] === 'owner') header('Location: ' . BASE_URL . '/owner/dashboard.php');
                    else header('Location: ' . BASE_URL . '/farmer/dashboard.php');
                    exit;
                }
            } else {
                $error = 'Invalid credentials. Please verify your mobile/email and password.';
            }
        }
    }
}

// Handle Mobile Number OTP Request (Farmer & Owner Portals)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_otp') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please refresh the page.';
    } else {
        $mobile = cleanInput($_POST['mobile'] ?? '');
        $preferredRole = cleanInput($_POST['preferred_role'] ?? 'farmer');

        if (empty($mobile)) {
            $error = 'Mobile number is required.';
        } elseif (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
            $error = 'Please enter a valid 10-digit Indian mobile number (e.g. 9823456789).';
        } else {
            // Check Rate-Limiting: Max 5 requests per 15 minutes
            $limitCheck = $pdo->prepare("SELECT COUNT(*) as recent_count FROM `otp_verifications` 
                                         WHERE `mobile` = :mobile AND `created_at` > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
            $limitCheck->execute([':mobile' => $mobile]);
            $recent = $limitCheck->fetch();

            if ((int)$recent['recent_count'] >= 6) {
                $error = 'Too many OTP requests. Please wait 15 minutes before requesting again.';
            } else {
                // Generate 6-digit OTP
                $otpCode = TEST_OTP_MODE ? '123456' : str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
                $otpHash = password_hash($otpCode, PASSWORD_DEFAULT);
                $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

                // Look up existing user
                $userStmt = $pdo->prepare("SELECT `id`, `role`, `name` FROM `users` WHERE `mobile` = :mobile LIMIT 1");
                $userStmt->execute([':mobile' => $mobile]);
                $existingUser = $userStmt->fetch();

                $userId = $existingUser ? $existingUser['id'] : null;

                // Insert OTP
                $insertOtp = $pdo->prepare("INSERT INTO `otp_verifications` (`user_id`, `mobile`, `otp_code`, `otp_hash`, `expires_at`) 
                                            VALUES (:uid, :mobile, :otp, :hash, :expires)");
                $insertOtp->execute([
                    ':uid'     => $userId,
                    ':mobile'  => $mobile,
                    ':otp'     => $otpCode,
                    ':hash'    => $otpHash,
                    ':expires' => $expiresAt
                ]);

                // Store in session for Screen 3
                $_SESSION['auth_mobile'] = $mobile;
                $_SESSION['auth_role']   = $preferredRole;
                $_SESSION['latest_otp']  = $otpCode;

                header('Location: ' . BASE_URL . '/otp.php');
                exit;
            }
        }
    }
}

$pageTitle = 'Login to Agricultural Equipment Rental Portal';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="text-center mb-4">
        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold text-uppercase">
            <i class="fa-solid fa-lock me-1"></i>Secure Authentication Gateway
        </span>
        <h2 class="fw-extrabold text-dark mt-2 mb-1">Select Your Account Type</h2>
        <p class="text-muted small">Choose your role to access your dedicated management portal</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="row justify-content-center mb-4">
            <div class="col-lg-8">
                <div class="alert alert-danger border-0 rounded-4 shadow-sm d-flex align-items-center">
                    <i class="fa-solid fa-circle-exclamation me-3 fs-4"></i>
                    <div><?= e($error) ?></div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- THREE ATTRACTIVE ACCOUNT TYPE CARDS -->
    <div class="row g-4 justify-content-center mb-5">
        <!-- 1. FARMER / USER CARD -->
        <div class="col-md-4">
            <div class="card card-hover border-2 h-100 <?= $activeRole === 'farmer' ? 'border-success shadow-lg' : 'border-light-subtle shadow-sm' ?> rounded-4 p-4 text-center cursor-pointer transition-all"
                 onclick="window.location.href='<?= BASE_URL ?>/login.php?role=farmer'">
                <div class="mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center"
                     style="width: 70px; height: 70px; background: #e8f5e9; color: #2e7d32;">
                    <i class="fa-solid fa-seedling fs-2"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Farmer / User</h5>
                <p class="text-muted small mb-3">Browse machinery, check availability, book equipment, pay online & download receipts.</p>
                <span class="btn btn-sm <?= $activeRole === 'farmer' ? 'btn-success text-white' : 'btn-outline-success' ?> rounded-pill px-4 fw-semibold">
                    <?= $activeRole === 'farmer' ? 'Selected' : 'Login as Farmer' ?>
                </span>
            </div>
        </div>

        <!-- 2. EQUIPMENT OWNER CARD -->
        <div class="col-md-4">
            <div class="card card-hover border-2 h-100 <?= $activeRole === 'owner' ? 'border-warning shadow-lg' : 'border-light-subtle shadow-sm' ?> rounded-4 p-4 text-center cursor-pointer transition-all"
                 onclick="window.location.href='<?= BASE_URL ?>/login.php?role=owner'">
                <div class="mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center"
                     style="width: 70px; height: 70px; background: #fff8e1; color: #f57f17;">
                    <i class="fa-solid fa-tractor fs-2"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Equipment Owner</h5>
                <p class="text-muted small mb-3">Register machinery, manage fleet availability, approve rental requests & track earnings.</p>
                <span class="btn btn-sm <?= $activeRole === 'owner' ? 'btn-warning text-dark' : 'btn-outline-warning' ?> rounded-pill px-4 fw-semibold">
                    <?= $activeRole === 'owner' ? 'Selected' : 'Login as Owner' ?>
                </span>
            </div>
        </div>

        <!-- 3. ADMIN GATEWAY CARD -->
        <div class="col-md-4">
            <div class="card card-hover border-2 h-100 <?= $activeRole === 'admin' ? 'border-danger shadow-lg' : 'border-light-subtle shadow-sm' ?> rounded-4 p-4 text-center cursor-pointer transition-all"
                 onclick="window.location.href='<?= BASE_URL ?>/login.php?role=admin'">
                <div class="mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center"
                     style="width: 70px; height: 70px; background: #ffebee; color: #c62828;">
                    <i class="fa-solid fa-shield-halved fs-2"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Administrator</h5>
                <p class="text-muted small mb-3">Moderation command center, machinery verification, dispute monitoring & platform analytics.</p>
                <span class="btn btn-sm <?= $activeRole === 'admin' ? 'btn-danger text-white' : 'btn-outline-danger' ?> rounded-pill px-4 fw-semibold">
                    <?= $activeRole === 'admin' ? 'Selected' : 'Admin Gateway' ?>
                </span>
            </div>
        </div>
    </div>

    <!-- DEDICATED LOGIN FORM CONTAINER -->
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
                
                <?php if ($activeRole === 'farmer'): ?>
                    <!-- FARMER LOGIN FORM -->
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="p-2 bg-success text-white rounded-3 fs-5"><i class="fa-solid fa-seedling"></i></div>
                        <div>
                            <h4 class="fw-bold mb-0">Farmer Portal Login</h4>
                            <small class="text-muted">Instant login with your mobile number via OTP</small>
                        </div>
                    </div>

                    <form method="POST" action="<?= BASE_URL ?>/login.php?role=farmer" class="mb-4">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="send_otp">
                        <input type="hidden" name="preferred_role" value="farmer">

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Mobile Number <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-end-0 fw-semibold text-muted">+91</span>
                                <input type="tel" name="mobile" class="form-control border-start-0" placeholder="e.g. 9894012345" 
                                       value="<?= e($_POST['mobile'] ?? '9894012345') ?>" maxlength="10" required autofocus>
                            </div>
                            <div class="form-text">We'll send a 6-digit OTP code to verify your mobile number.</div>
                        </div>

                        <button type="submit" class="btn btn-agri-primary w-100 py-3 rounded-pill fw-bold">
                            <i class="fa-solid fa-paper-plane me-2"></i>Send Verification OTP
                        </button>
                    </form>

                    <!-- Or Password Toggle -->
                    <div class="text-center border-top pt-3">
                        <button class="btn btn-link text-success text-decoration-none fw-semibold p-0" type="button" data-bs-toggle="collapse" data-bs-target="#farmerPasswordBox">
                            <i class="fa-solid fa-key me-1"></i>Or login with password
                        </button>
                        <div class="collapse mt-3" id="farmerPasswordBox">
                            <form method="POST" action="<?= BASE_URL ?>/login.php?role=farmer">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="password_login">
                                <input type="hidden" name="intended_role" value="farmer">
                                <div class="mb-2">
                                    <input type="text" name="login_input" class="form-control" placeholder="Mobile or Email" value="9894012345" required>
                                </div>
                                <div class="mb-3">
                                    <input type="password" name="password" class="form-control" placeholder="Password (default: admin123)" value="admin123" required>
                                </div>
                                <button type="submit" class="btn btn-outline-success w-100 rounded-pill fw-semibold">Login with Password</button>
                            </form>
                        </div>
                    </div>

                    <!-- Preset Demo Credentials -->
                    <div class="mt-4 p-3 bg-light rounded-3 small">
                        <span class="fw-bold text-dark d-block mb-1"><i class="fa-solid fa-lightbulb text-warning me-1"></i>Farmer Demo Account:</span>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Mobile: <strong>9894012345</strong> (Gopi Krishnan)</span>
                            <span class="badge bg-success-subtle text-success">Demo OTP: 123456</span>
                        </div>
                    </div>

                <?php elseif ($activeRole === 'owner'): ?>
                    <!-- OWNER LOGIN FORM -->
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="p-2 bg-warning text-dark rounded-3 fs-5"><i class="fa-solid fa-tractor"></i></div>
                        <div>
                            <h4 class="fw-bold mb-0">Equipment Owner Login</h4>
                            <small class="text-muted">Manage machinery fleet and rental bookings</small>
                        </div>
                    </div>

                    <form method="POST" action="<?= BASE_URL ?>/login.php?role=owner" class="mb-4">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="send_otp">
                        <input type="hidden" name="preferred_role" value="owner">

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Mobile Number <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-end-0 fw-semibold text-muted">+91</span>
                                <input type="tel" name="mobile" class="form-control border-start-0" placeholder="e.g. 9812345678" 
                                       value="<?= e($_POST['mobile'] ?? '9812345678') ?>" maxlength="10" required autofocus>
                            </div>
                            <div class="form-text">Registered owner mobile number verification via OTP.</div>
                        </div>

                        <button type="submit" class="btn btn-warning w-100 py-3 rounded-pill fw-bold text-dark">
                            <i class="fa-solid fa-paper-plane me-2"></i>Send Owner Verification OTP
                        </button>
                    </form>

                    <div class="text-center border-top pt-3">
                        <button class="btn btn-link text-warning text-decoration-none fw-semibold p-0 text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#ownerPasswordBox">
                            <i class="fa-solid fa-key me-1"></i>Or login with password
                        </button>
                        <div class="collapse mt-3" id="ownerPasswordBox">
                            <form method="POST" action="<?= BASE_URL ?>/login.php?role=owner">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="password_login">
                                <input type="hidden" name="intended_role" value="owner">
                                <div class="mb-2">
                                    <input type="text" name="login_input" class="form-control" placeholder="Mobile or Email" value="9812345678" required>
                                </div>
                                <div class="mb-3">
                                    <input type="password" name="password" class="form-control" placeholder="Password (default: admin123)" value="admin123" required>
                                </div>
                                <button type="submit" class="btn btn-outline-dark w-100 rounded-pill fw-semibold">Login with Password</button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-light rounded-3 small">
                        <span class="fw-bold text-dark d-block mb-1"><i class="fa-solid fa-lightbulb text-warning me-1"></i>Owner Demo Account:</span>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Mobile: <strong>9812345678</strong> (Balwinder Singh)</span>
                            <span class="badge bg-warning-subtle text-dark">Demo OTP: 123456</span>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- ADMIN LOGIN FORM -->
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="p-2 bg-danger text-white rounded-3 fs-5"><i class="fa-solid fa-shield-halved"></i></div>
                        <div>
                            <h4 class="fw-bold mb-0">System Administrator Gateway</h4>
                            <small class="text-muted">Restricted access for platform administrators</small>
                        </div>
                    </div>

                    <form method="POST" action="<?= BASE_URL ?>/login.php?role=admin" class="mb-4">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="password_login">
                        <input type="hidden" name="intended_role" value="admin">

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Admin Email or Username <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-user-shield text-danger"></i></span>
                                <input type="text" name="login_input" class="form-control" placeholder="admin@agrirental.com" 
                                       value="<?= e($_POST['login_input'] ?? 'admin@agrirental.com') ?>" required autofocus>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Admin Password <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-lock text-danger"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="••••••••" 
                                       value="admin123" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">Security PIN / 2FA <span class="text-muted fw-normal">(Optional for Demo)</span></label>
                            <input type="text" name="two_factor_code" class="form-control form-control-lg" placeholder="e.g. 123456" maxlength="6">
                        </div>

                        <button type="submit" class="btn btn-danger w-100 py-3 rounded-pill fw-bold shadow-sm">
                            <i class="fa-solid fa-right-to-bracket me-2"></i>Authenticate Admin Access
                        </button>
                    </form>

                    <div class="p-3 bg-danger-subtle rounded-3 small text-danger-emphasis">
                        <div class="fw-bold mb-1"><i class="fa-solid fa-lock me-1"></i>Admin Credentials:</div>
                        <div>Email: <strong>admin@agrirental.com</strong> | Password: <strong>admin123</strong></div>
                    </div>

                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
