<?php
require_once '../../../initialize.php'; // Adjust path as needed
header('Content-Type: application/json');

// REMOVE OR COMMENT OUT THIS AUTHENTICATION BLOCK FOR NO LOGGING
/*
$auth = new AuthMiddleware();
$userRole = $auth->checkAdmin();

if ($userRole !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Admin privileges required.']);
    exit;
}
*/

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $professionalID = $input['id'] ?? null;

    if (empty($professionalID)) {
        echo json_encode(['status' => 'error', 'message' => 'Professional ID is required for approval.']);
        exit;
    }

    $response = Professionals::approve($professionalID); // Using the approve method from Professionals class

    echo json_encode($response);
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}
?>