<?php
session_start();
include('../../includes/config.php');

if (!isset($_SESSION['landlord_id']) || !isset($_POST['application_id'])) {
    header("Location: viewapplication.php");
    exit();
}

$application_id = $_POST['application_id'];
$landlord_id = $_SESSION['landlord_id'];

// Fetch tenant_id and property_id from lease application
$query = "SELECT tenant_id, property_id FROM lease_applications WHERE application_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $application_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header("Location: viewapplication.php?error=notfound");
    exit();
}
$row = $result->fetch_assoc();
$tenant_id = $row['tenant_id'];
$property_id = $row['property_id'];

// Update the lease table to mark it as active
$updateLease = "
    UPDATE leases 
    SET status = 'Active', start_date = CURDATE(), end_date = DATE_ADD(CURDATE(), INTERVAL 1 YEAR)
    WHERE tenant_id = ? AND property_id = ?";
$stmt = $conn->prepare($updateLease);
$stmt->bind_param("ii", $tenant_id, $property_id);
$stmt->execute();

// Mark the lease application as "Approved"
$updateApplication = "
    UPDATE lease_applications 
    SET status = 'Approved' 
    WHERE application_id = ?";
$stmt = $conn->prepare($updateApplication);
$stmt->bind_param("i", $application_id);
$stmt->execute();

header("Location: viewapplication.php?success=resolved");
exit();
?>
