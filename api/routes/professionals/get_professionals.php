<?php
require_once '../../initialize.php';

try {
    ApiHelper::requireMethod('GET');

    // Check if an ID is provided
    $professionalId = $_GET['id'] ?? null;
    // Fetch professionals by ID or all professionals
    $data = Professionals::findProfessionalsById($professionalId);
    if ($data) {
        ApiHelper::sendJsonResponse(['status' => 'success', 'data' => $data], 200);
    } else {
        ApiHelper::sendJsonResponse(['status' => 'error', 'message' => 'No professionals found.'], 404);
    }

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Failed to fetch professionals.',
        'error'   => $e->getMessage()
    ], 500);
}
