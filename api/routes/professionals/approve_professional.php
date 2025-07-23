<?php
require_once '../../initialize.php';

try {
    ApiHelper::requireMethod('POST');
    $input = ApiHelper::getJsonInput();

    // Validate professional ID
    ApiHelper::validateRequiredFields($input, ['id']);
    $professionalID = $input['id'];

    // Approve professional
    $response = Professionals::approve($professionalID);

    // Ensure the response is in the correct structure
    if (!is_array($response) || !isset($response['status'])) {
        throw new Exception('Invalid response structure from Professionals::approve');
    }

    ApiHelper::sendJsonResponse($response, $response['status'] === 'success' ? 200 : 400);

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Professional approval failed.',
        'error'   => $e->getMessage()
    ], 500);
}
