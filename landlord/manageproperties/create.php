<?php
ob_start(); // Start output buffering

session_start();
include('../../includes/config.php');
include('../../includes/landlordheader.php');

$landlord_id = $_SESSION['landlord_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $full_address = mysqli_real_escape_string($conn, $_POST['full_address']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $bedrooms = mysqli_real_escape_string($conn, $_POST['bedrooms']);
    $bathrooms = mysqli_real_escape_string($conn, $_POST['bathrooms']);
    $amenities = mysqli_real_escape_string($conn, $_POST['amenities']);
    $sale_status = mysqli_real_escape_string($conn, $_POST['sale_status']);
    $availability = 'available';

    // Insert property details
    $query = "INSERT INTO properties (landlord_id, name, description, location, full_address, price, bedrooms, bathrooms, amenities, availability, sale_status) 
              VALUES ('$landlord_id', '$name', '$description', '$location', '$full_address', '$price', '$bedrooms', '$bathrooms', '$amenities', '$availability', '$sale_status')";

if (mysqli_query($conn, $query)) {
    $property_id = mysqli_insert_id($conn);
    
    // Image Upload Logic
    $uploadDir = realpath(__DIR__ . '/../../propertypictures/uploads/') . '/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
            die("❌ Error: Failed to create upload directory at $uploadDir");
        }
    }

    $imagePaths = [];
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            if (!empty($_FILES['images']['name'][$key])) {
                $fileName = time() . '_' . basename($_FILES['images']['name'][$key]);
                $targetFilePath = $uploadDir . $fileName;
                if (move_uploaded_file($tmpName, $targetFilePath)) {
                    $imagePaths[] = $fileName;
                } else {
                    echo "❌ Error: Failed to upload " . $_FILES['images']['name'][$key] . "<br>";
                }
            }
        }
    }
    $imageList = implode(',', $imagePaths);
    mysqli_query($conn, "UPDATE properties SET images='$imageList' WHERE property_id='$property_id'");

    // Redirect after successful property addition
    header("Location: ../manageproperties/crudindex.php");
    exit();
} else {
    $error = 'Failed to add property. Please try again.';
}
}

ob_end_flush(); // Flush the output buffer
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Property - SmartLease</title>
    <!-- <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600&display=swap" rel="stylesheet"> -->
    <style>
        * {
            /* margin: 0;
            padding: 0; */
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

        .form-container {
            display: flex;
            gap: 20px;
            justify-content: space-between;
        }

        .group-box {
            flex: 1;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        label {
            font-weight: 600;
            color: #5a3e2b;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #5a3e2b;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }

        button:hover {
            background: #5a3e2b;
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
        <h2>Add Property</h2>
        <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
        <form action="create.php" method="POST" enctype="multipart/form-data">
            <div class="form-container">
                <div class="group-box">
                    <h3>Property Details</h3>
                    <div class="form-group">
                        <label>Property Name:</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Description:</label>
                        <textarea name="description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Location:</label>
                        <input type="text" name="location" required>
                    </div>
                    <div class="form-group">
                        <label>Full Address:</label>
                        <textarea name="full_address" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Price:</label>
                        <input type="number" step="0.01" name="price" required>
                    </div>
                </div>
                <div class="group-box">
                    <h3>Additional Details</h3>
                    <div class="form-group">
                        <label>Bedrooms:</label>
                        <input type="number" name="bedrooms" required>
                    </div>
                    <div class="form-group">
                        <label>Bathrooms:</label>
                        <input type="number" name="bathrooms" required>
                    </div>
                    <div class="form-group">
                        <label>Amenities:</label>
                        <textarea name="amenities"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Sale Status:</label>
                        <select name="sale_status">
                            <option value="for_rent">For Rent</option>
                            <option value="for_sale">For Sale</option>
                            <option value="sold">Sold</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Upload Images:</label>
                        <input type="file" name="images[]" multiple>
                    </div>
                </div>
            </div>
            <button type="submit">Add Property</button>
        </form>
    </div>
</body>
</html>
