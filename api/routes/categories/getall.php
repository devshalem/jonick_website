<?php
require_once '../../initialize.php';

try {
    ApiHelper::requireMethod('GET');

    $categories = Categories::allCategories();

    if ($categories) {
        ApiHelper::sendJsonResponse([
            'status' => 'success',
            'data'   => $categories
        ], 200);
    } else {
        ApiHelper::sendJsonResponse([
            'status' => 'error',
            'message'=> 'No categories found.'
        ], 404);
    }

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Failed to fetch categories.',
        'error'   => $e->getMessage()
    ], 500);
}
