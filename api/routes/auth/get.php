<?php
require_once '../../initialize.php';

try {
    // Only allow GET requests
    ApiHelper::requireMethod('GET');

    // Get user_id if provided
    $userId = $_GET['user_id'] ?? null;

    if ($userId) {
        // Fetch user by ID
        $user = Users::findById($userId);

        ApiHelper::sendJsonResponse([
            'status' => $user ? 'success' : 'error',
            'data'   => $user ?: null,
            'message' => $user ? 'User fetched successfully' : 'User not found'
        ], $user ? 200 : 404);
    } else {
        // Fetch all users
        $users = Users::findAll();

        ApiHelper::sendJsonResponse([
            'status' => 'success',
            'data'   => $users,
            'message' => 'All users fetched successfully'
        ], 200);
    }

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Fetching user(s) failed',
        'error'   => $e->getMessage()
    ], 500);
}
