<?php
    include('../includes/config.php');
    session_start();

    $message = []; // Initialize an array for error messages

    if (isset($_POST['submit'])) {
        // Correct the form field name from 'full_name' to 'name'
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

        // Validate required fields
        if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
            $message[] = 'All fields are required.';
        }

        // Validate name format
        if (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
            $message[] = 'Full Name should contain letters and spaces only.';
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message[] = 'Invalid email format.';
        }

        // Check if email already exists in the tenant table
        $check_email = mysqli_query($conn, "SELECT * FROM tenants WHERE email = '$email'") or die('Query failed');
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

        // If no errors, hash the password and insert into the tenant table
        if (empty($message)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_query = "INSERT INTO tenants (name, email, password) 
                             VALUES ('$name', '$email', '$hashed_password')";
                             
            if (mysqli_query($conn, $insert_query)) {
                // Store tenant_id in session
                $_SESSION['tenant_id'] = mysqli_insert_id($conn);
                $_SESSION['role'] = 'tenant';
                $_SESSION['name'] = $name;
                header('Location: ../homepage/home.php');
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
    <title>SmartLease - Tenant Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #3F51B5;
            --primary-dark: #303F9F;
            --primary-light: #C5CAE9;
            --accent: #FF4081;
            --tenant: #2196F3;
            --tenant-dark: #1976D2;
            --tenant-light: #BBDEFB;
            --text-dark: #263238;
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
            background: linear-gradient(135deg, #5C6BC0, #3949AB);
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
            background: linear-gradient(to right, var(--tenant), var(--tenant-dark));
        }
        
        .logo {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .logo i {
            font-size: 45px;
            color: var(--tenant);
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
            color: var(--tenant);
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
            border-color: var(--tenant);
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.2);
        }
        
        .field input::placeholder {
            color: #9E9E9E;
        }
        
        .btn {
            width: 100%;
            padding: 16px;
            font-size: 1rem;
            color: var(--text-light);
            background: var(--tenant);
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
            background: var(--tenant-dark);
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
            color: var(--tenant);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
            margin-left: 5px;
        }
        
        .links-center a:hover {
            color: var(--tenant-dark);
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
            background-color: var(--tenant-light);
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
            color: var(--tenant-dark);
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
            color: var(--tenant);
        }
        
        .password-strength {
            height: 5px;
            border-radius: 3px;
            background: #e1e1e1;
            overflow: hidden;
            margin-top: 10px;
        }
        
        .password-strength span {
            display: block;
            height: 100%;
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .strength-text {
            font-size: 12px;
            margin-top: 5px;
            color: #666;
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
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <i class="fas fa-home"></i>
        </div>
        <form action="" method="post">
            <header>Tenant Registration</header>
            <p class="subtitle">Create your account to find and rent properties</p>
            
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
                <input type="text" name="name" placeholder="Full Name" required>
            </div>
            
            <div class="field">
                <i class="fas fa-envelope icon"></i>
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            
            <div class="field">
                <i class="fas fa-lock icon"></i>
                <input type="password" name="password" id="password" placeholder="Password" required onkeyup="checkPasswordStrength()">
                <i class="fas fa-eye toggle-password" id="toggle-password-icon" onclick="togglePassword('password', 'toggle-password-icon')"></i>
                <div class="password-strength">
                    <span id="strength-bar"></span>
                </div>
                <div class="strength-text" id="strength-text"></div>
            </div>
            
            <div class="field">
                <i class="fas fa-lock icon"></i>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required onkeyup="checkPasswordMatch()">
                <i class="fas fa-eye toggle-password" id="toggle-confirm-icon" onclick="togglePassword('confirm_password', 'toggle-confirm-icon')"></i>
                <div class="strength-text" id="match-text"></div>
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
        
        function checkPasswordStrength() {
            const password = document.getElementById("password").value;
            const strengthBar = document.getElementById("strength-bar");
            const strengthText = document.getElementById("strength-text");
            
            // Calculate strength
            let strength = 0;
            if (password.length >= 8) strength += 25;
            if (password.match(/[a-z]+/)) strength += 25;
            if (password.match(/[A-Z]+/)) strength += 25;
            if (password.match(/[0-9]+/) || password.match(/[^a-zA-Z0-9]+/)) strength += 25;
            
            // Update UI
            strengthBar.style.width = strength + "%";
            
            if (strength < 25) {
                strengthBar.style.backgroundColor = "#d32f2f";
                strengthText.textContent = "Very Weak";
                strengthText.style.color = "#d32f2f";
            } else if (strength < 50) {
                strengthBar.style.backgroundColor = "#ff9800";
                strengthText.textContent = "Weak";
                strengthText.style.color = "#ff9800";
            } else if (strength < 75) {
                strengthBar.style.backgroundColor = "#ffc107";
                strengthText.textContent = "Moderate";
                strengthText.style.color = "#ffc107";
            } else if (strength < 100) {
                strengthBar.style.backgroundColor = "#4caf50";
                strengthText.textContent = "Strong";
                strengthText.style.color = "#4caf50";
            } else {
                strengthBar.style.backgroundColor = "#2e7d32";
                strengthText.textContent = "Very Strong";
                strengthText.style.color = "#2e7d32";
            }
        }
        
        function checkPasswordMatch() {
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirm_password").value;
            const matchText = document.getElementById("match-text");
            
            if (confirmPassword.length === 0) {
                matchText.textContent = "";
                return;
            }
            
            if (password === confirmPassword) {
                matchText.textContent = "Passwords match";
                matchText.style.color = "#4caf50";
            } else {
                matchText.textContent = "Passwords do not match";
                matchText.style.color = "#d32f2f";
            }
        }
    </script>
</body>
</html>