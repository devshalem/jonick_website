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
    $bookingID = $input['id'] ?? null;
    $newStatus = $input['status'] ?? null;

    if (empty($bookingID) || empty($newStatus)) {
        echo json_encode(['status' => 'error', 'message' => 'Booking ID and new status are required.']);
        exit;
    }

    // Validate allowed statuses
    $allowedStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];
    if (!in_array($newStatus, $allowedStatuses)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid status provided.']);
        exit;
    }

    $response = Bookings::updateStatus($bookingID, $newStatus); // Using the updateStatus method from Bookings class

    echo json_encode($response);
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}
?>