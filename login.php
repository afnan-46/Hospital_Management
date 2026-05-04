<?php
// login.php
require_once 'db.php';
require_once 'auth.php';

if (isset($_SESSION['user_id'])) {
    header("Location: /{$_SESSION['role']}/dashboard.php");
    exit;
}

// Rate Limiting
if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
if (!isset($_SESSION['last_attempt'])) $_SESSION['last_attempt'] = time();
if (time() - $_SESSION['last_attempt'] > 900) $_SESSION['login_attempts'] = 0;

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    
    if ($_SESSION['login_attempts'] >= 5) {
        $error = "Too many failed attempts. Please try again in 15 minutes.";
    } else {
        $identifier = trim($_POST['identifier']);
        $password = $_POST['password'];
        $type = $_POST['login_type']; // 'staff' or 'patient'

        // Check user
        $stmt = $pdo->prepare("SELECT u.*, p.patient_id FROM users u LEFT JOIN patients p ON u.id = p.user_id WHERE u.username = :id OR u.email = :id OR p.patient_id = :id LIMIT 1");
        $stmt->execute(['id' => $identifier]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password']) && $user['is_active']) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            if ($user['patient_id']) $_SESSION['patient_id'] = $user['patient_id'];
            
            $_SESSION['login_attempts'] = 0;
            header("Location: /" . $user['role'] . "/dashboard.php");
            exit;
        } else {
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt'] = time();
            $error = "Invalid credentials or inactive account.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Green Delta Hospital</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">
    <div class="card login-card">
        <h2 style="text-align: center; color: var(--primary);">+ Green Delta Hospital</h2>
        <p style="text-align: center; margin-bottom: 1.5rem; color: #666;">Advanced Healthcare Services</p>
        
        <?php if($error): ?><div class="badge cancelled" style="display:block; padding:1rem; margin-bottom:1rem;"><?= e($error) ?></div><?php endif; ?>

        <div class="tabs">
            <div class="tab active" onclick="switchTab('staff')">Staff Login</div>
            <div class="tab" onclick="switchTab('patient')">Patient Login</div>
        </div>

        <form method="POST" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="login_type" id="loginType" value="staff">

            <div class="form-group">
                <input type="text" name="identifier" id="identifier" class="form-control" placeholder=" " required>
                <label class="form-label" id="idLabel">Username or Email</label>
            </div>
            
            <div class="form-group">
                <input type="password" name="password" class="form-control" placeholder=" " required>
                <label class="form-label">Password</label>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%;">Secure Login</button>
        </form>

        <div style="margin-top: 1.5rem; text-align: center; font-size: 0.9rem;">
            <p id="registerLink" style="display:none;">New Patient? <a href="register.php" style="color: var(--primary);">Register here</a></p>
            <details style="margin-top:1rem; cursor:pointer; color:#777;">
                <summary>Demo Credentials</summary>
                <div style="background:#f1f1f1; padding:10px; margin-top:5px; border-radius:4px; font-size:0.8rem; text-align:left;">
                    <strong>Admin:</strong> admin / 1234<br>
                    <strong>Doctor:</strong> doctor1 / 1234<br>
                    <strong>Pharmacy:</strong> medicine / 1234<br>
                    <strong>Patient:</strong> GDH-00001 / 1234
                </div>
            </details>
        </div>
    </div>

    <script>
        function switchTab(type) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            event.target.classList.add('active');
            document.getElementById('loginType').value = type;
            document.getElementById('idLabel').innerText = type === 'staff' ? 'Username or Email' : 'Patient ID or Email';
            document.getElementById('registerLink').style.display = type === 'patient' ? 'block' : 'none';
        }
    </script>
</body>
</html>