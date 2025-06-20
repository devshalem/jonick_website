<?php
// Start output buffering to prevent any accidental output
ob_start();

require_once '../../initialize.php';

// Set JSON header first
header('Content-Type: application/json');

try {
    // Only accept POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        exit;
    }

    // Get raw POST data and parse it
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    // Validate required fields
    if (empty($data['email']) || empty($data['password'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Email and password are required']);
        exit;
    }

    // Process login
    $response = Users::login($data['email'], $data['password']);

    // Validate response structure
    if (!isset($response['status'])) {
        throw new Exception('Invalid response structure from login method');
    }

    // Set appropriate HTTP status
    http_response_code($response['status'] === 'success' ? 200 : 401);

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Login processing failed',
        'error' => $e->getMessage()
    ]);
} finally {
    // Ensure no extra output
    ob_end_clean();
}
?>