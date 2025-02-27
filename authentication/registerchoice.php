<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartLease - Register Choice</title>
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
            text-align: center;
        }
        .container header {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }
        .choice-button {
            display: block;
            width: 100%;
            padding: 15px;
            margin: 10px 0;
            font-size: 1rem;
            color: #fff;
            background: #9b59b6;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
            text-decoration: none;
            text-align: center;
        }
        .choice-button:hover {
            background: #8e44ad;
        }
        .back-link {
            display: block;
            margin-top: 20px;
            color: #9b59b6;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>Register as...</header>
        <a href="landlordregister.php" class="choice-button">Landlord</a>
        <a href="tenantregister.php" class="choice-button">Tenant</a>
        <a href="login.php" class="back-link">Back to Login</a>
    </div>
</body>
</html>
