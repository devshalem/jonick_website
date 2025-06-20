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

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Fetch all payments with joined details from users and jobs tables
    // Assuming 'payments' table has 'user_id' and 'job_id' foreign keys
    $sql = "SELECT p.*, u.NAME as user_name, u.EMAIL as user_email,
                   j.description as job_description, j.status as job_status
            FROM payments p
            JOIN users u ON p.user_id = u.ID
            LEFT JOIN jobs j ON p.job_id = j.id"; // Use LEFT JOIN in case a payment isn't linked to a job

    $payments = Payments::findBySql($sql);

    if ($payments) {
        echo json_encode(['status' => 'success', 'data' => $payments]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No payments found.']);
    }
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}
?>