<?php
session_start();
include('../includes/config.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = [];

if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $message[] = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message[] = 'Email is not valid.';
    } else {
        // Prepare SQL to check both tenants and landlords
        $query = "SELECT 'tenant' AS role, tenant_id AS user_id, name, email, password FROM tenants WHERE email = ? 
                  UNION 
                  SELECT 'landlord' AS role, landlord_id AS user_id, name, email, password FROM landlords WHERE email = ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $email, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                // Set session variables based on user role
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['name'] = $row['name'];

                if ($row['role'] == 'tenant') {
                    $_SESSION['tenant_id'] = $row['user_id']; // Set tenant session
                } else {
                    $_SESSION['landlord_id'] = $row['user_id']; // Set landlord session
                }

                // Update last_login timestamp
                $updateQuery = "UPDATE " . ($row['role'] == 'tenant' ? 'tenants' : 'landlords') . " SET last_login = NOW() WHERE email = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bind_param("s", $email);
                $updateStmt->execute();

                // Redirect based on role
                $loc = ($row['role'] == 'tenant') ? "../homepage/home.php" : "../landlord/home.php";
                header("Location: $loc");
                exit();
            } else {
                $message[] = 'Incorrect password.';
            }
        } else {
            $message[] = 'Email not registered.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartLease Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #795548;
            --primary-dark: #5D4037;
            --primary-light: #D7CCC8;
            --accent: #FF9800;
            --text-dark: #3E2723;
            --text-light: #FFFFFF;
            --error: #C62828;
            --success: #2E7D32;
            --gray-light: #F5F5F5;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #8D6E63, #5D4037);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            color: var(--text-dark);
        }
        
        .container {
            width: 100%;
            max-width: 420px;
            background: #fff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(to right, var(--accent), var(--primary));
        }
        
        .container header {
            font-size: 2.2rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 35px;
            color: var(--primary-dark);
            letter-spacing: 0.5px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .logo img {
            height: 60px;
            width: auto;
        }
        
        .field {
            margin-bottom: 25px;
            position: relative;
        }
        
        .field input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            font-size: 1rem;
            border: 2px solid var(--primary-light);
            border-radius: 8px;
            outline: none;
            transition: all 0.3s;
            background-color: var(--gray-light);
        }
        
        .field i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            font-size: 18px;
        }
        
        .field input:focus {
            border-color: var(--primary);
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(121, 85, 72, 0.2);
        }
        
        .field input::placeholder {
            color: #9E9E9E;
        }
        
        .btn {
            width: 100%;
            padding: 16px;
            font-size: 1rem;
            color: var(--text-light);
            background: var(--primary);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn:hover {
            background: var(--primary-dark);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }
        
        .btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
        
        .links-center {
            text-align: center;
            margin-top: 25px;
            color: var(--primary);
            font-size: 0.95rem;
        }
        
        .links-center a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
            margin-left: 5px;
        }
        
        .links-center a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        .message {
            padding: 14px;
            margin: 0 0 25px 0;
            border-radius: 8px;
            text-align: center;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .message.error {
            background-color: #FFEBEE;
            color: var(--error);
            border: 1px solid #FFCDD2;
        }
        
        .message.error i {
            margin-right: 8px;
            font-size: 16px;
        }
        
        .forgot-password {
            text-align: right;
            margin-bottom: 20px;
        }
        
        .forgot-password a {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s;
        }
        
        .forgot-password a:hover {
            color: var(--accent);
            text-decoration: underline;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 30px 0;
        }
        
        .divider::before, .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background-color: var(--primary-light);
        }
        
        .divider span {
            padding: 0 15px;
            color: #9E9E9E;
            font-size: 0.9rem;
        }
        
        .social-login {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .social-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 20px;
            color: white;
        }
        
        .social-btn.google {
            background-color: #DB4437;
        }
        
        .social-btn.facebook {
            background-color: #4267B2;
        }
        
        .social-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 30px 20px;
                max-width: 90%;
                border-radius: 12px;
            }
            
            .container header {
                font-size: 1.8rem;
                margin-bottom: 25px;
            }
            
            .field input {
                padding: 12px 12px 12px 40px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <!-- You can add your logo here -->
            <i class="fas fa-home" style="font-size: 45px; color: var(--primary);"></i>
        </div>
        <form action="login.php" method="post">
            <header>Welcome Back</header>

            <!-- Display error messages -->
            <?php if (!empty($message)): ?>
                <?php foreach ($message as $msg): ?>
                    <div class="message error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($msg); ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="field">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Email Address" required>
            </div>

            <div class="field">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <div class="forgot-password">
                <a href="#">Forgot Password?</a>
            </div>

            <div class="field">
                <input type="submit" class="btn" name="submit" value="Log In">
            </div>

            <div class="divider">
                <span>OR</span>
            </div>

        

            <div class="links-center">
                New to SmartLease?<a href="registerchoice.php">Create Account</a>
            </div>
        </form>
    </div>
</body>
</html>