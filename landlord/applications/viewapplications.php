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

// Fetch applications for landlord's properties
$query = "
    SELECT la.application_id, la.status, 
           t.name AS tenant_name, t.email AS tenant_email, 
           p.name AS property_name
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #007bff;
            color: white;
        }
        .check-btn {
            background-color: #28a745;
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }
        .check-btn:hover {
            background-color: #218838;
        }
        .status-pending { color: orange; }
        .status-approved { color: green; }
        .status-rejected { color: red; }
    </style>
</head>
<body>

<div class="container">
    <h2>Tenant Lease Applications</h2>
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
            <td class="status-<?php echo strtolower($row['status']); ?>">
                <?php echo ucfirst($row['status']); ?>
            </td>
            <td>
            <a href="view.php?application_id=<?php echo $row['application_id']; ?>" class="check-btn">Check</a>

            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
