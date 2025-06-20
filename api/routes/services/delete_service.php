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
    $serviceID = $input['id'] ?? null;

    if (empty($serviceID)) {
        echo json_encode(['status' => 'error', 'message' => 'Service ID is required for deletion.']);
        exit;
    }

    $response = Services::deleteService($serviceID); // Using the deleteService method

    echo json_encode($response);
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}
?>