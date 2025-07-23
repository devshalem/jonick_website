<?php
require_once '../../initialize.php';

try {
    ApiHelper::requireMethod('POST');
    $input = ApiHelper::getJsonInput();

    ApiHelper::validateRequiredFields($input, ['id']);
    $categoryID = $input['id'];

    $category = Categories::findCategoryById($categoryID);

    if ($category) {
        ApiHelper::sendJsonResponse([
            'status' => 'success',
            'data'   => $category
        ], 200);
    } else {
        ApiHelper::sendJsonResponse([
            'status' => 'error',
            'message'=> 'Category not found.'
        ], 404);
    }

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Failed to fetch category.',
        'error'   => $e->getMessage()
    ], 500);
}
