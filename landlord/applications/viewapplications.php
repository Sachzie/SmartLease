<?php
session_start();
include('../../includes/config.php');
include('../../includes/landlordheader.php'); // Assuming landlords have a separate header

// Ensure landlord is logged in
if (!isset($_SESSION['landlord_id'])) {
    header("Location: login.php");
    exit();
}

$landlord_id = $_SESSION['landlord_id'];

// Handle lease resolution
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['application_id'])) {
    $application_id = intval($_POST['application_id']);

    // Fetch tenant_id, property_id from lease application
    $query = "SELECT la.tenant_id, la.property_id, p.price 
              FROM lease_applications la
              JOIN properties p ON la.property_id = p.property_id
              WHERE la.application_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $application_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $error = "Application not found.";
    } else {
        $row = $result->fetch_assoc();
        $tenant_id = $row['tenant_id'];
        $property_id = $row['property_id'];
        $rent_amount = $row['price'];

        // Start transaction
        $conn->begin_transaction();
        try {
            // Insert new lease
            $insertLease = "
                INSERT INTO leases (tenant_id, property_id, landlord_id, start_date, end_date, rent_amount, status) 
                VALUES (?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), ?, 'active')";
            $stmt = $conn->prepare($insertLease);
            $stmt->bind_param("iiid", $tenant_id, $property_id, $landlord_id, $rent_amount);
            $stmt->execute();

            // Mark the lease application as "Resolved"
            $updateApplication = "
                UPDATE lease_applications 
                SET status = 'resolved' 
                WHERE application_id = ?";
            $stmt = $conn->prepare($updateApplication);
            $stmt->bind_param("i", $application_id);
            $stmt->execute();

            // Commit transaction
            $conn->commit();
            $success = "Lease successfully created and application resolved.";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Failed to create lease.";
        }
    }
}

// Fetch applications for landlord's properties
$query = "
    SELECT la.application_id, la.status, 
           t.tenant_id, t.name AS tenant_name, t.email AS tenant_email, 
           p.property_id, p.name AS property_name
    FROM lease_applications la
    JOIN tenants t ON la.tenant_id = t.tenant_id
    JOIN properties p ON la.property_id = p.property_id
    WHERE p.landlord_id = ?
    ORDER BY la.status ASC, la.application_id DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $landlord_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Applications</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4e1c5;
            color: #5a3e2b;
            padding-top: 100px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 30px;
            background: #e6c8a0;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            color: #5a3e2b;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: center;
        }

        th {
            background: #5a3e2b;
            color: white;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        .btn {
            display: inline-block;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
            transition: 0.3s;
            color: white;
        }

        .btn-check {
            background: #5a3e2b;
        }

        .btn-resolve {
            background: #e6c8a0;
            border: none;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 5px;
        }

        .btn-resolve:hover, .btn-check:hover {
            opacity: 0.8;
        }

        .success, .error {
            text-align: center;
            font-weight: bold;
            padding: 10px;
            margin: 10px 0;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }

        @media (max-width: 768px) {
            table {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Tenant Lease Applications</h2>
    
    <?php if (isset($success)): ?>
        <p class="success"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <table>
        <tr>
            <th>Property</th>
            <th>Tenant</th>
            <th>Email</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['property_name']); ?></td>
            <td><?php echo htmlspecialchars($row['tenant_name']); ?></td>
            <td><?php echo htmlspecialchars($row['tenant_email']); ?></td>
            <td><?php echo ucfirst($row['status']); ?></td>
            <td>
                <a href="view.php?application_id=<?php echo $row['application_id']; ?>" class="btn btn-check">
                    <i class="fas fa-eye"></i> Check
                </a>
                <?php if (strtolower($row['status']) === "in progress"): ?>
                    <form method="POST" action="" style="display:inline;">
                        <input type="hidden" name="application_id" value="<?php echo $row['application_id']; ?>">
                        <button type="submit" class="btn btn-resolve">
                            <i class="fas fa-check"></i> Resolve
                        </button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
