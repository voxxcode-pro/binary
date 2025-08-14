<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BINARY PAY - Mobile Payments</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>BINARY PAY</h1>
            <p>Secure Mobile Money Payments</p>
        </header>
        
        <div class="payment-form">
            <form id="paymentForm">
                <div class="form-group">
                    <label for="category">Payment Category:</label>
                    <select id="category" name="category" required>
                        <option value="">Select a category</option>
                        <option value="NORMAL PAY">NORMAL PAY</option>
                        <option value="DROPSHIPING">DROPSHIPING</option>
                        <option value="FOREX SIGNALS">FOREX SIGNALS</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="name">Full Name:</label>
                    <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number:</label>
                    <input type="tel" id="phone" name="phone" placeholder="07XXXXXXXX" pattern="[0-9]{10}" required>
                    <small>Format: 07XXXXXXXX (10 digits)</small>
                </div>
                
                <div class="form-group">
                    <label for="amount">Amount (TZS):</label>
                    <input type="number" id="amount" name="amount" placeholder="Enter amount" min="100" required>
                </div>
                
                <button type="submit" id="payButton" class="btn-pay">
                    <span id="buttonText">PAY NOW</span>
                    <div id="loadingSpinner" class="spinner hidden">
                        <div class="double-bounce1"></div>
                        <div class="double-bounce2"></div>
                    </div>
                </button>
            </form>
        </div>
    </div>
    <div id="modal-container"></div>
    <script src="js/script.js"></script>
</body>
</html>
