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
        echo json_encode(['status' => 'error', 'message' => 'Job ID is required for update.']);
        exit;
    }

    $job = Jobs::findById($input['id']); // Using findById from DatabaseObject

    if (!$job) {
        echo json_encode(['status' => 'error', 'message' => 'Job not found.']);
        exit;
    }

    // Merge only allowed fields
    // Assuming 'jobs' table has fields like user_id, service_id, professional_id, status, description, price
    $allowedFields = ['user_id', 'service_id', 'professional_id', 'status', 'description', 'price'];
    $updateData = [];
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updateData[$field] = $input[$field];
        }
    }

    $job->mergeAttributes($updateData);
    $job->updated_at = date('Y-m-d H:i:s'); // Assuming 'updated_at' field exists

    // If you have a validate method in Jobs class, call it here
    // $errors = $job->validate();
    // if (!empty($errors)) {
    //     echo json_encode(['status' => 'error', 'message' => 'Validation failed', 'errors' => $errors]);
    //     exit;
    // }

    $result = $job->save(); // Save will call update() if ID exists

    if ($result) {
        echo json_encode(['status' => 'success', 'message' => 'Job updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update job.']);
    }
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}
?>