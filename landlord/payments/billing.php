<?php
session_start();
include('../../includes/config.php');
include('../../includes/landlordheader.php');

$landlord_id = $_SESSION['landlord_id']; // Use session-based landlord ID

// Fetch tenants linked to this landlord
$sql_tenants = "SELECT DISTINCT t.tenant_id, t.name, l.rent_amount
                FROM tenants t 
                INNER JOIN leases l ON t.tenant_id = l.tenant_id 
                WHERE l.landlord_id = ?";
$stmt_tenants = $conn->prepare($sql_tenants);
$stmt_tenants->bind_param("i", $landlord_id);
$stmt_tenants->execute();
$result_tenants = $stmt_tenants->get_result();

$tenants = [];
while ($row = $result_tenants->fetch_assoc()) {
    $tenants[] = $row;
}
$stmt_tenants->close();

// Fetch existing bills
$sql_bills = "SELECT p.payment_id, t.name, 'Rent' AS bill_type, p.amount, p.billing_month, p.billing_year, p.due_date 
              FROM payments p 
              INNER JOIN tenants t ON p.tenant_id = t.tenant_id 
              WHERE p.landlord_id = ?
              UNION 
              SELECT u.utility_id, t.name, u.bill_type, u.amount, u.billing_month, u.billing_year, u.due_date 
              FROM utility_payments u 
              INNER JOIN tenants t ON u.tenant_id = t.tenant_id 
              WHERE u.landlord_id = ?
              ORDER BY billing_year DESC, billing_month DESC";
$stmt_bills = $conn->prepare($sql_bills);
$stmt_bills->bind_param("ii", $landlord_id, $landlord_id);
$stmt_bills->execute();
$result_bills = $stmt_bills->get_result();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    var_dump($_POST); // Debugging: Check if the due_date is received
    $tenant_id = $_POST['tenant_id'];
    $bill_type = $_POST['bill_type'];
    $amount = $_POST['amount'];
    $billing_month = $_POST['billing_month'];
    $billing_year = $_POST['billing_year'];
    $due_date = $_POST['due_date']; // Make sure this is correctly retrieved

    if (empty($billing_month) || empty($billing_year) || empty($due_date)) {
        echo "<script>alert('Please select all required fields including the due date.');</script>";
    } else {
        $sql_lease = "SELECT lease_id FROM leases WHERE tenant_id = ? AND landlord_id = ?";
        $stmt_lease = $conn->prepare($sql_lease);
        $stmt_lease->bind_param("ii", $tenant_id, $landlord_id);
        $stmt_lease->execute();
        $result_lease = $stmt_lease->get_result();
        $lease = $result_lease->fetch_assoc();
        
        if (!$lease) {
            echo "<script>alert('No active lease found for this tenant!');</script>";
        } else {
            $lease_id = $lease['lease_id'];

            if ($bill_type === "rent") {
                $sql_insert = "INSERT INTO payments (lease_id, tenant_id, landlord_id, amount, billing_month, billing_year, due_date, status) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bind_param("iiiidss", $lease_id, $tenant_id, $landlord_id, $amount, $billing_month, $billing_year, $due_date);
            } else {
                $sql_insert = "INSERT INTO utility_payments (lease_id, tenant_id, landlord_id, bill_type, amount, billing_month, billing_year, due_date, payment_status) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bind_param("iiisddds", $lease_id, $tenant_id, $landlord_id, $bill_type, $amount, $billing_month, $billing_year, $due_date);
            }

            if ($stmt_insert->execute()) {
                echo "<script>alert('Bill added successfully!'); window.location.href='billing.php';</script>";
            } else {
                echo "<script>alert('Error adding bill: " . $stmt_insert->error . "');</script>";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing - SmartLease</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5e6d7;
            margin: 0;
            padding-top: 80px;
        }

        .container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 20px;
        }

        .bill-list, .billing-form-container {
            width: 48%;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        .bill-list {
            background: #8d6e63;
            color: white;
        }

        .billing-form-container {
            background: #a1887f;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #5d4037;
        }

        label, select, input, button {
            display: block;
            width: 100%;
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 5px;
        }

        button {
            background: #795548;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background: #5d4037;
        }
    </style>

    <script>
        function updateRentAmount() {
            var tenants = <?= json_encode($tenants) ?>; // Use the stored tenants array
            var tenantId = document.getElementById("tenant_id").value;
            var billType = document.getElementById("bill_type").value;
            var amountField = document.getElementById("amount");

            if (billType === "rent") {
                var rent = tenants.find(tenant => tenant.tenant_id == tenantId)?.rent_amount || "";
                amountField.value = rent;
            } else {
                amountField.value = "";
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="bill-list">
            <h2>Existing Bills</h2>
            <table>
                <tr>
                    <th>Tenant</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Month</th>
                    <th>Year</th>
                    <th>Due Date</th>
                </tr>
                <?php while ($bill = $result_bills->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($bill['name']) ?></td>
                    <td><?= htmlspecialchars($bill['bill_type']) ?></td>
                    <td><?= number_format($bill['amount'], 2) ?></td>
                    <td><?= date("F", mktime(0, 0, 0, $bill['billing_month'], 1)) ?></td>
                    <td><?= $bill['billing_year'] ?></td>
                    <td><?= $bill['due_date'] ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <div class="billing-form-container">
            <h2>Add Tenant Bill</h2>
            <form method="post">
                <label>Tenant:</label>
                <select name="tenant_id" id="tenant_id" required onchange="updateRentAmount()">
                    <option value="">Select Tenant</option>
                    <?php foreach ($tenants as $row): ?>
                        <option value="<?= $row['tenant_id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Bill Type:</label>
                <select name="bill_type" id="bill_type" required onchange="updateRentAmount()">
                    <option value="rent">Rent</option>
                    <option value="electricity">Electricity</option>
                    <option value="water">Water</option>
                </select>

                <label>Billing Month:</label>
                <select name="billing_month" required>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>"><?= date("F", mktime(0, 0, 0, $m, 1)) ?></option>
                    <?php endfor; ?>
                </select>

                <label>Billing Year:</label>
                <input type="number" name="billing_year" min="2020" required>

                <label>Due Date:</label>
                <input type="date" name="due_date" required>

                <label>Amount:</label>
                <input type="number" name="amount" id="amount" min="1" step="0.01" required>

                <button type="submit">Add Bill</button>
            </form>
        </div>
    </div>
</body>
</html>
