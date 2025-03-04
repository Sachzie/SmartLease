<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartLease - Register Choice</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #795548;
            --primary-dark: #5D4037;
            --primary-light: #D7CCC8;
            --accent: #FF9800;
            --text-dark: #3E2723;
            --text-light: #FFFFFF;
            --landlord: #4CAF50;
            --landlord-dark: #388E3C;
            --tenant: #2196F3;
            --tenant-dark: #1976D2;
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
            text-align: center;
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
        
        .logo {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .logo i {
            font-size: 45px;
            color: var(--primary);
        }
        
        .container header {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 35px;
            letter-spacing: 0.5px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 25px;
            font-size: 1rem;
        }
        
        .choices {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .choice-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 2px solid #f5f5f5;
            position: relative;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: var(--text-dark);
        }
        
        .choice-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .landlord-card {
            border-left: 5px solid var(--landlord);
        }
        
        .landlord-card:hover {
            border-color: var(--landlord);
        }
        
        .tenant-card {
            border-left: 5px solid var(--tenant);
        }
        
        .tenant-card:hover {
            border-color: var(--tenant);
        }
        
        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: var(--primary);
            position: absolute;
            left: 30px;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .landlord-card .card-icon {
            color: var(--landlord);
        }
        
        .tenant-card .card-icon {
            color: var(--tenant);
        }
        
        .card-content {
            padding-left: 80px;
            text-align: left;
            width: 100%;
        }
        
        .card-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .card-description {
            font-size: 0.85rem;
            color: #777;
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
        
        .back-link {
            display: inline-flex;
            align-items: center;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
            padding: 10px 20px;
            border-radius: 8px;
        }
        
        .back-link i {
            margin-right: 8px;
        }
        
        .back-link:hover {
            background-color: var(--primary-light);
            color: var(--primary-dark);
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
            
            .choice-card {
                height: 100px;
            }
            
            .card-icon {
                font-size: 2rem;
                left: 20px;
            }
            
            .card-content {
                padding-left: 70px;
            }
            
            .card-title {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <i class="fas fa-home"></i>
        </div>
        <header>Join SmartLease</header>
        <p class="subtitle">Select how you'd like to use our platform</p>
        
        <div class="choices">
            <a href="landlordregister.php" class="choice-card landlord-card">
                <div class="card-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="card-content">
                    <div class="card-title">Landlord</div>
                    <div class="card-description">List properties and manage tenants</div>
                </div>
            </a>
            
            <a href="tenantregister.php" class="choice-card tenant-card">
                <div class="card-icon">
                    <i class="fas fa-user"></i>
                </div>
                <div class="card-content">
                    <div class="card-title">Tenant</div>
                    <div class="card-description">Find and rent properties</div>
                </div>
            </a>
        </div>
        
        <div class="divider">
            <span>OR</span>
        </div>
        
        <a href="login.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Login
        </a>
    </div>
</body>
</html>