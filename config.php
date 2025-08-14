<?php
$db_url = getenv('MYSQL_URL');

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

$createTableSql = "
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` VARCHAR(255) NOT NULL UNIQUE,
  `category` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `amount` DECIMAL(10, 2) NOT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'PENDING',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";

if (!$conn->query($createTableSql)) {
    die("Error creating table: " . $conn->error);
}

define('ZENOPAY_API_KEY', getenv('ZENOPAY_API_KEY'));
define('ADMIN_EMAIL', getenv('ADMIN_EMAIL'));

define('ZENOPAY_API_URL', 'https://zenoapi.com/api/payments/mobile_money_tanzania');
define('ZENOPAY_STATUS_URL', 'https://zenoapi.com/api/payments/order-status');
define('FROM_EMAIL', 'noreply@yourdomain.com');
?>
