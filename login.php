<?php
require_once 'config.php';

$error = '';
$user_not_found = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $login_type = $_POST['login_type']; // 'user' or 'admin'
    
    $conn = getDBConnection();
    
    if ($login_type == 'admin') {
        // Admin Login
        $stmt = $conn->prepare("SELECT id, email, password FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_email'] = $admin['email'];
                $stmt->close();
                $conn->close();
                redirect('admin/dashboard.php');
            } else {
                $error = 'Invalid password';
            }
        } else {
            $user_not_found = true;
            $error = 'No admin account found with this email';
        }
    } else {
        // User Login
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $stmt->close();
                $conn->close();
                redirect('index.php');
            } else {
                $error = 'Invalid password';
            }
        } else {
            $user_not_found = true;
            $error = 'No user account found with this email';
        }
    }
    
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-commerce</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .container {
            max-width: 450px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 { margin-bottom: 10px; color: #333; text-align: center; font-size: 24px; }
        .subtitle {
            text-align: center;
            color: #7f8c8d;
            margin-bottom: 25px;
            font-size: 14px;
        }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #555; font-weight: bold; font-size: 14px; }
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .login-type-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .login-type-option {
            flex: 1;
            padding: 15px 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s;
            background: white;
        }
        .login-type-option:hover {
            border-color: #3498db;
            background: #f8f9fa;
        }
        .login-type-option.active {
            border-color: #3498db;
            background: #ebf5fb;
        }
        .login-type-option input[type="radio"] {
            display: none;
        }
        .login-type-icon {
            font-size: 28px;
            margin-bottom: 5px;
        }
        .login-type-label {
            font-weight: bold;
            color: #2c3e50;
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
        .error { 
            padding: 12px; 
            background: #f8d7da; 
            color: #721c24; 
            border-radius: 4px; 
            margin-bottom: 15px;
            font-size: 14px;
        }
        .info-box {
            padding: 12px;
            background: #fff3cd;
            color: #856404;
            border-radius: 4px;
            margin-bottom: 15px;
            border-left: 4px solid #ffc107;
            font-size: 14px;
        }
        .info-box strong { display: block; margin-bottom: 5px; }
        .info-box a {
            color: #856404;
            font-weight: bold;
            text-decoration: underline;
        }
        .link { 
            text-align: center; 
            margin-top: 20px;
            font-size: 14px;
        }
        .link a { color: #3498db; text-decoration: none; }
        .link a:hover { text-decoration: underline; }
        /* Removed .divider and .google-btn CSS */
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .container {
                margin: 20px;
                padding: 20px;
                max-width: 100%;
            }
            h2 { font-size: 22px; }
            .login-type-selector {
                gap: 8px;
            }
            .login-type-option {
                padding: 12px 8px;
            }
            .login-type-icon {
                font-size: 24px;
                margin-bottom: 3px;
            }
            .login-type-label {
                font-size: 13px;
            }
            input, select {
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
            h2 { font-size: 20px; }
            .subtitle { font-size: 13px; }
            .login-type-icon { font-size: 22px; }
            .login-type-label { font-size: 12px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <p class="subtitle">Sign in to your account</p>
        
        <?php if ($user_not_found): ?>
            <div class="info-box">
                <strong>⚠️ Account Not Found</strong>
                It looks like you don't have an account with this email. 
                <?php if (!isset($_POST['login_type']) || $_POST['login_type'] != 'admin'): ?>
                    <a href="register.php">Click here to register</a> and create a new account.
                <?php endif; ?>
            </div>
        <?php elseif ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" id="loginForm">
            <div class="login-type-selector">
                <label class="login-type-option active" id="userOption">
                    <input type="radio" name="login_type" value="user" checked>
                    <div class="login-type-icon">👤</div>
                    <div class="login-type-label">User</div>
                </label>
                <label class="login-type-option" id="adminOption">
                    <input type="radio" name="login_type" value="admin">
                    <div class="login-type-icon">🔐</div>
                    <div class="login-type-label">Admin</div>
                </label>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn" id="loginBtn">Login</button>
        </form>
        
        <div class="link" id="registerLink">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
    
    <script>
        const userOption = document.getElementById('userOption');
        const adminOption = document.getElementById('adminOption');
        const registerLink = document.getElementById('registerLink');
        const loginBtn = document.getElementById('loginBtn');
        
        // Toggle login type
        userOption.addEventListener('click', function() {
            userOption.classList.add('active');
            adminOption.classList.remove('active');
            registerLink.style.display = 'block';
            loginBtn.textContent = 'Login as User';
        });
        
        adminOption.addEventListener('click', function() {
            adminOption.classList.add('active');
            userOption.classList.remove('active');
            registerLink.style.display = 'none';
            loginBtn.textContent = 'Login as Admin';
        });
        
        // Initialize
        registerLink.style.display = 'block';
        loginBtn.textContent = 'Login as User';
    </script>
</body>
</html>