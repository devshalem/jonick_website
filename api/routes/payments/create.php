<?php
require_once '../../initialize.php';

try {
    ApiHelper::requireMethod('POST');
    $input = ApiHelper::getJsonInput();

    // Required fields
    ApiHelper::validateRequiredFields($input, ['reference', 'user_id', 'job_id', 'amount']);

    $paystackSecretKey = EnvLoader::get('PAYSTACK_SECRET_KEY');
    if (!$paystackSecretKey) {
        throw new Exception('Paystack secret key not configured.');
    }

    $verifyUrl = "https://api.paystack.co/transaction/verify/" . $input['reference'];

    // Verify transaction with Paystack
    $ch = curl_init($verifyUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$paystackSecretKey}",
        "Cache-Control: no-cache"
    ]);
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode !== 200) {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'Could not verify payment with Paystack',
            'http_code' => $httpcode
        ], 500);
    }

    $paystackResponse = json_decode($response, true);

    if (!isset($paystackResponse['status']) || !$paystackResponse['status']) {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'Payment verification failed.',
            'data'    => $paystackResponse
        ], 400);
    }

    $paystackData = $paystackResponse['data'];

    // Check payment status
    if ($paystackData['status'] !== 'success') {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'Payment not successful.',
            'data'    => $paystackData
        ], 400);
    }

    // Check amount integrity (Paystack sends amount in kobo)
    $amountPaid = $paystackData['amount'] / 100;
    if ((float)$amountPaid !== (float)$input['amount']) {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'Amount mismatch detected.',
            'expected_amount' => $input['amount'],
            'received_amount' => $amountPaid
        ], 400);
    }

    // Prevent duplicate payments by reference
    $existingPayment = Payments::findByTransactionId($paystackData['reference']);
    if ($existingPayment) {
        ApiHelper::sendJsonResponse([
            'status'  => 'error',
            'message' => 'Payment with this reference already exists.'
        ], 409);
    }

    // Save payment to DB
    $paymentData = [
        'user_id'        => $input['user_id'],
        'job_id'         => $input['job_id'],
        'amount'         => $input['amount'],
        'method'         => 'paystack',
        'transaction_id' => $paystackData['reference'],
        'status'         => 'success',
        'created_at'     => date('Y-m-d H:i:s'),
        'updated_at'     => date('Y-m-d H:i:s')
    ];

    $payment = new Payments($paymentData);
    if (!$payment->save()) {
        throw new Exception('Failed to save payment record.');
    }

    ApiHelper::sendJsonResponse([
        'status'  => 'success',
        'message' => 'Payment recorded successfully.',
        'payment' => $payment
    ], 200);

} catch (Exception $e) {
    ApiHelper::sendJsonResponse([
        'status'  => 'error',
        'message' => 'Payment processing failed.',
        'error'   => $e->getMessage()
    ], 500);
}
