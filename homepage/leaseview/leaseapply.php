<?php
session_start();
include('../../includes/config.php');
include('../../includes/header.php');

// Ensure tenant is logged in
if (!isset($_SESSION['tenant_id'])) {
    header("Location: login.php");
    exit();
}

$tenant_id = $_SESSION['tenant_id'];
$property_id = $_GET['property_id'] ?? null;
$message = "";

// Fetch tenant details
$queryTenant = "SELECT name, email, address, phone FROM tenants WHERE tenant_id = ?";
$stmt = $conn->prepare($queryTenant);
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$resultTenant = $stmt->get_result();
$tenant = $resultTenant->fetch_assoc();

// Fetch property details
$queryProperty = "SELECT * FROM properties WHERE property_id = ?";
$stmt = $conn->prepare($queryProperty);
$stmt->bind_param("i", $property_id);
$stmt->execute();
$resultProperty = $stmt->get_result();
$property = $resultProperty->fetch_assoc();

if (!$property) {
    die("Invalid property.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $preferred_move_in_date = $_POST['preferred_move_in_date'];
    $num_occupants = $_POST['num_occupants'];
    $additional_requests = $_POST['additional_requests'];
    $valid_id = null;

    // Handle file upload
    $uploadDir = realpath(__DIR__ . '/../../profile/uploaded_image/') . '/';
    if (!empty($_FILES["valid_id"]["name"])) {
        $file_name = basename($_FILES["valid_id"]["name"]);
        $target_file = $uploadDir . $file_name;
        $db_file_path = "profile/uploaded_image/" . $file_name;

        if (move_uploaded_file($_FILES["valid_id"]["tmp_name"], $target_file)) {
            $valid_id = $db_file_path;
        }
    }

    // Insert into lease_applications
    $insertApplication = "INSERT INTO lease_applications (property_id, tenant_id, status) VALUES (?, ?, 'pending')";
    $stmt = $conn->prepare($insertApplication);
    $stmt->bind_param("ii", $property_id, $tenant_id);
    $stmt->execute();
    $application_id = $stmt->insert_id;

    // Insert into lease_application_details
    $insertDetails = "INSERT INTO lease_application_details (application_id, preferred_move_in_date, num_occupants, additional_requests, valid_id) 
                      VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertDetails);
    $stmt->bind_param("isiss", $application_id, $preferred_move_in_date, $num_occupants, $additional_requests, $valid_id);
    
    if ($stmt->execute()) {
        $message = "Application submitted successfully!";
    } else {
        $message = "Error submitting application.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Lease</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            width: 80%;
            margin: 20px auto;
            gap: 20px;
        }
        .box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            width: 48%;
        }
        h2 {
            text-align: center;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }
        input, select, textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            margin-top: 15px;
            cursor: pointer;
            border-radius: 4px;
        }
        button:hover {
            background-color: #218838;
        }
        .message {
            text-align: center;
            color: green;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Tenant Details Box -->
    <div class="box">
        <h2>Tenant Details</h2>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($tenant['name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($tenant['email']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($tenant['address']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($tenant['phone']); ?></p>
    </div>

    <!-- Lease Application Box -->
    <div class="box">
        <h2>Apply for <?php echo htmlspecialchars($property['name']); ?></h2>
        <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>
        
        <form method="post" enctype="multipart/form-data">
            <label>Preferred Move-In Date:</label>
            <input type="date" name="preferred_move_in_date" required>

            <label>Number of Occupants:</label>
            <input type="number" name="num_occupants" min="1" required>

            <label>Additional Requests:</label>
            <textarea name="additional_requests" rows="4"></textarea>

            <label>Upload Valid ID:</label>
            <input type="file" name="valid_id" required>

            <button type="submit">Submit Application</button>
        </form>
    </div>
</div>

</body>
</html>
