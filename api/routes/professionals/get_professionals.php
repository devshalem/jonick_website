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
    // Fetch all professionals, optionally join with users table to get user details
    $sql = "SELECT p.*, u.NAME as user_name, u.EMAIL as user_email, u.PHONE as user_phone 
            FROM professionals p
            JOIN users u ON p.user_id = u.ID";
    $professionals = Professionals::findBySql($sql);

    if ($professionals) {
        echo json_encode(['status' => 'success', 'data' => $professionals]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No professionals found.']);
    }
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}
?>