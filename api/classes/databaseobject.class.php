<?php

class DatabaseObject {
    public $errors = [];

    static protected $database; // PDO instance

    // Set the database connection
    static public function setDatabase($pdo)
    {
        self::$database = $pdo;
        // Added debug here to confirm what's being set
        error_log("DEBUG: DatabaseObject::setDatabase - Type set: " . (is_object(self::$database) ? get_class(self::$database) : gettype(self::$database)));
    }

    // Execute a query with prepared statements
    static protected function executeQuery($sql, $params = [])
    {
        // Debug line at the very beginning of executeQuery
        error_log("DEBUG in executeQuery (before prepare): Type of self::\$database: " . (is_object(self::$database) ? get_class(self::$database) : gettype(self::$database)) . "; SQL: " . $sql);

        // Ensure $database is a valid PDO object before attempting to use it
        if (! (self::$database instanceof PDO)) {
            $type = is_object(self::$database) ? get_class(self::$database) : gettype(self::$database);
            throw new Exception("Database connection is not a valid PDO object. Type found: {$type}");
        }

        $stmt = self::$database->prepare($sql);
        if (!$stmt->execute($params)) {
            throw new Exception("Database query failed: " . implode(", ", $stmt->errorInfo()));
        }
        return $stmt;
    }

    // Fetch records from a SQL query
    static public function findBySql($sql, $params = [])
    {
        $stmt = self::executeQuery($sql, $params);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([static::class, 'instantiate'], $records);
    }

    // Fetch all records
    static public function findAll()
    {
        $sql = "SELECT * FROM " . static::$table_name;
        return static::findBySql($sql);
    }

    // Find a record by ID
    static public function findById($id)
    {
        $sql = "SELECT * FROM " . static::$table_name . " WHERE ID = :id LIMIT 1";
        $stmt = self::executeQuery($sql, ['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? static::instantiate($result) : false;
    }

    // Count all records
    static public function countAll()
    {
        $sql = "SELECT COUNT(*) AS count FROM " . static::$table_name;
        $stmt = self::executeQuery($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    // Create or update the current record
    public function save()
    {
        return isset($this->ID) && $this->ID != "" ? $this->update() : $this->create();
    }

    // Create a new record
    protected function create()
    {
        $attributes = $this->sanitizedAttributes();
        $columns = array_keys($attributes);
        $placeholders = array_map(fn($col) => ":$col", $columns);

        $sql = "INSERT INTO " . static::$table_name . " (";
        $sql .= implode(', ', $columns);
        $sql .= ") VALUES (";
        $sql .= implode(', ', $placeholders);
        $sql .= ")";

        $stmt = self::executeQuery($sql, $attributes);

        if ($stmt) {
            $this->ID = self::$database->lastInsertId();
        }
        return $stmt;
    }

    // Update an existing record
    protected function update()
    {
        $attributes = $this->sanitizedAttributes();
        $attribute_pairs = [];
        foreach ($attributes as $key => $value) {
            $attribute_pairs[] = "$key = :$key";
        }

        $sql = "UPDATE " . static::$table_name . " SET ";
        $sql .= implode(', ', $attribute_pairs);
        $sql .= " WHERE ID = :id";

        $attributes['id'] = $this->ID;

        return self::executeQuery($sql, $attributes);
    }

    // Delete a record by ID
    static public function delete($id)
    {
        $sql = "DELETE FROM " . static::$table_name . " WHERE ID = :id LIMIT 1";
        return self::executeQuery($sql, ['id' => $id]);
    }

    // Merge new attributes into the object
    public function mergeAttributes($args = [])
    {
        foreach ($args as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
            $upperCaseKey = strtoupper($key);
            if (property_exists($this, $upperCaseKey)) {
                $this->$upperCaseKey = $value;
            }
        }
    }

    // Get attributes for the object excluding the ID
    public function attributes()
    {
        $attributes = [];
        foreach (static::$db_columns as $column) {
            if ($column === 'ID') continue;
            if (property_exists($this, $column)) {
                $attributes[$column] = $this->$column;
            } else {
                $lowerCaseColumn = strtolower($column);
                if (property_exists($this, $lowerCaseColumn)) {
                    $attributes[$column] = $lowerCaseColumn;
                } else {
                    $attributes[$column] = null;
                }
            }
        }
        return $attributes;
    }

    // Sanitize attributes before saving to the database
    protected function sanitizedAttributes()
    {
        $sanitized = [];
        foreach ($this->attributes() as $key => $value) {
            $sanitized[$key] = htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
        }
        return $sanitized;
    }

    // Instantiate an object from a record
    static protected function instantiate($record)
    {
        $object = new static;
        foreach ($record as $property => $value) {
            if (property_exists($object, $property)) {
                $object->$property = $value;
            }
            $lowerCaseProperty = strtolower($property);
            if (property_exists($object, $lowerCaseProperty)) {
                $object->$lowerCaseProperty = $value;
            }
        }
        return $object;
    }
}
?>