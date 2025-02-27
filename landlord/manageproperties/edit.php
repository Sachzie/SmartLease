<?php
ob_start(); // Start output buffering

session_start();
include('../../includes/config.php');
include('../../includes/landlordheader.php');

$landlord_id = $_SESSION['landlord_id'];
$property_id = $_GET['property_id'];

// Fetch property details
$query = mysqli_query($conn, "SELECT * FROM properties WHERE property_id = '$property_id' AND landlord_id = '$landlord_id'") 
or die(mysqli_error($conn));
$property = mysqli_fetch_assoc($query);
$imagePaths = explode(',', $property['images']);

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

    // Handle image deletions
    if (!empty($_POST['deleted_images'])) {
        $deletedImages = explode(',', $_POST['deleted_images']);
        foreach ($deletedImages as $deletedImage) {
            if (($key = array_search($deletedImage, $imagePaths)) !== false) {
                unset($imagePaths[$key]);
                unlink('../../propertypictures/uploads/' . $deletedImage);
            }
        }
    }

    // Update property details
    $query = "UPDATE properties SET 
                name='$name', 
                description='$description', 
                location='$location', 
                full_address='$full_address', 
                price='$price', 
                bedrooms='$bedrooms', 
                bathrooms='$bathrooms', 
                amenities='$amenities', 
                availability='$availability', 
                sale_status='$sale_status' 
              WHERE property_id='$property_id' AND landlord_id='$landlord_id'";

    if (mysqli_query($conn, $query)) {
        // Image Upload Logic
        $uploadDir = realpath(__DIR__ . '/../../propertypictures/uploads/') . '/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
                die("❌ Error: Failed to create upload directory at $uploadDir");
            }
        }

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
        if (!empty($imageList)) {
            mysqli_query($conn, "UPDATE properties SET images='$imageList' WHERE property_id='$property_id'");
        }

        // Redirect after successful property update
        header("Location: ../manageproperties/crudindex.php");
        exit();
    } else {
        $error = 'Failed to update property. Please try again.';
    }
}

ob_end_flush(); // Flush the output buffer
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Property - SmartLease</title>
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

        .carousel {
            position: relative;
            width: 100%;
            height: 200px;
            overflow: hidden;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .carousel img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: none;
        }

        .carousel img.active {
            display: block;
        }

        .carousel button {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
        }

        .carousel .prev {
            left: 10px;
        }

        .carousel .next {
            right: 10px;
        }

        .image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }

        .image-preview {
            position: relative;
            width: 100px;
            height: 100px;
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 5px;
        }

        .image-preview button {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(255, 0, 0, 0.7);
            color: white;
            border: none;
            padding: 5px;
            cursor: pointer;
            border-radius: 3px;
        }

        .image-preview button:hover {
            background: rgba(255, 0, 0, 1);
        }

        .image-preview.removed {
            opacity: 0.5;
            filter: grayscale(100%);
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
        <h2>Edit Property</h2>
        <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
        <form action="edit.php?property_id=<?php echo $property_id; ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="deleted_images" id="deleted_images">
            <div class="form-container">
                <div class="group-box">
                    <h3>Property Details</h3>
                    <div class="form-group">
                        <label>Property Name:</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($property['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Description:</label>
                        <textarea name="description" required><?php echo htmlspecialchars($property['description']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Location:</label>
                        <input type="text" name="location" value="<?php echo htmlspecialchars($property['location']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Full Address:</label>
                        <textarea name="full_address" required><?php echo htmlspecialchars($property['full_address']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Price:</label>
                        <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($property['price']); ?>" required>
                    </div>
                </div>
                <div class="group-box">
                    <h3>Additional Details</h3>
                    <div class="form-group">
                        <label>Bedrooms:</label>
                        <input type="number" name="bedrooms" value="<?php echo htmlspecialchars($property['bedrooms']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Bathrooms:</label>
                        <input type="number" name="bathrooms" value="<?php echo htmlspecialchars($property['bathrooms']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Amenities:</label>
                        <textarea name="amenities"><?php echo htmlspecialchars($property['amenities']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Sale Status:</label>
                        <select name="sale_status">
                            <option value="for_rent" <?php echo $property['sale_status'] == 'for_rent' ? 'selected' : ''; ?>>For Rent</option>
                            <option value="for_sale" <?php echo $property['sale_status'] == 'for_sale' ? 'selected' : ''; ?>>For Sale</option>
                            <option value="sold" <?php echo $property['sale_status'] == 'sold' ? 'selected' : ''; ?>>Sold</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Add Images:</label>
                        <div id="image-upload-container">
                            <input type="file" name="images[]" multiple>
                        </div>
                    </div>

                    <div class="image-preview-container">
                        <?php foreach ($imagePaths as $index => $image) { ?>
                            <div class="image-preview">
                                <img src="../../propertypictures/uploads/<?php echo htmlspecialchars($image); ?>" alt="Property Image">
                                <button type="button" class="delete-image" data-image="<?php echo htmlspecialchars($image); ?>">Remove</button>
                            </div>
                        <?php } ?>
                    </div>

                    <!-- <div class="carousel">
                        <?php foreach ($imagePaths as $index => $image) { ?>
                            <img src="../../propertypictures/uploads/<?php echo htmlspecialchars($image); ?>" 
                                class="<?php echo $index === 0 ? 'active' : ''; ?>" 
                                alt="Property Image">
                        <?php } ?>
                        <button class="prev">&#10094;</button>
                        <button class="next">&#10095;</button>
                    </div> -->
                </div>
            </div>
            <button type="submit">Update Property</button>
        </form>
    </div>

    <script>
    // document.querySelectorAll('.carousel').forEach(carousel => {
    //     let images = carousel.querySelectorAll('img');
    //     let currentIndex = 0;

    //     carousel.querySelector('.next').addEventListener('click', () => {
    //         images[currentIndex].classList.remove('active');
    //         currentIndex = (currentIndex + 1) % images.length;
    //         images[currentIndex].classList.add('active');
    //     });

    //     carousel.querySelector('.prev').addEventListener('click', () => {
    //         images[currentIndex].classList.remove('active');
    //         currentIndex = (currentIndex - 1 + images.length) % images.length;
    //         images[currentIndex].classList.add('active');
    //     });
    // });

    document.querySelectorAll('.delete-image').forEach(button => {
        button.addEventListener('click', function() {
            const imageName = this.getAttribute('data-image');
            let deletedImagesInput = document.getElementById('deleted_images');
            let deletedImages = deletedImagesInput.value ? deletedImagesInput.value.split(',') : [];
            deletedImages.push(imageName);
            deletedImagesInput.value = deletedImages.join(',');

            this.parentElement.classList.add('removed');
        });
    });
    </script>

</body>
</html>
