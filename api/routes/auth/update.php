<?php
require_once '../../initialize.php';

try {
    // Ensure request is PATCH or PUT (commonly used for updates)
    ApiHelper::requireMethod(['PUT', 'PATCH', 'POST']); 
    // We allow POST for clients that can't send PUT/PATCH easily.

    // Get input data
    $data = ApiHelper::getJsonInput();

    // Validate required fields
    ApiHelper::validateRequiredFields($data, ['user_id']); 
    // user_id is mandatory for update

    $userId = $data['user_id'];
    unset($data['user_id']); // Remove user_id from the update data

    if (empty($data)) {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'No data provided to update'
        ], 400);
    }

    // Find existing user
    $user = Users::findById($userId);
    if (!$user) {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'User not found'
        ], 404);
    }

    // Merge data and save
    $user->mergeAttributes($data);
    $update = $user->save();

    if ($update) {
        ApiHelper::sendJsonResponse([
            'status'  => 'success',
            'message' => 'User updated successfully',
            'data'    => $user
        ], 200);
    } else {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'Failed to update user'
        ], 400);
    }

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Update processing failed',
        'error'   => $e->getMessage()
    ], 500);
}
