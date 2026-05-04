<?php
http_response_code(403);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>403 Forbidden - Green Delta</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body class="login-body">
    <div class="card login-card" style="text-align: center;">
        <h1 style="color: var(--danger); font-size: 4rem; margin-bottom: 1rem;">403</h1>
        <h2 style="color: var(--primary);">Access Denied</h2>
        <p style="margin: 1.5rem 0; color: #666;">You do not have the required permissions to view this page.</p>
        <a href="/login.php" class="btn-primary" style="text-decoration:none; display:inline-block;">Return to Login</a>
    </div>
</body>
</html>