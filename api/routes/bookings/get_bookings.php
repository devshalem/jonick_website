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
    // Fetch all bookings with joined details from users, professionals, and services tables
    $sql = "SELECT b.*, u.NAME as user_name, u.EMAIL as user_email,
                   p.expertise as professional_expertise,
                   s.NAME as service_name, s.DESCRIPTION as service_description
            FROM bookings b
            JOIN users u ON b.user_id = u.ID
            LEFT JOIN professionals p ON b.professional_id = p.id
            JOIN services s ON b.service_id = s.id"; // Assuming service_id is always present

    $bookings = Bookings::findBySql($sql);

    if ($bookings) {
        echo json_encode(['status' => 'success', 'data' => $bookings]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No bookings found.']);
    }
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}
?>