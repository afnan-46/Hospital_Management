<?php
// register.php
require_once 'db.php';
require_once 'auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $pdo->beginTransaction();
        
        // Check email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) throw new Exception("Email already registered.");

        // Insert User
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, full_name) VALUES (?, ?, ?, 'patient', ?)");
        $username = explode('@', $email)[0] . rand(100,999);
        $stmt->execute([$username, $email, $password, $name]);
        $user_id = $pdo->lastInsertId();

        // Generate Patient ID (GDH-XXXXX)
        $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(patient_id, 5) AS UNSIGNED)) AS max_id FROM patients");
        $row = $stmt->fetch();
        $next_id = "GDH-" . str_pad(($row['max_id'] ? $row['max_id'] + 1 : 1), 5, "0", STR_PAD_LEFT);

        // Insert Patient
        $stmt = $pdo->prepare("INSERT INTO patients (patient_id, user_id, phone) VALUES (?, ?, ?)");
        $stmt->execute([$next_id, $user_id, $phone]);

        $pdo->commit();
        $success = "Registration successful! Your Patient ID is <strong>$next_id</strong>. You can now login.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Green Delta Hospital</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">
    <div class="card login-card">
        <h2 style="text-align: center; color: var(--primary);">Patient Registration</h2>
        <p style="text-align: center; margin-bottom: 1.5rem; color: #666;">Create your patient account to book appointments and access health records.</p>

        <?php if ($error): ?><div class="badge cancelled" style="display:block; padding:1rem; margin-bottom:1rem;"><?= e($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="badge completed" style="display:block; padding:1rem; margin-bottom:1rem;"><?= $success ?></div><?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="form-group">
                <input type="text" name="full_name" class="form-control" placeholder=" " required>
                <label class="form-label">Full Name</label>
            </div>

            <div class="form-group">
                <input type="email" name="email" class="form-control" placeholder=" " required>
                <label class="form-label">Email Address</label>
            </div>

            <div class="form-group">
                <input type="text" name="phone" class="form-control" placeholder=" " required>
                <label class="form-label">Phone Number</label>
            </div>

            <div class="form-group">
                <input type="password" name="password" class="form-control" placeholder=" " required>
                <label class="form-label">Password</label>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%;">Register Patient</button>
        </form>

        <p style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: #777;">
            Already registered? <a href="login.php" style="color: var(--primary);">Login here</a>
        </p>
    </div>
</body>
</html>