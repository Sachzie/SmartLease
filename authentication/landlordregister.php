<?php
    include('../includes/config.php');
    session_start();

    $message = []; // Initialize an array for error messages

    if (isset($_POST['submit'])) {
        $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

        // Validate required fields
        if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
            $message[] = 'All fields are required.';
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message[] = 'Invalid email format.';
        }

        // Check if email already exists in the landlord table
        $check_email = mysqli_query($conn, "SELECT * FROM landlords WHERE email = '$email'") or die('Query failed');
        if (mysqli_num_rows($check_email) > 0) {
            $message[] = 'Email already exists.';
        }

        // Check password length
        if (strlen($password) < 8) {
            $message[] = 'Password must be at least 8 characters.';
        }

        // Check if password and confirm password match
        if ($password !== $confirm_password) {
            $message[] = 'Passwords do not match.';
        }

        // If no errors, hash the password and insert into the landlord table
        if (empty($message)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_query = "INSERT INTO landlords (name, email, password) 
                             VALUES ('$full_name', '$email', '$hashed_password')";
                             
            if (mysqli_query($conn, $insert_query)) {
                // Store landlord_id in session
                $_SESSION['landlord_id'] = mysqli_insert_id($conn);
                $_SESSION['role'] = 'landlord';
                $_SESSION['name'] = $full_name;
                header('Location: ../authentication/verification.php');
                exit();
            } else {
                $message[] = 'Registration failed. Please try again.';
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartLease - Landlord Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #795548;
            --primary-dark: #5D4037;
            --primary-light: #D7CCC8;
            --accent: #FF9800;
            --landlord: #4CAF50;
            --landlord-dark: #388E3C;
            --landlord-light: #C8E6C9;
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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            color: var(--text-dark);
            padding: 20px;
        }
        
        .container {
            width: 100%;
            max-width: 450px;
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
            background: linear-gradient(to right, var(--landlord), var(--landlord-dark));
        }
        
        .logo {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .logo i {
            font-size: 45px;
            color: var(--landlord);
        }
        
        .container header {
            font-size: 2.2rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 10px;
            color: var(--primary-dark);
            letter-spacing: 0.5px;
        }
        
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 1rem;
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
        
        .field i.icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--landlord);
            font-size: 18px;
        }
        
        .field .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--primary-dark);
            font-size: 18px;
            transition: color 0.3s;
        }
        
        .field .toggle-password:hover {
            color: var(--accent);
        }
        
        .field input:focus {
            border-color: var(--landlord);
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
        }
        
        .field input::placeholder {
            color: #9E9E9E;
        }
        
        .btn {
            width: 100%;
            padding: 16px;
            font-size: 1rem;
            color: var(--text-light);
            background: var(--landlord);
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
            background: var(--landlord-dark);
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
            color: var(--landlord);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
            margin-left: 5px;
        }
        
        .links-center a:hover {
            color: var(--landlord-dark);
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
        
        .password-requirements {
            margin-top: 10px;
            margin-bottom: 20px;
            background-color: var(--landlord-light);
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 0.85rem;
            color: var(--primary-dark);
        }
        
        .password-requirements h4 {
            font-size: 0.95rem;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            color: var(--landlord-dark);
        }
        
        .password-requirements h4 i {
            margin-right: 5px;
        }
        
        .requirements-list {
            list-style-type: none;
            padding-left: 10px;
        }
        
        .requirements-list li {
            margin: 4px 0;
            display: flex;
            align-items: center;
        }
        
        .requirements-list li i {
            margin-right: 8px;
            font-size: 12px;
            color: var(--landlord);
        }
        
        .steps {
            display: flex;
            margin-bottom: 30px;
            justify-content: space-between;
        }
        
        .step {
            flex: 1;
            text-align: center;
            font-size: 0.8rem;
            position: relative;
        }
        
        .step::after {
            content: "";
            position: absolute;
            top: 25px;
            left: 50%;
            height: 3px;
            width: 100%;
            background-color: var(--primary-light);
            z-index: 0;
        }
        
        .step:last-child::after {
            display: none;
        }
        
        .step-circle {
            width: 30px;
            height: 30px;
            background-color: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            position: relative;
            z-index: 1;
            color: #fff;
            font-weight: bold;
        }
        
        .step.active .step-circle {
            background-color: var(--landlord);
        }
        
        .step-label {
            color: #777;
        }
        
        .step.active .step-label {
            color: var(--landlord);
            font-weight: 600;
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 30px 20px;
                border-radius: 12px;
            }
            
            .container header {
                font-size: 1.8rem;
                margin-bottom: 20px;
            }
            
            .field input {
                padding: 12px 12px 12px 40px;
            }
            
            .steps {
                margin-bottom: 20px;
            }
            
            .step-circle {
                width: 25px;
                height: 25px;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <i class="fas fa-building"></i>
        </div>
        <form action="" method="post">
            <header>Landlord Registration</header>
            <p class="subtitle">Create your account to list and manage properties</p>
            
            <div class="steps">
                <div class="step active">
                    <div class="step-circle">1</div>
                    <div class="step-label">Account</div>
                </div>
                <div class="step">
                    <div class="step-circle">2</div>
                    <div class="step-label">Verification</div>
                </div>
                <div class="step">
                    <div class="step-circle">3</div>
                    <div class="step-label">Complete</div>
                </div>
            </div>

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
                <i class="fas fa-user icon"></i>
                <input type="text" name="full_name" placeholder="Full Name" required>
            </div>
            
            <div class="field">
                <i class="fas fa-envelope icon"></i>
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            
            <div class="field">
                <i class="fas fa-lock icon"></i>
                <input type="password" name="password" id="password" placeholder="Password" required>
                <i class="fas fa-eye toggle-password" id="toggle-password-icon" onclick="togglePassword('password', 'toggle-password-icon')"></i>
            </div>
            
            <div class="field">
                <i class="fas fa-lock icon"></i>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
                <i class="fas fa-eye toggle-password" id="toggle-confirm-icon" onclick="togglePassword('confirm_password', 'toggle-confirm-icon')"></i>
            </div>
            
            <div class="password-requirements">
                <h4><i class="fas fa-shield-alt"></i> Password Requirements</h4>
                <ul class="requirements-list">
                    <li><i class="fas fa-check-circle"></i> At least 8 characters long</li>
                    <li><i class="fas fa-check-circle"></i> Include uppercase and lowercase letters</li>
                    <li><i class="fas fa-check-circle"></i> Include at least one number</li>
                </ul>
            </div>
            
            <div class="field">
                <input type="submit" class="btn" name="submit" value="Create Account">
            </div>
        </form>
        <div class="links-center">
            Already have an account?<a href="login.php">Sign In</a>
        </div>
    </div>

    <script>
        function togglePassword(fieldId, iconId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(iconId);
            
            if (field.type === "password") {
                field.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                field.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>
</body>
</html>