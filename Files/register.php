<?php
/**
 * User Registration
 * user/register.php
 */

require_once '../config/database.php';
require_once '../config/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        // Get form data
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $first_name = sanitize_input($_POST['first_name'] ?? '');
        $last_name = sanitize_input($_POST['last_name'] ?? '');

        // Validate inputs
        if (empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
            $error = 'All fields are required.';
        } elseif (!is_valid_email($email)) {
            $error = 'Invalid email format.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } else {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = 'Email already registered. Please login or use a different email.';
            } else {
                // Hash password and insert user
                $hashed_password = hash_password($password);
                $role = 'customer';

                $insert_stmt = $conn->prepare(
                    "INSERT INTO users (email, password, first_name, last_name, role) 
                     VALUES (?, ?, ?, ?, ?)"
                );
                $insert_stmt->bind_param("sssss", $email, $hashed_password, $first_name, $last_name, $role);

                if ($insert_stmt->execute()) {
                    $success = 'Registration successful! Redirecting to login...';
                    header('refresh:2; url=login.php');
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    }
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .register-container { max-width: 400px; margin: 60px auto; }
        .card { border: none; border-radius: 10px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); }
        .btn-register { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
        .btn-register:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="card">
            <div class="card-body p-5">
                <h1 class="text-center mb-4">Create Account</h1>
                
                <?php 
                if ($error) display_error($error);
                if ($success) display_success($success);
                ?>

                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name *</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>

                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name *</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password (min 6 characters) *</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>

                    <button type="submit" class="btn btn-register btn-lg w-100 text-white mb-3">
                        Register
                    </button>
                </form>

                <p class="text-center">
                    Already have an account? <a href="login.php" class="text-decoration-none">Login here</a>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
