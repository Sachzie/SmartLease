<?php
include('../../includes/config.php');

$data = json_decode(file_get_contents('php://input'), true);
$image = $data['image'];
$property_id = $data['property_id'];

$query = mysqli_query($conn, "SELECT images FROM properties WHERE property_id = '$property_id'");
$property = mysqli_fetch_assoc($query);
$imagePaths = explode(',', $property['images']);

if (($key = array_search($image, $imagePaths)) !== false) {
    unset($imagePaths[$key]);
    $newImageList = implode(',', $imagePaths);
    mysqli_query($conn, "UPDATE properties SET images = '$newImageList' WHERE property_id = '$property_id'");
    unlink('../../propertypictures/uploads/' . $image);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>
