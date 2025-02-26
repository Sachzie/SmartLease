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
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #71b7e6, #9b59b6);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            width: 100%;
            max-width: 400px;
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        .container header {
            font-size: 2rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .field {
            margin-bottom: 20px;
            position: relative;
        }
        .field input {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
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
        .links-center {
            text-align: center;
            margin-top: 10px;
        }
        .links-center a {
            color: #9b59b6;
            text-decoration: none;
            font-weight: bold;
        }
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
        <form action="login.php" method="post">
            <header>Login</header>

            <!-- Display error messages -->
            <?php if (!empty($message)): ?>
                <?php foreach ($message as $msg): ?>
                    <div class="message error"><?= htmlspecialchars($msg); ?></div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="field">
                <input type="email" name="email" placeholder="Email Address" required>
            </div>

            <div class="field">
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <div class="field">
                <input type="submit" class="btn" name="submit" value="Login">
            </div>

            <div class="links-center">
                New to SmartLease? <a href="registerchoice.php">Sign Up</a>
            </div>
        </form>
    </div>
</body>
</html>