<?php
include 'config.php';

$result = $conn->query("SELECT order_id, name, amount FROM transactions WHERE status = 'PENDING'");

if ($result->num_rows > 0) {
    while($transaction = $result->fetch_assoc()) {
        $order_id = $transaction['order_id'];

        $ch = curl_init(ZENOPAY_STATUS_URL . '?order_id=' . urlencode($order_id));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['x-api-key: ' . ZENOPAY_API_KEY]);
        $response = curl_exec($ch);
        curl_close($ch);
        $status_data = json_decode($response, true);

        if (isset($status_data['data'][0]['payment_status'])) {
            $payment_status = $status_data['data'][0]['payment_status'];

            if ($payment_status === 'COMPLETED') {
                $update_stmt = $conn->prepare("UPDATE transactions SET status = 'COMPLETED' WHERE order_id = ?");
                $update_stmt->bind_param("s", $order_id);
                $update_stmt->execute();

                $subject = "Payment Confirmed (BG) - Order #" . $order_id;
                $message = "<h2>Payment Confirmed by Background Worker</h2><p><strong>Name:</strong> " . $transaction['name'] . "</p><p><strong>Amount:</strong> " . $transaction['amount'] . " TZS</p><p><strong>Order ID:</strong> " . $order_id . "</p>";
                $headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: BINARY PAY <" . FROM_EMAIL . ">\r\n";
                mail(ADMIN_EMAIL, $subject, $message, $headers);

            } elseif ($payment_status === 'FAILED' || $payment_status === 'CANCELLED') {
                $update_stmt = $conn->prepare("UPDATE transactions SET status = ? WHERE order_id = ?");
                $update_stmt->bind_param("ss", $payment_status, $order_id);
                $update_stmt->execute();
            }
        }
    }
    echo "Verification check completed for " . $result->num_rows . " pending transactions.";
} else {
    echo "No pending transactions to check.";
}

$conn->close();
?>
