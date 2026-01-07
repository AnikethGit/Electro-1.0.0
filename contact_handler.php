<?php
/**
 * Contact Form Handler
 * Processes contact form submissions and sends emails
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

try {
    // Validate required fields
    $required_fields = ['name', 'email', 'subject', 'message'];
    $errors = [];
    $data = [];

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst($field) . ' is required';
        } else {
            $data[$field] = sanitize($_POST[$field]);
        }
    }

    if (!empty($errors)) {
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit();
    }

    // Validate email
    if (!is_valid_email($data['email'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit();
    }

    // Optional phone
    $data['phone'] = sanitize($_POST['phone'] ?? '');

    // Validate message length
    if (strlen($data['message']) < 10) {
        echo json_encode(['success' => false, 'message' => 'Message must be at least 10 characters long']);
        exit();
    }

    if (strlen($data['message']) > 5000) {
        echo json_encode(['success' => false, 'message' => 'Message must not exceed 5000 characters']);
        exit();
    }

    // Save to database
    $stmt = $pdo->prepare(
        "INSERT INTO contacts (name, email, phone, subject, message, created_at) 
         VALUES (?, ?, ?, ?, ?, NOW())"
    );
    $stmt->execute([
        $data['name'],
        $data['email'],
        $data['phone'],
        $data['subject'],
        $data['message']
    ]);

    // Send email to admin
    $admin_email = 'admin@electro.com';
    $subject = 'New Contact Form Submission: ' . htmlspecialchars($data['subject']);
    
    $email_body = "New contact form submission from Electro website:\n\n";
    $email_body .= "Name: " . $data['name'] . "\n";
    $email_body .= "Email: " . $data['email'] . "\n";
    if (!empty($data['phone'])) {
        $email_body .= "Phone: " . $data['phone'] . "\n";
    }
    $email_body .= "Subject: " . $data['subject'] . "\n\n";
    $email_body .= "Message:\n" . $data['message'] . "\n\n";
    $email_body .= "---\n";
    $email_body .= "Please reply to: " . $data['email'];
    
    $headers = "From: " . $admin_email . "\r\n";
    $headers .= "Reply-To: " . $data['email'] . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // Send email
    @mail($admin_email, $subject, $email_body, $headers);
    
    // Send confirmation email to user
    $user_subject = 'We received your message - Electro';
    $user_body = "Hi " . $data['name'] . ",\n\n";
    $user_body .= "Thank you for contacting Electro. We have received your message and will get back to you as soon as possible.\n\n";
    $user_body .= "Your message:\n";
    $user_body .= "Subject: " . $data['subject'] . "\n\n";
    $user_body .= $data['message'] . "\n\n";
    $user_body .= "Best regards,\nElectro Team";
    
    $user_headers = "From: " . $admin_email . "\r\n";
    $user_headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    @mail($data['email'], $user_subject, $user_body, $user_headers);
    
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your message! We will get back to you shortly.'
    ]);
    
} catch (Exception $e) {
    error_log('Contact form error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your request. Please try again later.'
    ]);
}

?>