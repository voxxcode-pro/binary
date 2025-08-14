<?php
// --- Database Connection using Railway's MYSQL_URL ---
$db_url = getenv('MYSQL_URL');

// --- Fallback for Local Development ---
if (empty($db_url)) {
    $db_host = 'localhost';
    $db_user = 'your_local_db_username';
    $db_pass = 'your_local_db_password';
    $db_name = 'your_local_db_name';
} else {
    $db_url_parts = parse_url($db_url);

    $db_host = $db_url_parts['host'];
    $db_user = $db_url_parts['user'];
    $db_pass = $db_url_parts['pass'];
    $db_name = ltrim($db_url_parts['path'], '/');
}

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- API Keys and Other Settings from Environment Variables ---
define('ZENOPAY_API_KEY', getenv('ZENOPAY_API_KEY'));
define('ADMIN_EMAIL', getenv('ADMIN_EMAIL'));

// --- Static Configuration ---
define('ZENOPAY_API_URL', 'https://zenoapi.com/api/payments/mobile_money_tanzania');
define('ZENOPAY_STATUS_URL', 'https://zenoapi.com/api/payments/order-status');
define('FROM_EMAIL', 'noreply@yourdomain.com');
?>
