<?php
require_once '../../initialize.php';
header('Content-Type: application/json');

try {
    // Allow only POST
    ApiHelper::requireMethod('POST');

    // Get JSON or form data
    $input = ApiHelper::getJsonInput();

    // Ensure Job ID is provided
    ApiHelper::validateRequiredFields($input, ['id']);

    // Find job by ID
    $job = Jobs::findById($input['id']);
    if (!$job) {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'Job not found.'
        ], 404);
    }

    // Merge only allowed fields
    $allowedFields = ['user_id', 'service_id', 'professional_id', 'status', 'description', 'price'];
    $updateData = [];
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updateData[$field] = $input[$field];
        }
    }

    // Update fields
    $job->mergeAttributes($updateData);
    $job->updated_at = date('Y-m-d H:i:s'); // Ensure updated_at is set

    // Validate if needed
    if (method_exists($job, 'validate')) {
        $errors = $job->validate();
        if (!empty($errors)) {
            ApiHelper::sendJsonResponse([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $errors
            ], 400);
        }
    }

    // Save job
    $result = $job->save();

    if ($result) {
        ApiHelper::sendJsonResponse([
            'status'  => 'success',
            'message' => 'Job updated successfully.'
        ], 200);
    } else {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'Failed to update job.'
        ], 500);
    }

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'An error occurred while updating the job.',
        'error'   => $e->getMessage()
    ], 500);
}
