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
    <style>
        /* Reset and base styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #71b7e6, #9b59b6);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        /* Container for the registration form */
        .container {
            width: 100%;
            max-width: 400px;
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        /* Header styling */
        .container header {
            font-size: 2rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        /* Form fields styling */
        .field {
            margin-bottom: 20px;
            position: relative;
        }
        .field input {
            width: 100%;
            padding: 12px 15px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        /* Toggle password icon */
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }
        /* Submit button styling */
        .btn {
            width: 100%;
            padding: 12px;
            font-size: 1rem;
            color: #fff;
            background: #9b59b6;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .btn:hover {
            background: #8e44ad;
        }
        /* Centered links below the form */
        .links-center {
            text-align: center;
            margin-top: 10px;
        }
        .links-center a {
            color: #9b59b6;
            text-decoration: none;
            font-weight: bold;
        }
        /* Error message styling */
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            text-align: center;
            font-size: 0.9rem;
        }
        .message.error {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <form action="" method="post">
            <header>Tenant Registration</header>

            <!-- Display error messages -->
            <?php if (!empty($message)): ?>
                <?php foreach ($message as $msg): ?>
                    <div class="message error"><?= $msg; ?></div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="field">
                <input type="text" name="name" placeholder="Full Name" required>
            </div>
            <div class="field">
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            <div class="field">
                <input type="password" name="password" id="password" placeholder="Password" required>
                <span class="toggle-password" onclick="togglePassword('password')">👁️</span>
            </div>
            <div class="field">
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
                <span class="toggle-password" onclick="togglePassword('confirm_password')">👁️</span>
            </div>
            <div class="field">
                <input type="submit" class="btn" name="submit" value="Register">
            </div>
        </form>
        <div class="links-center">
                Already registered? <a href="login.php">Login</a>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            field.type = field.type === "password" ? "text" : "password";
        }
    </script>
</body>
</html>
