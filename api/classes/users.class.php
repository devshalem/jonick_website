<?php

// Corrected path: Go up one directory (from classes/) to api/, then find initialize.php
require_once __DIR__ . '/../initialize.php'; // Corrected path

class Users extends DatabaseObject
{
    // Table name
    static protected $table_name = "users";

    // Database columns (Keep these as they match DB column names)
    static protected $db_columns = ['ID', 'NAME', 'EMAIL', 'PASSWORD', 'PHONE', 'ROLE', 'CREATED_AT', 'UPDATED_AT'];

    // Class properties for each column (Change these to lowercase to match $_POST)
    public $ID; // ID is usually uppercase from DB
    public $NAME; // Change public $name; to public $NAME;
    public $EMAIL; // Change public $email; to public $EMAIL;
    public $PASSWORD; // Change public $password; to public $PASSWORD;
    public $PHONE; // Change public $phone; to public $PHONE;
    public $ROLE; // Change public $role; to public $ROLE;
    public $CREATED_AT; // Change public $created_at; to public $CREATED_AT;
    public $UPDATED_AT; // Change public $updated_at; to public $UPDATED_AT;


    // Constructor
    public function __construct($args = [])
    {
        // Now map incoming lowercase keys from $args (which is $_POST) to uppercase properties
        $this->ID = $args['ID'] ?? $args['id'] ?? null; // Handle both cases for ID if needed
        $this->NAME = $args['name'] ?? ''; // Map 'name' from $_POST to NAME property
        $this->EMAIL = $args['email'] ?? ''; // Map 'email' from $_POST to EMAIL property
        $this->PASSWORD = $args['password'] ?? ''; // Map 'password' from $_POST to PASSWORD property
        $this->PHONE = $args['phone'] ?? ''; // Map 'phone' from $_POST to PHONE property
        $this->ROLE = $args['role'] ?? 'user'; // Map 'role' from $_POST to ROLE property, default 'user'
        $this->CREATED_AT = $args['created_at'] ?? date('Y-m-d H:i:s'); // Map 'created_at' if available, otherwise set default
        $this->UPDATED_AT = $args['updated_at'] ?? date('Y-m-d H:i:s'); // Map 'updated_at' if available, otherwise set default
    }
    // Register a new user
    static public function register($data)
    {
        $passwordHash = new PasswordHash();
        $passwordHashed = isset($data["PASSWORD"]) ? $passwordHash->hash($data["PASSWORD"]) : '';

        $user = new self($data);
        $user->password = $passwordHashed;

        $errors = $user->validate();
        if (!empty($errors)) {
            return ['status' => 'error', 'message' => 'Validation failed', 'errors' => $errors];
        }

        $save_query = $user->save();

        return $save_query
            ? ['status' => 'success', 'message' => 'User registered successfully']
            : ['status' => 'error', 'message' => 'Registration failed'];
    }

    // Verify user login
    static public function login($email, $password)
    {
        $sql = "SELECT * FROM " . static::$table_name . " WHERE EMAIL = :email";
        $stmt = self::executeQuery($sql, ['email' => $email]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_data) {
            $user = static::instantiate($user_data);
            $passwordHash = new PasswordHash();

            if ($passwordHash->verify($password, $user->PASSWORD)) { // Changed $user->password to $user->PASSWORD to match property name.
                $tokenData = [
                    'user_id' => $user->ID, // Changed to $user->ID
                    'name' => $user->NAME, // Changed to $user->NAME
                    'role' => $user->ROLE // Changed to $user->ROLE
                ];
                $token = JWT::generateToken($tokenData);

                return [
                    'status' => 'success',
                    'message' => 'Login successful',
                    'user' => $user,
                    'token' => $token
                ];
            } else {
                return ['status' => 'error', 'message' => 'Invalid password'];
            }
        }

        return ['status' => 'error', 'message' => 'User not found'];
    }

    // Retrieve all users
    static public function allUsers()
    {
        return self::findAll();
    }

    // Retrieve user by ID
    static public function findUserById($id)
    {
        return self::findById($id);
    }

    // Validation for user fields
    protected function validate()
    {
        $this->errors = [];

        if ($this->is_blank($this->NAME)) { // Changed to $this->NAME
            $this->errors[] = "Name cannot be blank.";
        } elseif (strlen($this->NAME) < 3) { // Changed to $this->NAME
            $this->errors[] = "Name must be at least 3 characters.";
        }

        if ($this->is_blank($this->EMAIL)) { // Changed to $this->EMAIL
            $this->errors[] = "Email cannot be blank.";
        } elseif (!$this->has_valid_email_format($this->EMAIL)) { // Changed to $this->EMAIL
            $this->errors[] = "Email must be a valid format.";
        }

        if ($this->is_blank($this->PASSWORD)) { // Changed to $this->PASSWORD
            $this->errors[] = "Password cannot be blank.";
        } elseif (strlen($this->PASSWORD) < 8) { // Changed to $this->PASSWORD
            $this->errors[] = "Password must be at least 8 characters.";
        }

        return $this->errors;
    }

    // Helper functions
    private function is_blank($value)
    {
        return !isset($value) || trim($value) === '';
    }

    private function has_valid_email_format($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

?>