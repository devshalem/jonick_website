<?php
require_once '../../initialize.php';

try {
    ApiHelper::requireMethod('POST');
    $input = ApiHelper::getJsonInput();

    // Validate required fields
    ApiHelper::validateRequiredFields($input, ['user_id', 'expertise']);

    // Prepare data for Professionals::apply
    $data = [
        'user_id'      => $input['user_id'],
        'expertise'    => $input['expertise'],
        'availability' => $input['availability'] ?? '',
        'status'       => 'pending',
        'created_at'   => date('Y-m-d H:i:s')
    ];

    // Call the apply method in Professionals class
    $response = Professionals::apply($data);

    // Ensure proper response structure
    if (!is_array($response) || !isset($response['status'])) {
        throw new Exception('Invalid response from Professionals::apply');
    }

    ApiHelper::sendJsonResponse($response, $response['status'] === 'success' ? 200 : 400);

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Failed to submit professional application.',
        'error'   => $e->getMessage()
    ], 500);
}
