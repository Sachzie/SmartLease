<?php
session_start();
include('../../includes/config.php');

if (isset($_GET['property_id'])) {
    $property_id = $_GET['property_id'];
    $landlord_id = $_SESSION['landlord_id'];

    // Delete property
    $query = "DELETE FROM properties WHERE property_id = '$property_id' AND landlord_id = '$landlord_id'";
    if (mysqli_query($conn, $query)) {
        header("Location: crudindex.php");
    } else {
        echo "Error deleting property: " . mysqli_error($conn);
    }
} else {
    echo "Invalid property ID.";
}
?>
