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

    if (!isset($input['id']) || empty($input['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Booking ID is required for update.']);
        exit;
    }

    $booking = Bookings::findBookingById($input['id']);

    if (!$booking) {
        echo json_encode(['status' => 'error', 'message' => 'Booking not found.']);
        exit;
    }

    // Merge only allowed fields
    $allowedFields = ['user_id', 'service_id', 'professional_id', 'status', 'appointment_date', 'total_price'];
    $updateData = [];
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updateData[$field] = $input[$field];
        }
    }

    $booking->mergeAttributes($updateData);
    $booking->updated_at = date('Y-m-d H:i:s'); // Update timestamp

    // Validate the booking object before saving
    $errors = $booking->validate();
    if (!empty($errors)) {
        echo json_encode(['status' => 'error', 'message' => 'Validation failed', 'errors' => $errors]);
        exit;
    }

    $result = $booking->save(); // Save will call update() if ID exists

    if ($result) {
        echo json_encode(['status' => 'success', 'message' => 'Booking updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update booking.']);
    }
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}
?>