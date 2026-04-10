<?php
/**
 * Get current exchange rate from MAD to USD
 * In a production environment, you would use a currency API
 * For example: https://exchangeratesapi.io or similar
 */

header('Content-Type: application/json');

// In a real app, this would make an API call to get live exchange rates
// For simplicity, we'll use a fixed rate or you could store this in your database
$exchange_rate = 0.0945; // Example rate: 1 MAD = 0.0945 USD (as of May 2025)

// You could also cache this value for some time period to avoid excessive API calls

echo json_encode([
    'success' => true,
    'rate' => $exchange_rate,
    'from' => 'MAD',
    'to' => 'USD',
    'last_updated' => date('Y-m-d H:i:s')
]);
?>
