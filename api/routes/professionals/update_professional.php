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
        echo json_encode(['status' => 'error', 'message' => 'Professional ID is required for update.']);
        exit;
    }

    $professional = Professionals::findProfessionalById($input['id']);

    if (!$professional) {
        echo json_encode(['status' => 'error', 'message' => 'Professional not found.']);
        exit;
    }

    // Merge only allowed fields
    $allowedFields = ['expertise', 'availability', 'status']; // Add more fields if needed
    $updateData = [];
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updateData[$field] = $input[$field];
        }
    }

    $professional->mergeAttributes($updateData);

    // Validate the professional object before saving
    $errors = $professional->validate();
    if (!empty($errors)) {
        echo json_encode(['status' => 'error', 'message' => 'Validation failed', 'errors' => $errors]);
        exit;
    }

    $result = $professional->save();

    if ($result) {
        echo json_encode(['status' => 'success', 'message' => 'Professional updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update professional.']);
    }
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}
?>