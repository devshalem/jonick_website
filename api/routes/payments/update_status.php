<?php
require_once '../../initialize.php';

try {
    // Only allow POST requests
    ApiHelper::requireMethod('POST');

    // Get input data
    $input = ApiHelper::getJsonInput();
    ApiHelper::validateRequiredFields($input, ['id', 'status']);

    $paymentID = $input['id'];
    $newStatus = strtolower(trim($input['status']));

    // Allowed statuses (adjust if necessary)
    $allowedStatuses = ['pending', 'completed', 'failed', 'cancelled'];
    if (!in_array($newStatus, $allowedStatuses)) {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'Invalid status provided.'
        ], 400);
    }

    // Find payment
    $payment = Payments::findById($paymentID);
    if (!$payment) {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'Payment not found.'
        ], 404);
    }

    // Update status
    $payment->status = $newStatus;
    $payment->updated_at = date('Y-m-d H:i:s');

    if ($payment->save()) {
        ApiHelper::sendJsonResponse([
            'status'  => 'success',
            'message' => 'Payment status updated successfully.'
        ]);
    } else {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'Failed to update payment status.'
        ], 500);
    }

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'An error occurred while updating payment status.',
        'error'   => $e->getMessage()
    ], 500);
}
