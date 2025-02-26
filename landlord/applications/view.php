<?php
session_start();
include('../../includes/config.php');
include('../../includes/landlordheader.php'); // Landlord-specific header

// Ensure landlord is logged in
if (!isset($_SESSION['landlord_id'])) {
    header("Location: login.php");
    exit();
}

$landlord_id = $_SESSION['landlord_id'];

// Check if application_id is set
if (!isset($_GET['application_id'])) {
    echo "Invalid application.";
    exit();
}

$application_id = $_GET['application_id'];
$message = "";

// Fetch application details
$query = "
    SELECT la.application_id, la.status, la.application_date, 
           t.name AS tenant_name, t.email AS tenant_email, 
           t.address AS tenant_address, t.phone AS tenant_phone, t.picture AS tenant_picture,
           p.name AS property_name, p.location AS property_location, 
           lad.preferred_move_in_date, lad.num_occupants, lad.additional_requests, lad.valid_id
    FROM lease_applications la
    JOIN tenants t ON la.tenant_id = t.tenant_id
    JOIN properties p ON la.property_id = p.property_id
    JOIN lease_application_details lad ON la.application_id = lad.application_id
    WHERE la.application_id = ? AND p.landlord_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $application_id, $landlord_id);
$stmt->execute();
$result = $stmt->get_result();
$application = $result->fetch_assoc();

// If no data found, deny access
if (!$application) {
    echo "No application found.";
    exit();
}

// Accept application logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accept'])) {
    $update_query = "UPDATE lease_applications SET status = 'in progress' WHERE application_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $application_id);
    
    if ($update_stmt->execute()) {
        $message = "Application accepted successfully!";
        $application['status'] = 'in progress'; // Update UI dynamically
    } else {
        $message = "Error accepting application.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Overview</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        h2 { text-align: center; }
        .group-box {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .box {
            width: 48%;
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 10px;
        }
        .status {
            font-weight: bold;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .status-pending { background-color: orange; }
        .status-in-progress { background-color: blue; }
        .status-resolved { background-color: green; }
        .btn {
            display: inline-block;
            background: green;
            color: white;
            padding: 10px 15px;
            border: none;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn:hover { background: darkgreen; }
        .message {
            text-align: center;
            font-weight: bold;
            color: green;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Application Overview</h2>
    <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

    <div class="group-box">
        <!-- Tenant Details -->
        <div class="box">
            <h3>Tenant Details</h3>
            <img src="<?php echo htmlspecialchars($application['tenant_picture']); ?>" alt="Tenant Picture">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($application['tenant_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($application['tenant_email']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($application['tenant_address']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($application['tenant_phone']); ?></p>
        </div>

        <!-- Application Details -->
        <div class="box">
            <h3>Application Details</h3>
            <p><strong>Property:</strong> <?php echo htmlspecialchars($application['property_name']); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($application['property_location']); ?></p>
            <p><strong>Preferred Move-in Date:</strong> <?php echo htmlspecialchars($application['preferred_move_in_date']); ?></p>
            <p><strong>Number of Occupants:</strong> <?php echo htmlspecialchars($application['num_occupants']); ?></p>
            <p><strong>Additional Requests:</strong> <?php echo nl2br(htmlspecialchars($application['additional_requests'])); ?></p>
            <p><strong>Valid ID:</strong> <a href="<?php echo htmlspecialchars($application['valid_id']); ?>" target="_blank">View ID</a></p>
            <p><strong>Application Status:</strong> 
                <span class="status status-<?php echo strtolower($application['status']); ?>">
                    <?php echo ucfirst($application['status']); ?>
                </span>
            </p>

            <!-- Accept Application Button -->
            <?php if ($application['status'] == 'pending'): ?>
                <form method="post">
                    <button type="submit" name="accept" class="btn">Accept Application</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
