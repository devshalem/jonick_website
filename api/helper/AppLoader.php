<?php

class App
{
    /**
     * Initialize the application.
     */
    public static function init()
    {
        ob_start();

        // Load environment variables
        self::loadEnv();

        // Define constants
        self::defineConstants();

        // Load required core files
        self::loadCoreFiles();

        // Register autoloader
        self::registerAutoload();

        // Connect to database
        self::initDatabase();

        ob_end_clean();
    }

    /**
     * Load .env file.
     */
    private static function loadEnv()
    {
        $envPath = __DIR__ . '/../src/key.env';
        require_once __DIR__ . '/EnvLoader.php';
        try {
            EnvLoader::load($envPath);
        } catch (Exception $e) {
            self::jsonDie("Environment file missing or invalid: " . $e->getMessage());
        }
    }

    /**
     * Define app-wide constants.
     */
    private static function defineConstants()
    {
        if (!defined('APP_INITIALIZED')) {
            define('APP_INITIALIZED', true);
        }
    }

    /**
     * Load required core files.
     */
    private static function loadCoreFiles()
    {
        $required_files = [
            __DIR__ . '/../src/credentials.php',
            __DIR__ . '/../src/db_functions.php',
            __DIR__ . '/../src/validation_functions.php',
            __DIR__ . '/../src/jwt_function.php',
            __DIR__ . '/ApiHelper.php'
        ];

        foreach ($required_files as $file) {
            if (!file_exists($file)) {
                self::jsonDie("Required file not found: $file");
            }
            require_once $file;
        }
    }

    /**
     * Autoload classes from /classes folder.
     */
    private static function registerAutoload()
    {
        spl_autoload_register(function ($class) {
            if (!preg_match('/\A[\w\\\\]+\Z/', $class)) {
                error_log("Invalid class name: $class");
                return;
            }

            $base_path = __DIR__ . '/../classes/' . str_replace('\\', '/', $class);
            $file_variants = [$base_path . '.class.php', $base_path . '.php'];

            foreach ($file_variants as $file) {
                if (file_exists($file)) {
                    require_once $file;
                    return;
                }
            }
        });
    }

    /**
     * Initialize the database and set it to DatabaseObject.
     */
    private static function initDatabase()
    {
        $database = db_connect();
        if (!$database) {
            self::jsonDie('Failed to connect to database');
        }

        if (class_exists('DatabaseObject')) {
            DatabaseObject::setDatabase($database);
        } else {
            self::jsonDie('DatabaseObject class not found in classes/DatabaseObject.php');
        }
    }

    /**
     * Output a JSON error and stop execution.
     */
    private static function jsonDie($message)
    {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $message]);
        exit;
    }
}
