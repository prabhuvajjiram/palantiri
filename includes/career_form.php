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
$log_file = $log_dir . 'career_form.log';
$log_content = date('Y-m-d H:i:s') . " - Career form submission initiated\n";
file_put_contents($log_file, $log_content, FILE_APPEND);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Log the POST data (excluding resume binary data for clarity)
    $post_data = $_POST;
    if (isset($post_data['resume'])) {
        $post_data['resume'] = '[BINARY DATA]';
    }
    $log_content = date('Y-m-d H:i:s') . " - POST data received: " . print_r($post_data, true) . "\n";
    file_put_contents($log_file, $log_content, FILE_APPEND);
    
    // Get basic form data
    $name = $_POST["name"];
    $email = $_POST["email"];
    $position = $_POST["position"];
    $phone = isset($_POST["phone"]) ? $_POST["phone"] : "";
    $skills = $_POST["skills"];
    $message = isset($_POST["message"]) ? $_POST["message"] : "";

    // Log extracted data
    $log_content = date('Y-m-d H:i:s') . " - Extracted data: Name=$name, Email=$email, Position=$position\n";
    file_put_contents($log_file, $log_content, FILE_APPEND);

    // Basic validation and sanitization
    $name = htmlspecialchars($name);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $position = htmlspecialchars($position);
    $phone = htmlspecialchars($phone);
    $skills = htmlspecialchars($skills);
    $message = htmlspecialchars($message);

    // File upload handling
    $resume_info = "";
    $upload_error = "";
    
    // Log file upload attempt
    if (isset($_FILES['resume'])) {
        $log_content = date('Y-m-d H:i:s') . " - Resume upload attempted. File info: " . 
                       "Name=" . $_FILES['resume']['name'] . 
                       ", Size=" . $_FILES['resume']['size'] . 
                       ", Type=" . $_FILES['resume']['type'] . 
                       ", Error=" . $_FILES['resume']['error'] . "\n";
        file_put_contents($log_file, $log_content, FILE_APPEND);
    } else {
        $log_content = date('Y-m-d H:i:s') . " - No resume file in request\n";
        file_put_contents($log_file, $log_content, FILE_APPEND);
    }
    
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
        // Set maximum file size (5MB)
        $max_size = 5 * 1024 * 1024;
        
        // Check file size
        if ($_FILES['resume']['size'] > $max_size) {
            $upload_error = "Resume file is too large (max 5MB)";
            $log_content = date('Y-m-d H:i:s') . " - Error: $upload_error\n";
            file_put_contents($log_file, $log_content, FILE_APPEND);
        } else {
            // Get file extension
            $file_extension = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
            
            // Check if extension is allowed (PDF, DOC, DOCX)
            $allowed_extensions = array('pdf', 'doc', 'docx');
            
            if (!in_array($file_extension, $allowed_extensions)) {
                $upload_error = "Invalid file type. Only PDF, DOC, and DOCX files are allowed.";
                $log_content = date('Y-m-d H:i:s') . " - Error: $upload_error\n";
                file_put_contents($log_file, $log_content, FILE_APPEND);
            } else {
                // Create unique filename to prevent overwriting
                $upload_dir = '../uploads/resumes/';
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    $dir_created = mkdir($upload_dir, 0777, true);
                    $log_content = date('Y-m-d H:i:s') . " - Created upload directory: " . ($dir_created ? "SUCCESS" : "FAILURE") . "\n";
                    file_put_contents($log_file, $log_content, FILE_APPEND);
                }
                
                // Create safe filename
                $new_filename = 'resume_' . date('Ymd_His') . '_' . preg_replace('/[^A-Za-z0-9]/', '', $name) . '.' . $file_extension;
                $target_file = $upload_dir . $new_filename;
                
                // Log the upload attempt
                $log_content = date('Y-m-d H:i:s') . " - Attempting to upload file to: $target_file\n";
                file_put_contents($log_file, $log_content, FILE_APPEND);
                
                // Upload file
                if (move_uploaded_file($_FILES['resume']['tmp_name'], $target_file)) {
                    $resume_info = "Resume uploaded successfully: " . $new_filename;
                    $log_content = date('Y-m-d H:i:s') . " - $resume_info\n";
                    file_put_contents($log_file, $log_content, FILE_APPEND);
                } else {
                    $upload_error = "Failed to upload resume file.";
                    $log_content = date('Y-m-d H:i:s') . " - Error: $upload_error. PHP upload error code: " . $_FILES['resume']['error'] . "\n";
                    file_put_contents($log_file, $log_content, FILE_APPEND);
                    
                    // Additional upload error info
                    $log_content = date('Y-m-d H:i:s') . " - Upload directory permissions: " . substr(sprintf('%o', fileperms($upload_dir)), -4) . "\n";
                    file_put_contents($log_file, $log_content, FILE_APPEND);
                }
            }
        }
    } else if (isset($_FILES['resume']) && $_FILES['resume']['error'] > 0) {
        // Handle errors
        switch ($_FILES['resume']['error']) {
            case 1: // UPLOAD_ERR_INI_SIZE
                $upload_error = "The uploaded file exceeds the upload_max_filesize directive in php.ini.";
                break;
            case 2: // UPLOAD_ERR_FORM_SIZE
                $upload_error = "The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form.";
                break;
            case 3: // UPLOAD_ERR_PARTIAL
                $upload_error = "The uploaded file was only partially uploaded.";
                break;
            case 4: // UPLOAD_ERR_NO_FILE
                $upload_error = "No file was uploaded.";
                break;
            case 6: // UPLOAD_ERR_NO_TMP_DIR
                $upload_error = "Missing a temporary folder.";
                break;
            case 7: // UPLOAD_ERR_CANT_WRITE
                $upload_error = "Failed to write file to disk.";
                break;
            case 8: // UPLOAD_ERR_EXTENSION
                $upload_error = "A PHP extension stopped the file upload.";
                break;
            default:
                $upload_error = "Unknown upload error.";
        }
        $log_content = date('Y-m-d H:i:s') . " - File upload error: $upload_error\n";
        file_put_contents($log_file, $log_content, FILE_APPEND);
    }

    // Email configuration
    $to = "hr@palantiri.in"; // Primary recipient
    $cc = "info@palantiri.in"; // CC recipient
    
    $headers = "From: $email\r\n";
    $headers .= "Cc: $cc\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    // Prepare email content
    $subject = "Job Application: $position";
    
    $message_body = "<html><body>";
    $message_body .= "<h1>Palantiri - Job Application Form</h1>";
    $message_body .= "<p><strong>Position Applied For:</strong> $position</p>";
    $message_body .= "<p><strong>Name:</strong> $name</p>";
    $message_body .= "<p><strong>Email:</strong> $email</p>";
    
    if (!empty($phone)) {
        $message_body .= "<p><strong>Phone:</strong> $phone</p>";
    }
    
    $message_body .= "<p><strong>Skills:</strong> $skills</p>";
    
    if (!empty($message)) {
        $message_body .= "<p><strong>Additional Information:</strong></p>";
        $message_body .= "<p>$message</p>";
    }
    
    if (!empty($resume_info)) {
        $message_body .= "<p><strong>Resume:</strong> $resume_info</p>";
    }
    
    if (!empty($upload_error)) {
        $message_body .= "<p><strong>Resume Upload Error:</strong> $upload_error</p>";
        $message_body .= "<p>Please ask the applicant to send their resume directly to hr@palantiri.in</p>";
    }
    
    $message_body .= "</body></html>";

    // Log email sending attempt
    $log_content = date('Y-m-d H:i:s') . " - Attempting to send email to: $to and Cc: $cc\n";
    file_put_contents($log_file, $log_content, FILE_APPEND);

    // Send email
    $mail_result = mail($to, $subject, $message_body, $headers);
    $log_content = date('Y-m-d H:i:s') . " - Mail function returned: " . ($mail_result ? "SUCCESS" : "FAILURE") . "\n";
    file_put_contents($log_file, $log_content, FILE_APPEND);
    
    if ($mail_result) {
        echo "success";
    } else {
        // Log the failure with additional info
        $log_content = date('Y-m-d H:i:s') . " - Mail sending failed. Additional info:\n";
        $log_content .= "PHP mail.log may contain more details.\n";
        $log_content .= "PHP version: " . phpversion() . "\n";
        $log_content .= "Server software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
        file_put_contents($log_file, $log_content, FILE_APPEND);
        
        echo "error";
    }
} else {
    $log_content = date('Y-m-d H:i:s') . " - Invalid request method: " . $_SERVER["REQUEST_METHOD"] . "\n";
    file_put_contents($log_file, $log_content, FILE_APPEND);
    echo "error";
}
?>
