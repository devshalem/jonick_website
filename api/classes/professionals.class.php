<?php

class Professionals extends DatabaseObject
{
    // Table name
    static protected $table_name = "professionals";

    // Database columns
    static protected $db_columns = [
        'id', 'user_id', 'expertise', 'availability', 'status', 'created_at', 'updated_at'
    ];

    // Class properties
    public $id;
    public $user_id;
    public $expertise;
    public $availability;
    public $status;
    public $created_at;
    public $updated_at;

    // Constructor
    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->user_id = $args['user_id'] ?? null;
        $this->expertise = $args['expertise'] ?? '';
        $this->availability = $args['availability'] ?? '';
        $this->status = $args['status'] ?? 'pending';
        $this->created_at = $args['created_at'] ?? date('Y-m-d H:i:s');
        $this->updated_at = $args['updated_at'] ?? date('Y-m-d H:i:s');
    }

    // Apply to become a professional
    static public function apply($data)
    {
        $professional = new self($data);
        $professional->created_at = date('Y-m-d H:i:s');
        $professional->updated_at = date('Y-m-d H:i:s');

        $errors = $professional->validate();
        if (!empty($errors)) {
            return ['status' => 'error', 'message' => 'Validation failed', 'errors' => $errors];
        }

        return $professional->save()
            ? ['status' => 'success', 'message' => 'Application submitted successfully']
            : ['status' => 'error', 'message' => 'Application submission failed'];
    }

    // Approve a professional
    static public function approve($id)
    {
        $professional = self::findById($id);
        if (!$professional) {
            return ['status' => 'error', 'message' => 'Professional not found'];
        }

        $professional->status = 'approved';
        $professional->updated_at = date('Y-m-d H:i:s');
        return $professional->save()
            ? ['status' => 'success', 'message' => 'Professional approved successfully']
            : ['status' => 'error', 'message' => 'Approval failed'];
    }

    // Reject a professional
    static public function reject($id)
    {
        $professional = self::findById($id);
        if (!$professional) {
            return ['status' => 'error', 'message' => 'Professional not found'];
        }

        $professional->status = 'rejected';
        $professional->updated_at = date('Y-m-d H:i:s');
        return $professional->save()
            ? ['status' => 'success', 'message' => 'Professional rejected successfully']
            : ['status' => 'error', 'message' => 'Rejection failed'];
    }

    // Find professionals by ID with joined user details
    static public function findProfessionalsById($professionalId)
    {
        $sql = "
            SELECT 
                professionals.id AS professional_id,
                professionals.expertise AS expertise,
                professionals.status AS status,
                professionals.created_at AS date_created,
                professionals.updated_at AS date_updated,
                users.id AS user_id,
                users.name AS user_name,
                users.email AS user_email,
                users.phone AS user_phone
            FROM professionals
            JOIN users ON professionals.user_id = users.id
        ";

        $params = [];
        if (!is_null($professionalId)) {
            $sql .= " WHERE professionals.id = :id";
            $params['id'] = $professionalId;
        }

        $sql .= " ORDER BY professionals.created_at DESC";

        $stmt = self::executeQuery($sql, $params);
        return $professionalId
            ? $stmt->fetch(PDO::FETCH_ASSOC)
            : $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Validation for professional fields
    public function validate()
    {
        $this->errors = [];

        if (empty($this->user_id)) {
            $this->errors[] = "User ID is required.";
        }
        if ($this->is_blank($this->expertise)) {
            $this->errors[] = "Expertise cannot be blank.";
        }
        if (!in_array($this->status, ['pending', 'approved', 'rejected'])) {
            $this->errors[] = "Invalid status value.";
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
