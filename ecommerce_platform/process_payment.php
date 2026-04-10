<?php
// Start session and include configuration
session_start();
require 'config.php';

// Make sure to install stripe PHP library: composer require stripe/stripe-php
require 'vendor/autoload.php';

// Set your secret key, get it from https://dashboard.stripe.com/apikeys
\Stripe\Stripe::setApiKey('sk_test_YOUR_SECRET_KEY');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$paymentMethod = isset($input['payment_method']) ? $input['payment_method'] : '';
$orderData = isset($input['order_data']) ? $input['order_data'] : [];
$customerInfo = isset($input['customer_info']) ? $input['customer_info'] : [];

// Validate input
if (empty($paymentMethod) || empty($orderData) || empty($customerInfo)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required data']);
    exit;
}

// Calculate order total
$amount = 0;
foreach ($orderData as $item) {
    $amount += $item['price'] * $item['quantity'];
}

// Convert to cents for Stripe
$amountInCents = $amount * 100;

try {
    if ($paymentMethod === 'stripe') {
        // Create a PaymentIntent with the order amount and currency
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $amountInCents,
            'currency' => 'usd',
            'payment_method_types' => ['card'],
            'description' => 'Order from Cara Store',
            'metadata' => [
                'customer_email' => $customerInfo['email'],
                'customer_name' => $customerInfo['name']
            ],
            'receipt_email' => $customerInfo['email']
        ]);

        // Send publishable key and PaymentIntent details to client
        echo json_encode([
            'clientSecret' => $paymentIntent->client_secret,
            'amount' => $amount,
            'id' => $paymentIntent->id
        ]);
    } else if ($paymentMethod === 'paypal') {
        // For PayPal integration, you would interact with PayPal's API here
        // This is a simplified example
        echo json_encode([
            'success' => true,
            'message' => 'PayPal integration would go here',
            'amount' => $amount
        ]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid payment method']);
    }
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>