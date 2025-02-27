<?php
session_start();
include('../includes/config.php');
include('../includes/landlordheader.php'); // This already starts session

$landlord_id = $_SESSION['landlord_id'];

$query = mysqli_query($conn, "SELECT name, email FROM landlords WHERE landlord_id = '$landlord_id'") or die('Query failed');
$landlord = mysqli_fetch_assoc($query);

$res_Uname = isset($landlord['name']) ? htmlspecialchars($landlord['name']) : 'Landlord';
$res_Email = isset($landlord['email']) ? htmlspecialchars($landlord['email']) : 'No Email';
$showNotification = false; // Change logic based on need

// Fetch tenants
$tenants_query = mysqli_query($conn, "SELECT name FROM tenants") or die('Query failed');
$tenants = [];
while ($tenant = mysqli_fetch_assoc($tenants_query)) {
    $tenants[] = [
        'name' => htmlspecialchars($tenant['name']),
    ];
}

// Fetch properties
$properties_query = mysqli_query($conn, "SELECT name, location FROM properties WHERE landlord_id = '$landlord_id'") or die('Query failed');
$properties = [];
while ($property = mysqli_fetch_assoc($properties_query)) {
    $properties[] = [
        'name' => htmlspecialchars($property['name']),
        'location' => htmlspecialchars($property['location'])
    ];
}
?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartLease Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* General Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Nunito Sans', sans-serif;
        }

        body {
            background: rgb(248, 243, 217);
        }

        /* Main Content */
        .main-content {
            padding: 30px;
        }

        .main-content h1 {
            font-size: 28px;
            color: rgb(80, 75, 56);
            margin-bottom: 10px;
        }

        .main-content p {
            font-size: 18px;
            color: rgb(100, 95, 76);
        }

        /* Dashboard Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .card {
            background: rgb(235, 229, 194);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .card h3 {
            margin: 0 0 10px 0;
            color: rgb(80, 75, 56);
        }

        /* Notification */
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #ffcc00;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            display: <?php echo $showNotification ? 'block' : 'none'; ?>;
        }

        .notification .buttons button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
            margin-left: 5px;
        }

        .notification .buttons button.later {
            background: #ccc;
        }
        .card-link {
    text-decoration: none;
    color: inherit;
    display: block;
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: scale(1.05);
}

    </style>
</head>
<body>
<div class="main-content">
    <h1>Welcome, <b><?php echo $res_Uname; ?></b></h1>
    <p>Your email: <b><?php echo $res_Email; ?></b></p>
    <p>Manage your Properties, View Tenants, and more!.</p>
    <div class="dashboard-cards">
        <a href="viewtenants/tenantsview.php" class="card-link">
            <div class="card">
                <h3>Tenants</h3>
                <p>View and manage all tenants renting your properties.</p>
                <div class="tenants-list">
                    <?php foreach ($tenants as $tenant): ?>
                        <div class="tenant">
                            <span><?php echo $tenant['name']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </a>
        <a href="payments/billing.php" class="card-link">
            <div class="card">
                <h3>Billing Notifications</h3>
                <p>Stay updated on pending and upcoming payments.</p>
            </div>
        </a>
        <a href="manageproperties/crudindex.php" class="card-link">
            <div class="card">
                <h3>Properties</h3>
                <p>Monitor available and occupied apartments.</p>
                <div class="properties-list">
                    <?php foreach ($properties as $property): ?>
                        <div class="property">
                            <span><?php echo $property['name']; ?> - <?php echo $property['location']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </a>
        <a href="maintenance/maintenance.php" class="card-link">
            <div class="card">
                <h3>Maintenance</h3>
                <p>Track maintenance requests and completed work.</p>
            </div>
        </a>
    </div>
</div>

    <!-- Notification -->
    <div class="notification" id="notification">
        <p>You need to complete your details.</p>
        <div class="buttons">
            <button onclick="window.location.href='../profile/editprofile.php'">Continue</button>
            <button class="later" onclick="hideNotification()">Later</button>
        </div>
    </div>

    <script>
        function hideNotification() {
            document.getElementById('notification').style.display = 'none';
        }
    </script>
</body>
</html>
