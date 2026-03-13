<?php
require 'db_connect.php';
require './lib/auth.php';

if (is_logged_in()) {
    header('Location: sku-management.php');
    exit;
}
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (login_user($email, $password)) {
        header('Location: sku-management.php');
        exit;
    } else {
        $error = 'Invalid email or password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JBC Manufacturing CMS - Login</title>
    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="./css/normalize.css">
    <link rel="stylesheet" href="./css/login.css">
</head>
<body>
    <!-- header -->
    <div class="header-bar">
        <h2>JBC Manufacturing CMS</h2>
    </div>
    
    <div class="login-container">
    <div class="login-wrapper">
        <form action="login.php" method="POST" class="login-form">
            <h2 class="form-title">Log In</h2>

            <?php if ($error): ?>
                <div class="error-message" 
                    style="background-color: #ffebee; color: #c62828; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="form-fields">
            <div class="form-group">
                <label for="username" class="form-label">Email:</label>
                <input 
                    type="email" 
                    id="username" 
                    name="username" 
                    class="form-input" 
                    placeholder="Enter your email"
                    value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                    required>
            </div>

            <div class="form-group">
            <label for="password" class="form-label">Password:</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                class="form-input" 
                placeholder="Enter your password"
                required>
            </div>
            </div>
    <button type="submit" class="submit-button">Sign In</button>
</form>
</div>
</div>
</body>
</html>