<?php
echo "Attempting to load initialize.php...<br>";
require_once('initialize.php');

// Check if DatabaseObject class is loaded
if (class_exists('DatabaseObject')) {
    echo "DatabaseObject class is loaded successfully.<br>";
} else {
    echo "Error: DatabaseObject class is NOT loaded.<br>";
}

// Check if Users class can be autoloaded
if (class_exists('Users')) {
    echo "Users class is loaded successfully.<br>";
} else {
    echo "Error: Users class is NOT loaded, or Autoloader failed.<br>";
}

echo "Script finished.";
?>