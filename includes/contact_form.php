<?php
// Contact form handler

// Set header to prevent direct access
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 403 Forbidden');
    echo 'Access forbidden';
    exit;
}

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
];

try {
    // Validate required fields
    $required_fields = ['name', 'email', 'subject', 'message'];
    $missing_fields = [];

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }

    if (!empty($missing_fields)) {
        $response['message'] = 'Required fields missing: ' . implode(', ', $missing_fields);
        echo json_encode($response);
        exit;
    }

    // Sanitize inputs
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $subject = filter_var($_POST['subject'], FILTER_SANITIZE_STRING);
    $message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email address';
        echo json_encode($response);
        exit;
    }

    // Email sending configuration
    $to_emails = ['info@palantiri.in', 'hr@palantiri.in'];
    $headers = "From: $name <$email>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // Prepare email content
    $email_content = "Name: $name\n";
    $email_content .= "Email: $email\n";
    $email_content .= "Subject: $subject\n\n";
    $email_content .= "Message:\n$message";

    // Send emails to both recipients
    $mail_sent = true;
    foreach ($to_emails as $recipient) {
        if (!mail($recipient, "Contact Form: $subject", $email_content, $headers)) {
            $mail_sent = false;
        }
    }
    
    if (!$mail_sent) {
        $response['message'] = 'There was an issue sending your message. Please try again later.';
        echo json_encode($response);
        exit;
    }

    // Set success response
    $response['success'] = true;
    $response['message'] = 'Thank you for your message. We will get back to you soon.';

    // Log the submission (for demonstration - in production, use proper logging)
    $log_entry = date('Y-m-d H:i:s') . " - Contact form submission from: $name ($email)\n";
    file_put_contents('../logs/contact_form.log', $log_entry, FILE_APPEND);

} catch (Exception $e) {
    $response['message'] = 'An error occurred while processing your request.';
    // Log the error (for demonstration)
    error_log($e->getMessage());
}

// Return response as JSON
header('Content-Type: application/json');
echo json_encode($response);
exit;
