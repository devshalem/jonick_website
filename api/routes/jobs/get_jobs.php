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
    // Fetch all jobs with joined details from users, professionals, and services tables
    $sql = "SELECT j.*, u.NAME as user_name, u.EMAIL as user_email,
                   p.expertise as professional_expertise,
                   s.NAME as service_name, s.DESCRIPTION as service_description
            FROM jobs j
            JOIN users u ON j.user_id = u.ID
            LEFT JOIN professionals p ON j.professional_id = p.id
            JOIN services s ON j.service_id = s.id"; 

    $jobs = Jobs::findBySql($sql);

    if ($jobs) {
        echo json_encode(['status' => 'success', 'data' => $jobs]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No jobs found.']);
    }
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}
?>