<?php
require_once '../../initialize.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Ensure POST method
    ApiHelper::requireMethod('POST');

    // Get input data (handles JSON and form data)
    $data = ApiHelper::getJsonInput();
    error_log("Register POST Data: " . print_r($data, true));

    // Validate required fields
    ApiHelper::validateRequiredFields($data, ['name', 'email', 'password']);

    // Register user
    $response = Users::register($data);

    // Ensure response has a status key
    if (!isset($response['status'])) {
        throw new Exception('Invalid response structure from Users::register');
    }

    // Send response
    ApiHelper::sendJsonResponse($response, $response['status'] === 'success' ? 200 : 400);

} catch (PDOException $e) {
    error_log("PDO Exception: " . $e->getMessage());
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Database error occurred',
        'error'   => $e->getMessage() // Can hide in production
    ], 500);

} catch (Exception $e) {
    error_log("General Exception: " . $e->getMessage());
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'An internal server error occurred',
        'error'   => $e->getMessage() // Can hide in production
    ], 500);
}
