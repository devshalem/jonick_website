<?php
require_once '../../initialize.php';

try {
    // Allow only GET requests
    ApiHelper::requireMethod('GET');

    // Check if a specific payment ID is requested
    $paymentId = $_GET['id'] ?? null;
    
    // Fetch payments by ID or all payments
    $payments = Payments::findPaymentsById($paymentId);

    ApiHelper::sendJsonResponse([
        'status' => $payments ? 'success' : 'error',
        'data'   => $payments ?: []
    ], $payments ? 200 : 404);

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Fetching payments failed',
        'error'   => $e->getMessage()
    ], 500);
}
