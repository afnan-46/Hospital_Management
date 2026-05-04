<?php
// "Page Not Found" error page
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - 404</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">
    <div class="card login-card" style="text-align: center;">
        <h1 style="color: var(--danger); font-size: 4rem; margin-bottom: 1rem;">404</h1>
        <h2 style="color: var(--primary);">Page Not Found</h2>
        <p style="margin: 1.5rem 0; color: #666;">The page you are looking for does not exist or has been moved.</p>
        <a href="login.php" class="btn-primary" style="text-decoration:none; display:inline-block;">Return to Login</a>
    </div>
</body>
</html>
