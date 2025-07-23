<?php

class Payments extends DatabaseObject
{
    // Table name
    static protected $table_name = "payments";

    // Database columns
    static protected $db_columns = ['id', 'booking_id', 'payment_method', 'transaction_id', 'amount', 'status', 'created_at', 'updated_at'];

    // Class properties for each column
    public $id;
    public $booking_id; // Foreign key to bookings table
    public $payment_method; // 'card', 'bank_transfer', etc.
    public $transaction_id; // Unique transaction reference
    public $amount; // Payment amount
    public $status; // 'pending', 'completed', 'failed', etc.
    public $created_at;
    public $updated_at;

    // Constructor
    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->booking_id = $args['booking_id'] ?? null;
        $this->payment_method = $args['payment_method'] ?? 'card';
        $this->transaction_id = $args['transaction_id'] ?? '';
        $this->amount = $args['amount'] ?? 0.00;
        $this->status = $args['status'] ?? 'pending';
        $this->created_at = $args['created_at'] ?? null;
        $this->updated_at = $args['updated_at'] ?? null;
    }

    // Create a payment record
    static public function createPayment($data)
    {
        $payment = new self($data);

        $errors = $payment->validate();
        if (!empty($errors)) {
            return ['status' => 'error', 'message' => 'Validation failed', 'errors' => $errors];
        }

        $save_query = $payment->save();

        return $save_query
            ? ['status' => 'success', 'message' => 'Payment created successfully']
            : ['status' => 'error', 'message' => 'Payment creation failed'];
    }

    // Retrieve all payments
    static public function allPayments()
    {
        return self::findAll();
    }

    // Retrieve payments by booking
    static public function findPaymentsByBooking($booking_id)
    {
        $sql = "SELECT * FROM " . static::$table_name . " WHERE booking_id = :booking_id";
        $stmt = self::executeQuery($sql, ['booking_id' => $booking_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Retrieve payment by transaction ID
    static public function findPaymentByTransactionId($transaction_id)
    {
        $sql = "SELECT * FROM " . static::$table_name . " WHERE transaction_id = :transaction_id";
        $stmt = self::executeQuery($sql, ['transaction_id' => $transaction_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? static::instantiate($result) : null;
    }

    // Update payment status
    static public function updateStatus($id, $status)
    {
        $payment = self::findById($id);

        if (!$payment) {
            return ['status' => 'error', 'message' => 'Payment not found'];
        }

        $payment->status = $status;
        $update_query = $payment->save();

        return $update_query
            ? ['status' => 'success', 'message' => 'Payment status updated successfully']
            : ['status' => 'error', 'message' => 'Payment status update failed'];
    }

    // Get payment by ID
    static public function findPaymentsById($paymentId = null)
    {
        $sql = "
            SELECT  
                payments.id AS payment_id,
                payments.amount AS payment_amount,
                payments.status AS payment_status,
                payments.payment_method AS payment_method,
                payments.created_at AS date_created,
                payments.updated_at AS date_updated,

                users.id AS user_id,
                users.name AS user_name,
                users.email AS user_email,

                jobs.id AS job_id,
                jobs.description AS job_description,
                jobs.status AS job_status
            FROM 
                payments
            JOIN 
                users ON payments.user_id = users.id
            LEFT JOIN 
                jobs ON payments.job_id = jobs.id
        ";

        $params = [];
        if (!is_null($paymentId)) {
            $sql .= " WHERE payments.id = :id";
            $params['id'] = $paymentId;
        }

        $sql .= "
            GROUP BY 
                payments.id,
                users.id,
                jobs.id
            ORDER BY 
                payments.created_at DESC
        ";

        $stmt = Payments::executeQuery($sql, $params);
        $payments = $paymentId ? $stmt->fetch(PDO::FETCH_ASSOC) : $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $payments;
    }

    public static function findByTransactionId($reference)
    {
        $sql = "SELECT * FROM payments WHERE transaction_id = :ref LIMIT 1";
        $stmt = self::executeQuery($sql, ['ref' => $reference]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // Validation for payment fields
    public function validate()
    {
        $this->errors = [];

        if (empty($this->booking_id)) {
            $this->errors[] = "Booking ID is required.";
        }

        if (empty($this->payment_method)) {
            $this->errors[] = "Payment method is required.";
        }

        if (empty($this->transaction_id)) {
            $this->errors[] = "Transaction ID is required.";
        }

        if (!is_numeric($this->amount) || $this->amount <= 0) {
            $this->errors[] = "Amount must be a positive number.";
        }

        return $this->errors;
    }

    // Helper functions
    private function is_blank($value)
    {
        return !isset($value) || trim($value) === '';
    }
}

?>
