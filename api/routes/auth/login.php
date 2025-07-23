<?php
require_once '../../initialize.php';

try {
    // Allow only POST
    ApiHelper::requireMethod('POST');

    // Get request data
    $data = ApiHelper::getJsonInput();

    // Validate required fields
    ApiHelper::validateRequiredFields($data, ['email', 'password']);

    // Process login
    $response = Users::login($data['email'], $data['password']);

    // Validate response structure
    if (!is_array($response) || !isset($response['status'])) {
        throw new Exception('Invalid response from Users::login');
    }

    // Send response
    $statusCode = $response['status'] === 'success' ? 200 : 401;
    ApiHelper::sendJsonResponse($response, $statusCode);

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Login processing failed',
        'error'   => $e->getMessage()
    ], 500);
}
