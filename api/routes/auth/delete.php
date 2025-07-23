<?php
require_once '../../initialize.php';

try {
    // Ensure request method is DELETE (or allow POST for clients with limitations)
    ApiHelper::requireMethod(['DELETE', 'POST']);

    // Get input data (handles JSON or form data)
    $data = ApiHelper::getJsonInput();

    // Validate required field
    ApiHelper::validateRequiredFields($data, ['user_id']);

    $userId = $data['user_id'];

    // Find existing user
    $user = Users::findById($userId);
    if (!$user) {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'User not found'
        ], 404);
    }

    // Attempt to delete the user
    $delete = $user->delete();
    if ($delete) {
        ApiHelper::sendJsonResponse([
            'status'  => 'success',
            'message' => 'User deleted successfully'
        ], 200);
    } else {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'Failed to delete user'
        ], 400);
    }

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Delete processing failed',
        'error'   => $e->getMessage()
    ], 500);
}
