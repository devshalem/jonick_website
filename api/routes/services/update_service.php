<?php
require_once '../../../initialize.php'; // Adjust path as needed
header('Content-Type: application/json');

// Authenticate and authorize admin access
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

    if (!isset($input['id']) || empty($input['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Service ID is required for update.']);
        exit;
    }

    $serviceID = $input['id'];
    unset($input['id']); // Remove ID from the data array as updateService expects it separately

    $response = Services::updateService($serviceID, $input); // Using the updateService method

    echo json_encode($response);
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}
?>