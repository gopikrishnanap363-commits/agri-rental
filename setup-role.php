<?php
/**
 * Agricultural Equipment Rental Management Portal
 * Role Selection, Profile Setup & Identity Verification for New Users
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/IdentityVerificationService.php';

$mobile = $_SESSION['auth_mobile'] ?? '';
$preferredRole = $_SESSION['auth_role'] ?? 'farmer';

if (empty($mobile)) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$pdo = getDbConnection();
$service = new IdentityVerificationService($pdo);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please retry.';
    } else {
        $name = cleanInput($_POST['name'] ?? '');
        $email = cleanInput($_POST['email'] ?? '');
        $role = cleanInput($_POST['role'] ?? 'farmer');
        $city = cleanInput($_POST['city'] ?? '');
        $state = cleanInput($_POST['state'] ?? '');
        $address = cleanInput($_POST['address'] ?? '');
        
        // Identity Verification parameters
        $aadhaarLast4 = preg_replace('/\D/', '', cleanInput($_POST['aadhaar_last4'] ?? ''));
        $dob = cleanInput($_POST['dob'] ?? null);

        if (!in_array($role, ['farmer', 'owner'])) {
            $role = 'farmer';
        }

        if (empty($name)) {
            $error = 'Please enter your Full Legal Name.';
        } elseif (empty($city) || empty($state)) {
            $error = 'Please provide your City/District and State.';
        } elseif (!empty($aadhaarLast4) && strlen($aadhaarLast4) !== 4) {
            $error = 'Please provide exactly the last 4 digits of your Aadhaar card.';
        } else {
            // Check if mobile already registered
            $check = $pdo->prepare("SELECT id FROM `users` WHERE `mobile` = :m");
            $check->execute([':m' => $mobile]);
            if ($check->fetch()) {
                $error = 'This mobile number is already registered. Please login.';
            } else {
                try {
                    $pdo->beginTransaction();

                    // Insert new user
                    $stmt = $pdo->prepare("INSERT INTO `users` 
                        (`name`, `mobile`, `email`, `role`, `status`, `city`, `state`, `address`, `is_verified`, `kyc_status`, `aadhaar_last4`, `verified_name`) 
                        VALUES (:name, :mobile, :email, :role, 'active', :city, :state, :address, 0, 'pending', :last4, :vname)");
                    
                    $stmt->execute([
                        ':name'    => $name,
                        ':mobile'  => $mobile,
                        ':email'   => !empty($email) ? $email : null,
                        ':role'    => $role,
                        ':city'    => $city,
                        ':state'   => $state,
                        ':address' => $address,
                        ':last4'   => !empty($aadhaarLast4) ? $aadhaarLast4 : null,
                        ':vname'   => !empty($aadhaarLast4) ? $name : null
                    ]);

                    $newUserId = (int)$pdo->lastInsertId();

                    // Create specific profile
                    if ($role === 'farmer') {
                        $fStmt = $pdo->prepare("INSERT INTO `farmer_profiles` (`user_id`, `farm_size_acres`, `soil_type`, `primary_crops`) 
                                               VALUES (:uid, 5.0, 'Alluvial Loamy', 'Paddy, Wheat')");
                        $fStmt->execute([':uid' => $newUserId]);
                    } else {
                        $oStmt = $pdo->prepare("INSERT INTO `owner_profiles` (`user_id`, `business_name`, `total_machinery_count`) 
                                               VALUES (:uid, :bname, 0)");
                        $oStmt->execute([':uid' => $newUserId, ':bname' => $name . ' Farm Rentals']);
                    }

                    // Perform Identity Verification if Aadhaar last 4 provided
                    if (!empty($aadhaarLast4)) {
                        $vRes = $service->verifyDemo($newUserId, $aadhaarLast4, $name);
                    }

                    // Welcome Notification
                    addNotification($pdo, $newUserId, 'Welcome to AgriRental Portal!', 'Your registration was completed successfully. You can now access equipment listings and rental bookings.', 'success');

                    // Audit Log
                    $auditStmt = $pdo->prepare("INSERT INTO `audit_logs` (`user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`) 
                                               VALUES (:uid, 'USER_REGISTERED', 'USERS', :uid, :details, :ip)");
                    $auditStmt->execute([
                        ':uid'     => $newUserId,
                        ':details' => "Registered as {$role} with mobile {$mobile}",
                        ':ip'      => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
                    ]);

                    $pdo->commit();

                    // Fetch newly created user and log in
                    $fetchStmt = $pdo->prepare("SELECT * FROM `users` WHERE `id` = :id");
                    $fetchStmt->execute([':id' => $newUserId]);
                    $newUser = $fetchStmt->fetch();

                    loginUser($newUser);
                    unset($_SESSION['auth_mobile'], $_SESSION['auth_role'], $_SESSION['latest_otp']);

                    setFlash('success', 'Welcome to AgriRental, ' . $name . '! Your profile has been created.');
                    if ($role === 'owner') {
                        header('Location: ' . BASE_URL . '/owner/dashboard.php');
                    } else {
                        header('Location: ' . BASE_URL . '/farmer/dashboard.php');
                    }
                    exit;

                } catch (Exception $e) {
                    $pdo->rollBack();
                    error_log("Registration error: " . $e->getMessage());
                    $error = 'Registration could not be completed: ' . $e->getMessage();
                }
            }
        }
    }
}

$pageTitle = 'Complete Profile & Identity Verification - ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <div class="card card-agri p-4 p-md-5 border-0 shadow-lg bg-white rounded-4">
                <div class="text-center mb-4">
                    <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill fw-bold">Step 3 of 3 • Profile Setup</span>
                    <h3 class="fw-bold mt-2 mb-1">Complete Your Agricultural Profile</h3>
                    <p class="text-muted small">Verified Mobile: <span class="fw-bold text-dark">+91 <?= e($mobile) ?></span></p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger border-0 rounded-3 shadow-sm d-flex align-items-center mb-4">
                        <i class="fa-solid fa-triangle-exclamation me-2 fs-5"></i>
                        <div><?= e($error) ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>/setup-role.php">
                    <?= csrfField() ?>

                    <!-- Role Selection -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark mb-2">Select Your Primary Account Role <span class="text-danger">*</span></label>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="card h-100 p-3 border-2 rounded-3 text-center cursor-pointer role-option" id="role-farmer-card">
                                    <input type="radio" name="role" value="farmer" class="d-none" <?= $preferredRole === 'farmer' ? 'checked' : '' ?> onchange="updateRoleSelection()">
                                    <div class="display-6 text-success mb-2"><i class="fa-solid fa-seedling"></i></div>
                                    <h6 class="fw-bold mb-1">Farmer</h6>
                                    <small class="text-muted">I want to rent equipment for my farm</small>
                                </label>
                            </div>
                            <div class="col-6">
                                <label class="card h-100 p-3 border-2 rounded-3 text-center cursor-pointer role-option" id="role-owner-card">
                                    <input type="radio" name="role" value="owner" class="d-none" <?= $preferredRole === 'owner' ? 'checked' : '' ?> onchange="updateRoleSelection()">
                                    <div class="display-6 text-warning mb-2"><i class="fa-solid fa-tractor"></i></div>
                                    <h6 class="fw-bold mb-1">Equipment Owner</h6>
                                    <small class="text-muted">I own machinery and want to rent it out</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Information -->
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">1. Personal & Contact Details</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Full Legal Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Gopi Krishnan" value="<?= e($_POST['name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email Address <span class="text-muted">(Optional)</span></label>
                            <input type="email" name="email" class="form-control" placeholder="gopi@example.com" value="<?= e($_POST['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">City / District <span class="text-danger">*</span></label>
                            <input type="text" name="city" class="form-control" placeholder="e.g. Tiruchirappalli" value="<?= e($_POST['city'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">State <span class="text-danger">*</span></label>
                            <input type="text" name="state" class="form-control" placeholder="e.g. Tamil Nadu" value="<?= e($_POST['state'] ?? '') ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Village / Street Address</label>
                            <input type="text" name="address" class="form-control" placeholder="e.g. Kallakudi Village, Lalgudi Post" value="<?= e($_POST['address'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Section: Identity / KYC Verification -->
                    <div class="p-3 bg-light rounded-4 mb-4 border">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="fw-bold text-dark mb-0">
                                <i class="fa-solid fa-id-card text-success me-2"></i>2. Identity Verification (Aadhaar KYC)
                            </h6>
                            <span class="badge bg-warning-subtle text-dark border">Demo Verification Mode</span>
                        </div>
                        <p class="text-muted small mb-3">
                            For security and mutual trust on the portal, provide the last 4 digits of your Aadhaar card. <em>(Full Aadhaar numbers are never collected or stored).</em>
                        </p>
                        <div class="row g-3 align-items-center">
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold">Aadhaar (Last 4 Digits)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-muted">XXXX-XXXX-</span>
                                    <input type="text" name="aadhaar_last4" class="form-control" placeholder="1234" maxlength="4" pattern="\d{4}" value="<?= e($_POST['aadhaar_last4'] ?? '3024') ?>">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold">Date of Birth</label>
                                <input type="date" name="dob" class="form-control" value="<?= e($_POST['dob'] ?? '1988-12-05') ?>">
                            </div>
                        </div>
                        <div class="form-text mt-2 text-success">
                            <i class="fa-solid fa-circle-check me-1"></i>Enables verified badge: <strong>✓ Identity Verified</strong> upon registration.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-agri-primary w-100 py-3 rounded-pill fw-bold">
                        <i class="fa-solid fa-check-circle me-2"></i>Complete Setup & Enter Portal
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function updateRoleSelection() {
    const isFarmer = document.querySelector('input[name="role"][value="farmer"]').checked;
    const farmerCard = document.getElementById('role-farmer-card');
    const ownerCard = document.getElementById('role-owner-card');
    
    if (isFarmer) {
        farmerCard.classList.add('border-success', 'shadow-sm');
        farmerCard.classList.remove('border-light-subtle');
        ownerCard.classList.remove('border-warning', 'shadow-sm');
        ownerCard.classList.add('border-light-subtle');
    } else {
        ownerCard.classList.add('border-warning', 'shadow-sm');
        ownerCard.classList.remove('border-light-subtle');
        farmerCard.classList.remove('border-success', 'shadow-sm');
        farmerCard.classList.add('border-light-subtle');
    }
}
document.addEventListener('DOMContentLoaded', updateRoleSelection);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
