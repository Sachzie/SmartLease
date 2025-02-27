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
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4e1c5;
            color: #5a3e2b;
            padding-top: 100px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        .container {
            max-width: 1150px;
            margin: 20px auto;
            padding: 40px;
            background: #e6c8a0;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            line-height: 1.6;
            font-size: 17px;
        }

        h2 {
            color: #5a3e2b;
            margin-bottom: 20px;
        }

        .form-container {
            display: flex;
            gap: 50px;
            justify-content: space-between;
        }

        .group-box {
            flex: 1;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: left;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-weight: 600;
            color: #5a3e2b;
            display: block;
            margin-bottom: 8px;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        button {
            width: 100%;
            padding: 14px;
            background: #5a3e2b;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }

        button:hover {
            background: #4a2e1e;
        }

        @media (max-width: 768px) {
            .form-container {
                
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Apply for <?php echo htmlspecialchars($property['name']); ?></h2>
    <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

    <div class="form-container">
        <!-- Tenant Details Box -->
        <div class="group-box">
            <h3>Your Details</h3> <br>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($tenant['name']); ?></p> 
            <p><strong>Email:</strong> <?php echo htmlspecialchars($tenant['email']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($tenant['address']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($tenant['phone']); ?></p>
        </div>

        <!-- Lease Application Box -->
        <div class="group-box">
            <h3>Application Form</h3>
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Preferred Move-In Date:</label>
                    <input type="date" name="preferred_move_in_date" required>
                </div>

                <div class="form-group">
                    <label>Number of Occupants:</label>
                    <input type="number" name="num_occupants" min="1" required>
                </div>

                <div class="form-group">
                    <label>Additional Requests:</label>
                    <textarea name="additional_requests" rows="4"></textarea>
                </div>

                <div class="form-group">
                    <label>Upload Valid ID:</label>
                    <input type="file" name="valid_id" required>
                </div>

                <button type="submit">Submit Application</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
