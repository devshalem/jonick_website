<?php
require_once '../../initialize.php';
header('Content-Type: application/json');

try {
    // Ensure GET method
    ApiHelper::requireMethod('GET');

    // Get professional ID from query
    $professionalId = $_GET['professional_id'] ?? null;

    if (empty($professionalId)) {
        ApiHelper::sendJsonResponse([
            'status' => 'error',
            'message' => 'Professional ID is required'
        ], 400);
    }

    // Fetch jobs for the professional
    $jobs = Jobs::findJobsByProfessional($professionalId);

    if ($jobs) {
        ApiHelper::sendJsonResponse([
            'status' => 'success',
            'data'   => $jobs
        ], 200);
    } else {
        ApiHelper::sendJsonResponse([
            'status' => 'error',
            'message' => 'No jobs found for this professional'
        ], 404);
    }

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Failed to fetch jobs',
        'error'   => $e->getMessage()
    ], 500);
}
