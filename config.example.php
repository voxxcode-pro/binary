<?php
$db_host = 'localhost';
$db_user = 'your_db_username';
$db_pass = 'your_db_password';
$db_name = 'your_db_name';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

define('ZENOPAY_API_KEY', 'ENTER_YOUR_ZENOPAY_API_KEY_HERE');
define('ZENOPAY_API_URL', 'https://zenoapi.com/api/payments/mobile_money_tanzania');
define('ZENOPAY_STATUS_URL', 'https://zenoapi.com/api/payments/order-status');

define('ADMIN_EMAIL', 'admin@example.com');
define('FROM_EMAIL', 'noreply@example.com');
?>
