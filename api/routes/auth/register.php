<?php
error_reporting(E_ALL);       // Crucial for displaying errors
ini_set('display_errors', 1); // Crucial for displaying errors
header('Content-Type: application/json'); // Keep this, it should be set last by PHP if no other errors occ

try {
    require_once '../../initialize.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = $_POST;
        error_log("Register POST Data: " . print_r($data, true)); // <-- ADD THIS LINE

       if (empty($data['name']) || empty($data['email']) || empty($data['password'])) { // <-- CHANGED THIS LINE
    echo json_encode(['status' => 'error', 'message' => 'Name, Email, and Password are required.']);
    exit;
}

        $response = Users::register($data); // This should return ['status' => 'success', 'message' => '...'] or ['status' => 'error', 'message' => '...']
        echo json_encode($response);
        exit;

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
        exit;
    }
} catch (PDOException $e) {
    // Catch database-specific connection errors
    error_log("PDO Exception: " . $e->getMessage()); // Log the error for server-side debugging
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]); // Expose message for debugging
    exit;
} catch (Exception $e) {
    // Catch any other general exceptions (e.g., from credentials.php or other early includes)
    error_log("General Exception: " . $e->getMessage()); // Log the error
    echo json_encode(['status' => 'error', 'message' => 'An internal server error occurred: ' . $e->getMessage()]); // Expose message for debugging
    exit;
}

?>