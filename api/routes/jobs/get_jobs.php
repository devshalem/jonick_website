<?php
require_once '../../initialize.php';
header('Content-Type: application/json');

try {
    // Ensure GET method
    ApiHelper::requireMethod('GET');

    // Check if job ID is provided
    $jobId = $_GET['id'] ?? null;

    // Fetch jobs (all or by ID)
    $jobs = Jobs::findJobById($jobId);

    ApiHelper::sendJsonResponse([
        'status' => $jobs ? 'success' : 'error',
        'data'   => $jobs ?: []
    ], $jobs ? 200 : 404);

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Failed to fetch jobs',
        'error'   => $e->getMessage()
    ], 500);
}
