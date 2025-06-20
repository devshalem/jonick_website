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
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data input']);
        exit;
    }

    $response = Services::createService($data); // Using the createService method

    echo json_encode($response);
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}
?>