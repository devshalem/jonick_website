<?php

class ApiHelper
{
    public static function requireMethod($method)
    {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
            self::sendJsonResponse(
                ['status' => 'error', 'message' => "Method not allowed, use $method"],
                405
            );
            exit;
        }
    }

    public static function getJsonInput()
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return $data ?? $_POST;
    }

    public static function validateRequiredFields($data, $fields)
    {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                self::sendJsonResponse([
                    'status' => 'error',
                    'message' => "Field '$field' is required"
                ], 400);
                exit;
            }
        }
    }

    public static function sendJsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
