<?php
require_once '../../initialize.php';
header('Content-Type: application/json');

try {
    // Ensure POST method
    ApiHelper::requireMethod('POST');

    // Parse request data
    $input = ApiHelper::getJsonInput();

    // Validate Job ID
    ApiHelper::validateRequiredFields($input, ['id']);
    $jobID = $input['id'];
    // Check if job exists
    $job = Jobs::findById($jobID);
    if (!$job) {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'Job not found.'
        ], 404);
    }
    // Attempt to delete job
    $result = $job->delete();

    ApiHelper::sendJsonResponse([
        'status'  => $result ? 'success' : 'error',
        'message' => $result ? 'Job deleted successfully.' : 'Failed to delete job.'
    ], $result ? 200 : 500);

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Job deletion failed.',
        'error'   => $e->getMessage()
    ], 500);
}
