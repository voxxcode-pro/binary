<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['phone'])) {
    header('Content-Type: application/json');

    $category = $_POST['category'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $amount = (int)$_POST['amount'];
    $order_id = 'binpay_' . uniqid();

    $stmt = $conn->prepare("INSERT INTO transactions (order_id, category, name, email, phone, amount, status) VALUES (?, ?, ?, ?, ?, ?, 'PENDING')");
    $stmt->bind_param("ssssdi", $order_id, $category, $name, $email, $phone, $amount);
    $stmt->execute();

    $payload = [
        'order_id' => $order_id,
        'buyer_email' => $email,
        'buyer_name' => $name,
        'buyer_phone' => $phone,
        'amount' => $amount
    ];

    $ch = curl_init(ZENOPAY_API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'x-api-key: ' . ZENOPAY_API_KEY
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response, true);

    if (isset($result['status']) && $result['status'] === 'success') {
        echo json_encode(['status' => 'initiated', 'order_id' => $order_id]);
    } else {
        $errorMessage = $result['message'] ?? 'Failed to initiate payment.';
        echo json_encode(['status' => 'error', 'message' => $errorMessage]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] == 'check_status') {
    header('Content-Type: application/json');
    $order_id = $_GET['order_id'];

    $ch = curl_init(ZENOPAY_STATUS_URL . '?order_id=' . urlencode($order_id));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['x-api-key: ' . ZENOPAY_API_KEY]);
    $response = curl_exec($ch);
    curl_close($ch);
    $status_data = json_decode($response, true);

    if (isset($status_data['data'][0])) {
        $paymentDetails = $status_data['data'][0];
        $payment_status = $paymentDetails['payment_status'];

        $stmt = $conn->prepare("SELECT status FROM transactions WHERE order_id = ?");
        $stmt->bind_param("s", $order_id);
        $stmt->execute();
        $db_status = $stmt->get_result()->fetch_assoc()['status'];

        if ($payment_status === 'COMPLETED' && $db_status === 'PENDING') {
            $update_stmt = $conn->prepare("UPDATE transactions SET status = 'COMPLETED' WHERE order_id = ?");
            $update_stmt->bind_param("s", $order_id);
            $update_stmt->execute();

            $email_stmt = $conn->prepare("SELECT name, email, phone, amount FROM transactions WHERE order_id = ?");
            $email_stmt->bind_param("s", $order_id);
            $email_stmt->execute();
            $transaction = $email_stmt->get_result()->fetch_assoc();

            $subject = "Payment Confirmed - Order #" . $order_id;
            $message = "<h2>Payment Confirmed</h2><p><strong>Name:</strong> " . $transaction['name'] . "</p><p><strong>Amount:</strong> " . $transaction['amount'] . " TZS</p>";
            $headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: BINARY PAY <" . FROM_EMAIL . ">\r\n";
            mail(ADMIN_EMAIL, $subject, $message, $headers);
        }

        echo json_encode([
            'status' => 'success',
            'payment_status' => $payment_status,
            'details' => [
                'phone_used' => $paymentDetails['msisdn'],
                'network' => $paymentDetails['channel'],
                'amount' => $paymentDetails['amount'],
                'transaction_id' => $paymentDetails['transid']
            ]
        ]);

    } else {
        echo json_encode(['status' => 'error', 'payment_status' => 'UNKNOWN']);
    }
    exit;
}

http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
?>
