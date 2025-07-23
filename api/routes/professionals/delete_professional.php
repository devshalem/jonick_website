<?php
require_once '../../initialize.php';

try {
    ApiHelper::requireMethod('POST');
    $input = ApiHelper::getJsonInput();

    // Validate Professional ID
    ApiHelper::validateRequiredFields($input, ['id']);
    $professionalID = $input['id'];
    // Check if the professional exists
    $professional = Professionals::findProfessionalsById($professionalID);
    // Delete the professional
    $result = $professional->delete();

    if ($result) {
        ApiHelper::sendJsonResponse([
            'status'  => 'success',
            'message' => 'Professional deleted successfully.'
        ], 200);
    } else {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'Failed to delete professional.'
        ], 400);
    }

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Professional deletion failed.',
        'error'   => $e->getMessage()
    ], 500);
}
