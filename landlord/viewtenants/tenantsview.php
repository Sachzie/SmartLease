<?php
session_start();
include('../../includes/config.php');
include('../../includes/landlordheader.php');

$landlord_id = $_SESSION['landlord_id'];

// Fetch tenants
$query = mysqli_query($conn, "SELECT name, email, phone, picture FROM tenants") 
or die(mysqli_error($conn));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Tenants - SmartLease</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Nunito Sans', sans-serif;
        }

        body {
            background: rgb(248, 243, 217);
            padding: 30px;
        }

        .container {
            max-width: 1200px;
            margin: 60px auto 0;
            background: rgb(235, 229, 194);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .tenant-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .tenant-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
            position: relative;
        }

        .tenant-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
        }

        .tenant-card img {
            width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>View Tenants</h2>
    </div>

    <div class="tenant-grid">
        <?php while ($tenant = mysqli_fetch_assoc($query)) { ?>
            <div class="tenant-card">
                <img src="../../<?php echo htmlspecialchars($tenant['picture']); ?>" alt="Tenant Picture">
                <h3><?php echo htmlspecialchars($tenant['name']); ?></h3>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($tenant['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($tenant['phone']); ?></p>
            </div>
        <?php } ?>
    </div>
</div>

</body>
</html>
