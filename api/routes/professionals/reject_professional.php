<?php
require_once '../../initialize.php';

try {
    ApiHelper::requireMethod('POST');
    $input = ApiHelper::getJsonInput();

    // Validate required field
    ApiHelper::validateRequiredFields($input, ['id']);
    $professionalID = $input['id'];

    // Fetch the professional object
    $professional = Professionals::findById($professionalID);
    if (!$professional) {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'Professional not found.'
        ], 404);
    }

    // Merge only allowed fields
    $allowedFields = ['expertise', 'availability', 'status'];
    $updateData = [];
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updateData[$field] = $input[$field];
        }
    }

    $professional->mergeAttributes($updateData);
    $professional->updated_at = date('Y-m-d H:i:s'); // Ensure timestamp is updated
    // If the validate method is not defined, we can skip this step
    if (method_exists($professional, 'validate')) {
        $errors = $professional->validate();
        if (!empty($errors)) {
            ApiHelper::sendJsonResponse([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $errors
            ], 400);
        }
    }
    if (!empty($errors)) {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'Validation failed',
            'errors'  => $errors
        ], 400);
    }

    // Save the updated professional
    $result = $professional->save();

    $response = $result
        ? ['status' => 'success', 'message' => 'Professional updated successfully.']
        : ['status' => 'error', 'message' => 'Failed to update professional.'];

    ApiHelper::sendJsonResponse($response, $response['status'] === 'success' ? 200 : 400);

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Failed to update professional.',
        'error'   => $e->getMessage()
    ], 500);
}
