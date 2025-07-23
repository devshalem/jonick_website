<?php
require_once '../../initialize.php';

try {
    // Allow only POST
    ApiHelper::requireMethod('POST');

    // Get request data
    $input = ApiHelper::getJsonInput();
    ApiHelper::validateRequiredFields($input, ['id']);

    // Fetch payment by ID
    $payment = Payments::findById($input['id']);
    if (!$payment) {
        ApiHelper::sendJsonResponse([
            'status' => 'error',
            'message' => 'Payment not found.'
        ], 404);
    }

    // Merge allowed fields
    $allowedFields = ['user_id', 'job_id', 'amount', 'status', 'transaction_id', 'method'];
    $updateData = [];
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updateData[$field] = $input[$field];
        }
    }

    $payment->mergeAttributes($updateData);
    $payment->updated_at = date('Y-m-d H:i:s'); // Ensure timestamp is updated

    // Optional: Validation (if Payments class has validate method)
    // $errors = $payment->validate();
    // if (!empty($errors)) {
    //     ApiHelper::sendJsonResponse(['status' => 'error', 'errors' => $errors], 422);
    // }

    // Save payment
    $result = $payment->save();
    if ($result) {
        ApiHelper::sendJsonResponse([
            'status' => 'success',
            'message' => 'Payment updated successfully.'
        ]);
    } else {
        ApiHelper::sendJsonResponse([
            'status' => 'error',
            'message' => 'Failed to update payment.'
        ], 500);
    }

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Payment update failed.',
        'error'   => $e->getMessage()
    ], 500);
}
