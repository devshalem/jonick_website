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
    $paymentID = $input['id'] ?? null;

    if (empty($paymentID)) {
        echo json_encode(['status' => 'error', 'message' => 'Payment ID is required for deletion.']);
        exit;
    }

    $result = Payments::delete($paymentID); // Using delete method from DatabaseObject

    if ($result) {
        echo json_encode(['status' => 'success', 'message' => 'Payment deleted successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete payment.']);
    }
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}
?>