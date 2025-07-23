<?php
require_once '../../initialize.php';

try {
    // Ensure POST method
    ApiHelper::requireMethod('POST');

    // Parse input data
    $input = ApiHelper::getJsonInput();

    // Validate required field
    ApiHelper::validateRequiredFields($input, ['id']);
    $paymentID = $input['id'];

    // Find payment by ID
    $payment = Payments::findById($paymentID);
    if (!$payment) {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'Payment not found.'
        ], 404);
    }   
    // Check if payment can be deleted (e.g., not already processed)
    if ($payment->status !== 'pending') {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'Payment cannot be deleted as it is not in a pending state.'
        ], 400);
    }   
    // Attempt deletion
    $result = $payment->delete();

    if ($result) {
        ApiHelper::sendJsonResponse([
            'status'  => 'success',
            'message' => 'Payment deleted successfully.'
        ], 200);
    } else {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'Failed to delete payment.'
        ], 500);
    }

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'An error occurred while deleting payment.',
        'error'   => $e->getMessage()
    ], 500);
}
