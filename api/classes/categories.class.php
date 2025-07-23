<?php

class Categories extends DatabaseObject
{
    // Table name
    static protected $table_name = "categories";

    // Database columns
    static protected $db_columns = ['id', 'name', 'description', 'created_at', 'updated_at'];

    // Class properties
    public $id;
    public $name;
    public $description;
    public $created_at;
    public $updated_at;
    public $errors = [];

    // Constructor
    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->name = $args['name'] ?? '';
        $this->description = $args['description'] ?? '';
        $this->created_at = $args['created_at'] ?? null;
        $this->updated_at = $args['updated_at'] ?? null;
    }

    // Create a new category
    static public function createCategory($data)
    {
        $category = new self($data);

        $errors = $category->validate();
        if (!empty($errors)) {
            return ['status' => 'error', 'message' => 'Validation failed', 'errors' => $errors];
        }

        $save_query = $category->save();

        return $save_query
            ? ['status' => 'success', 'message' => 'Category created successfully']
            : ['status' => 'error', 'message' => 'Category creation failed'];
    }

    // Update an existing category
    static public function updateCategory($id, $data)
    {
        $category = self::findById($id);

        if (!$category) {
            return ['status' => 'error', 'message' => 'Category not found'];
        }

        $category->mergeAttributes($data);
        // Ensure $category is an instance of Categories and call validate as an instance method
        if (!($category instanceof self)) {
            return ['status' => 'error', 'message' => 'Invalid category instance'];
        }   

        // Validate the updated category
        $errors = $category->validate();
        
        if (!empty($errors)) {
            return ['status' => 'error', 'message' => 'Validation failed', 'errors' => $errors];
        }

        $update_query = $category->save();

        return $update_query
            ? ['status' => 'success', 'message' => 'Category updated successfully']
            : ['status' => 'error', 'message' => 'Category update failed'];
    }

    // Delete a category
    static public function deleteCategory($id)
    {
        $category = self::findById($id);

        if (!$category) {
            return ['status' => 'error', 'message' => 'Category not found'];
        }

        $delete_query = $category->delete();

        return $delete_query
            ? ['status' => 'success', 'message' => 'Category deleted successfully']
            : ['status' => 'error', 'message' => 'Category deletion failed'];
    }

    // Retrieve all categories
    static public function allCategories()
    {
        return self::findAll();
    }

    // Retrieve a category by ID
    static public function findCategoryById($id)
    {
        return self::findById($id);
    }

    // Validation
    public function validate()
    {
        $this->errors = [];

        if ($this->is_blank($this->name)) {
            $this->errors[] = "Category name cannot be blank.";
        } elseif (strlen($this->name) < 3) {
            $this->errors[] = "Category name must be at least 3 characters.";
        }

        return $this->errors;
    }

    // Helper
    private function is_blank($value)
    {
        return !isset($value) || trim($value) === '';
    }
}
?>
