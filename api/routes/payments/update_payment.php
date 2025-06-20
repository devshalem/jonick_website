<?php
require_once '../../../initialize.php'; // Adjust path as needed
header('Content-Type: application/json');

// Authenticate and authorize admin access
// $auth = new AuthMiddleware();
// $userRole = $auth->checkAdmin();

// if ($userRole !== 'admin') {
//     echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Admin privileges required.']);
//     exit;
// }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['id']) || empty($input['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Payment ID is required for update.']);
        exit;
    }

    $payment = Payments::findById($input['id']); // Using findById from DatabaseObject

    if (!$payment) {
        echo json_encode(['status' => 'error', 'message' => 'Payment not found.']);
        exit;
    }

    // Merge only allowed fields
    // Assuming 'payments' table has fields like user_id, job_id, amount, status, transaction_id, method
    $allowedFields = ['user_id', 'job_id', 'amount', 'status', 'transaction_id', 'method'];
    $updateData = [];
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updateData[$field] = $input[$field];
        }
    }

    $payment->mergeAttributes($updateData);
    $payment->updated_at = date('Y-m-d H:i:s'); // Assuming 'updated_at' field exists

    // If you have a validate method in Payments class, call it here
    // $errors = $payment->validate();
    // if (!empty($errors)) {
    //     echo json_encode(['status' => 'error', 'message' => 'Validation failed', 'errors' => $errors]);
    //     exit;
    // }

    $result = $payment->save(); // Save will call update() if ID exists

    if ($result) {
        echo json_encode(['status' => 'success', 'message' => 'Payment updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update payment.']);
    }
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}
?>