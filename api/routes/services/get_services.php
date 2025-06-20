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

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Fetch all services
    // If you have a 'categories' table, you might want to join it here
    // Example with a hypothetical 'categories' table:
    // $sql = "SELECT s.*, c.name as category_name FROM services s LEFT JOIN categories c ON s.category_id = c.id";
    // $services = Services::findBySql($sql);

    $services = Services::allServices(); // Using the allServices method

    if ($services) {
        echo json_encode(['status' => 'success', 'data' => $services]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No services found.']);
    }
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}
?>