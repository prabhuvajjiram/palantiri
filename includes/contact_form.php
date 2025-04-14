<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1); // Temporarily enable error display to debug
ini_set('log_errors', 1);
ini_set('error_log', '../logs/php_errors.log');

// Create logs directory if it doesn't exist
$log_dir = '../logs/';
if (!file_exists($log_dir)) {
    $dir_created = mkdir($log_dir, 0777, true); // More permissive permissions
    if (!$dir_created) {
        // Try to output error directly if log creation fails
        echo "error - Failed to create log directory. Please check server permissions.";
        exit;
    }
}

// Initialize log file
$log_file = $log_dir . 'contact_form.log';
try {
    $log_content = date('Y-m-d H:i:s') . " - Contact form submission initiated\n";
    if (!file_put_contents($log_file, $log_content, FILE_APPEND)) {
        // Try to output error directly if log writing fails
        echo "error - Failed to write to log file. Please check file permissions.";
        exit;
    }

    // Debug output - will be visible in network tab
    // echo "<!-- Debug: Log initialized successfully at $log_file -->\n";
} catch (Exception $e) {
    echo "error - Exception during log initialization: " . $e->getMessage();
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Log the POST data
    try {
        $log_content = date('Y-m-d H:i:s') . " - POST data received: " . print_r($_POST, true) . "\n";
        file_put_contents($log_file, $log_content, FILE_APPEND);
        
        // Debug output
        // echo "<!-- Debug: POST data logged -->\n";
    } catch (Exception $e) {
        echo "error - Exception logging POST data: " . $e->getMessage();
        exit;
    }
    
    // Verify required fields exist
    if (!isset($_POST["name"]) || !isset($_POST["email"]) || !isset($_POST["subject"]) || !isset($_POST["message"])) {
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Error: Missing required fields\n", FILE_APPEND);
        echo "error - Missing required fields";
        exit;
    }
    
    $name = $_POST["name"];
    $email = $_POST["email"];
    $subject = $_POST["subject"];
    $messageContent = $_POST["message"];

    // Log the extracted data
    $log_content = date('Y-m-d H:i:s') . " - Extracted data: Name=$name, Email=$email, Subject=$subject\n";
    file_put_contents($log_file, $log_content, FILE_APPEND);

    // You should add more validation and sanitization here for security.
    $name = htmlspecialchars($name);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $subject = htmlspecialchars($subject);
    $messageContent = htmlspecialchars($messageContent);

    // Log after sanitization
    $log_content = date('Y-m-d H:i:s') . " - After sanitization: Name=$name, Email=$email, Subject=$subject\n";
    file_put_contents($log_file, $log_content, FILE_APPEND);

    $to = "info@palantiri.in"; // Primary recipient
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    $message = "<html><body>";
    $message .= "<h1>Palantiri - Website Contact Form</h1>";
    $message .= "<p><strong>Name:</strong> $name</p>";
    $message .= "<p><strong>Email:</strong> $email</p>";
    $message .= "<p><strong>Subject:</strong> $subject</p>";
    $message .= "<p><strong>Message:</strong></p>";
    $message .= "<p>$messageContent</p>";
    $message .= "</body></html>";

    // Log the email sending attempt
    $log_content = date('Y-m-d H:i:s') . " - Attempting to send email to: $to\n";
    file_put_contents($log_file, $log_content, FILE_APPEND);

    // Attempt to send email and log the result
    $mail_result = mail($to, "Contact Form: $subject", $message, $headers);
    $log_content = date('Y-m-d H:i:s') . " - Mail function returned: " . ($mail_result ? "SUCCESS" : "FAILURE") . "\n";
    file_put_contents($log_file, $log_content, FILE_APPEND);

    if ($mail_result) {
        // Try to send to hr as well
        $cc_result = mail("hr@palantiri.in", "Contact Form: $subject", $message, $headers);
        $log_content = date('Y-m-d H:i:s') . " - CC Mail to hr@palantiri.in returned: " . ($cc_result ? "SUCCESS" : "FAILURE") . "\n";
        file_put_contents($log_file, $log_content, FILE_APPEND);
        
        // Success - Just output the word "success" with no HTML or whitespace
        header('Content-Type: text/plain');
        echo "success";
        exit;
    } else {
        // Log the failure with additional info
        $log_content = date('Y-m-d H:i:s') . " - Mail sending failed. Additional info:\n";
        $log_content .= "PHP mail.log may contain more details.\n";
        $log_content .= "PHP version: " . phpversion() . "\n";
        $log_content .= "Server software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
        file_put_contents($log_file, $log_content, FILE_APPEND);
        
        // Error - Just output the word "error" with no HTML or whitespace
        header('Content-Type: text/plain');
        echo "error";
        exit;
    }
} else {
    $log_content = date('Y-m-d H:i:s') . " - Invalid request method: " . $_SERVER["REQUEST_METHOD"] . "\n";
    file_put_contents($log_file, $log_content, FILE_APPEND);
    echo "error";
}
?>
