<?php

    ob_start(); // Turning on output buffering
    require_once('src/credentials.php');//defined database credentials
    require_once('src/db_functions.php');//defined database functions
    require_once('src/validation_functions.php');//defined database functions
    require_once('src/jwt_function.php');//defined jwt functions
    
    require_once('classes/databaseobject.class.php'); 

    //Autoload Class Definitions
    function my_autoload($class)
    {
        if (preg_match('/\A\w+\Z/',$class)) {
            $file_name = __DIR__ . '/';
            $file_name .= 'classes/'.$class.'.class.php';
            if(file_exists($file_name)){
                include($file_name);
            }
        }
    }
    spl_autoload_register('my_autoload');
    
    $database= db_connect();

    // CRITICAL DEBUG LINE: Checks type of $database right after connection
    error_log("DEBUG in initialize.php: Type of \$database after db_connect(): " . (is_object($database) ? get_class($database) : gettype($database)));

    // Ensure $database is a valid PDO object before setting it
    if (! ($database instanceof PDO)) {
        throw new Exception("Database connection (PDO object) not established in initialize.php. Type found: " . (is_object($database) ? get_class($database) : gettype($database)));
    }

    DatabaseObject::setDatabase($database); 

?>