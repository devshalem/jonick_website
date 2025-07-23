<?php

class Jobs extends DatabaseObject
{
    // Table name
    static protected $table_name = "jobs";

    // Database columns
    static protected $db_columns = ['id', 'service_id', 'user_id', 'professional_id', 'status', 'description', 'scheduled_date', 'created_at', 'updated_at'];

    // Class properties for each column
    public $id;
    public $service_id;         // Foreign key to services table
    public $user_id;            // Foreign key to users table (buyer who booked the job)
    public $professional_id;    // Foreign key to professionals table (pro assigned to the job)
    public $status;             // 'pending', 'in_progress', 'completed', 'canceled'
    public $description;        // Additional details about the job
    public $scheduled_date;     // Scheduled date and time for the job
    public $created_at;
    public $updated_at;

    // Constructor
    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->service_id = $args['service_id'] ?? null;
        $this->user_id = $args['user_id'] ?? null;
        $this->professional_id = $args['professional_id'] ?? null;
        $this->status = $args['status'] ?? 'pending';
        $this->description = $args['description'] ?? '';
        $this->scheduled_date = $args['scheduled_date'] ?? null;
        $this->created_at = $args['created_at'] ?? null;
        $this->updated_at = $args['updated_at'] ?? null;
    }

    // Validate job data
    public function validate()
    {
        $errors = [];

        if (empty($this->service_id)) {
            $errors[] = "Service ID is required.";
        }
        if (empty($this->user_id)) {
            $errors[] = "User ID is required.";
        }
        if (empty($this->scheduled_date)) {
            $errors[] = "Scheduled date is required.";
        }
        if (!in_array($this->status, ['pending', 'in_progress', 'completed', 'canceled'])) {
            $errors[] = "Invalid status value.";
        }
        // Add more validation as needed

        return $errors;
    }

    // Create a job
    static public function createJob($data)
    {
        $job = new self($data);

        $errors = $job->validate();
        if (!empty($errors)) {
            return ['status' => 'error', 'message' => 'Validation failed', 'errors' => $errors];
        }

        $save_query = $job->save();

        return $save_query
            ? ['status' => 'success', 'message' => 'Job created successfully']
            : ['status' => 'error', 'message' => 'Job creation failed'];
    }

    // Retrieve all jobs
    static public function allJobs()
    {
        return self::findAll();
    }

    // Retrieve jobs by status
    static public function findJobsByStatus($status)
    {
        $sql = "SELECT * FROM " . static::$table_name . " WHERE status = :status";
        $stmt = self::executeQuery($sql, ['status' => $status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Retrieve jobs by professional
    static public function findJobsByProfessional($professionalId)
    {
        $sql = "
            SELECT  
                jobs.id AS job_id,
                jobs.status AS job_status,
                jobs.description AS job_description,
                jobs.scheduled_date AS scheduled_date,
                jobs.created_at AS date_created,
                jobs.updated_at AS date_updated,

                users.id AS user_id,
                users.name AS user_name,
                users.email AS user_email,

                professionals.id AS professional_id,
                professionals.expertise AS professional_expertise,

                services.id AS service_id,
                services.name AS service_name,
                services.description AS service_description
            FROM 
                jobs
            JOIN 
                users ON jobs.user_id = users.id
            LEFT JOIN 
                professionals ON jobs.professional_id = professionals.id
            JOIN 
                services ON jobs.service_id = services.id
            WHERE 
                jobs.professional_id = :professional_id
            ORDER BY 
                jobs.created_at DESC
        ";

        $stmt = self::executeQuery($sql, ['professional_id' => $professionalId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Retrieve jobs by user
    static public function findJobsByUser($user_id)
    {
        $sql = "SELECT * FROM " . static::$table_name . " WHERE user_id = :user_id";
        $stmt = self::executeQuery($sql, ['user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update job status
    static public function updateStatus($id, $status)
    {
        $job = self::findById($id);

        if (!$job) {
            return ['status' => 'error', 'message' => 'Job not found'];
        }

        $job->status = $status;
        $update_query = $job->save();

        return $update_query
            ? ['status' => 'success', 'message' => 'Job status updated successfully']
            : ['status' => 'error', 'message' => 'Job status update failed'];
    }
    
    // Retrieve job by ID
    static public function findJobById($jobId = null)
    {
        // Build SQL
        $sql = "
            SELECT  
                jobs.id AS job_id,
                jobs.status AS job_status,
                jobs.description AS job_description,
                jobs.scheduled_date AS scheduled_date,
                jobs.created_at AS date_created,
                jobs.updated_at AS date_updated,

                users.id AS user_id,
                users.name AS user_name,
                users.email AS user_email,

                professionals.id AS professional_id,
                professionals.expertise AS professional_expertise,

                services.id AS service_id,
                services.name AS service_name,
                services.description AS service_description
            FROM 
                jobs
            JOIN 
                users ON jobs.user_id = users.id
            LEFT JOIN 
                professionals ON jobs.professional_id = professionals.id
            JOIN 
                services ON jobs.service_id = services.id
        ";

        $params = [];
        if (!is_null($jobId)) {
            $sql .= " WHERE jobs.id = :id";
            $params['id'] = $jobId;
        }

        $sql .= "
            GROUP BY 
                jobs.id,
                users.id,
                professionals.id,
                services.id
            ORDER BY 
                jobs.created_at DESC
        ";

        $stmt = self::executeQuery($sql, $params);
        return $jobId ? $stmt->fetch(PDO::FETCH_ASSOC) : $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
