<?php
if (!defined('API_REQUEST') && session_status() === PHP_SESSION_NONE) {
    session_start();
}

function check_api_key(array $env) {
    $valid_key = $env['X-API-KEY'];
    $provided_key = null;
    $headers = getallheaders();

    foreach ($headers as $name => $value) {
        if (strtolower($name) === 'x-api-key') {
            $provided_key = $value;
            break;
        }
    }

    error_log("Expected key: " . $valid_key);
    error_log("Provided key: " . $provided_key);

    if ($provided_key !== $valid_key) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized: Invalid API Key']);
        exit;
    }
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_email']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function login_user($email, $password) {
    global $connection;
    
    $stmt = $connection->prepare("SELECT id, email, password_hash FROM cms_users WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return false;
    }
    
    $user = $result->fetch_assoc();
    
    if (password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        return true;
    }
    return false;
}

function logout_user() {
    session_start();
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}
?>