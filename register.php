<?php
require_once 'config.php';

$error = '';
$success = '';

// Assuming these variables are only set via Google flow, 
// they can be removed or reset if the Google flow is removed.
// $google_prompt = false; 
// $google_email = '';
// $google_name = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        $conn = getDBConnection();
        
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email already registered';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);
            
            if ($stmt->execute()) {
                // Auto-login after registration
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['user_name'] = $name;
                redirect('index.php');
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - E-commerce</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .container {
            max-width: 450px;
            margin: 80px auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 { margin-bottom: 30px; color: #333; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; color: #555; font-weight: bold; }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        .btn:hover { background: #2980b9; }
        .error { padding: 10px; background: #f8d7da; color: #721c24; border-radius: 4px; margin-bottom: 20px; }
        .success { padding: 10px; background: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 20px; }
        .info-box {
            padding: 15px;
            background: #fff3cd;
            color: #856404;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #ffc107;
        }
        .info-box strong { display: block; margin-bottom: 5px; }
        .link { text-align: center; margin-top: 20px; }
        .link a { color: #3498db; text-decoration: none; }
        .link a:hover { text-decoration: underline; }
        /* Removed .divider, .google-btn, and .google-icon CSS */
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .container {
                margin: 20px;
                padding: 20px;
                max-width: 100%;
            }
            h2 {
                font-size: 22px;
            }
            input {
                padding: 10px;
                font-size: 16px; /* Prevents zoom on iOS */
            }
            .btn {
                padding: 14px;
                font-size: 16px;
            }
            /* Removed google-btn mobile CSS */
        }
        
        @media (max-width: 480px) {
            .container {
                margin: 10px;
                padding: 15px;
            }
            h2 {
                font-size: 20px;
            }
            .form-group {
                margin-bottom: 15px;
            }
            label {
                font-size: 14px;
            }
            .info-box,
            .error,
            .success {
                font-size: 13px;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Create Account</h2>
        
        <?php // Removed Google-specific info-box check here ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required minlength="6">
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required minlength="6">
            </div>
            <button type="submit" class="btn">Register</button>
        </form>
        
        <div class="link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>