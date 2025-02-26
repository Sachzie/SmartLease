<?php
session_start();
include('../../includes/config.php');
include('../../includes/header.php');

// Ensure a property_id is passed
if (!isset($_GET['property_id'])) {
    echo "Property not found.";
    exit();
}
$property_id = intval($_GET['property_id']);

// Fetch the property details
$query = mysqli_query($conn, "SELECT * FROM properties WHERE property_id = '$property_id'") or die(mysqli_error($conn));
if (mysqli_num_rows($query) === 0) {
    echo "Property not found.";
    exit();
}
$property = mysqli_fetch_assoc($query);

// Explode the comma-separated images (if any)
$images = [];
if (!empty($property['images'])) {
    $images = array_map('trim', explode(',', $property['images']));
}

$showApplyButton = false;
$profileIncomplete = false;

if (isset($_SESSION['tenant_id'])) {
    $tenant_id = $_SESSION['tenant_id'];
    
    // Fetch tenant details (excluding non-existent 'valid_id')
    $tenantQuery = mysqli_query($conn, "SELECT phone, picture FROM tenants WHERE tenant_id = '$tenant_id'") or die(mysqli_error($conn));
    
    if ($tenantData = mysqli_fetch_assoc($tenantQuery)) {
        if (empty($tenantData['phone']) || empty($tenantData['picture'])) {
            $profileIncomplete = true; // Mark profile as incomplete if phone or picture is missing
        } else {
            $showApplyButton = true; // Profile is complete, show the Apply button
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($property['name']); ?> - Property Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        /* Basic Reset & Layout */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            background: #f4e1c5;
            color: #333;
            padding-top: 80px; /* Adjust if header occupies space */
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            display: flex;
            flex-wrap: wrap;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .left-column {
            flex: 1;
            min-width: 300px;
            padding: 20px;
            border-right: 1px solid #ddd;
            position: relative;
        }
        .right-column {
            flex: 1;
            min-width: 300px;
            padding: 20px;
        }
        .image-viewer {
            width: 100%;
            height: 400px;
            background: #000;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }
        .image-viewer img {
            max-width: 100%;
            max-height: 100%;
        }
        .arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0,0,0,0.5);
            color: #fff;
            border: none;
            font-size: 2em;
            padding: 10px;
            cursor: pointer;
            z-index: 10;
        }
        .arrow-left {
            left: 10px;
        }
        .arrow-right {
            right: 10px;
        }
        .thumbnails {
            display: flex;
            justify-content: center;
            margin-top: 10px;
            gap: 10px;
        }
        .thumbnails img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            cursor: pointer;
            border: 2px solid transparent;
        }
        .thumbnails img.active {
            border-color: #8b5a2b;
        }
        .property-details h1 {
            margin-bottom: 20px;
        }
        .property-details p {
            margin-bottom: 10px;
            line-height: 1.6;
        }
        .apply-button {
            display: block;
            background: #8b5a2b;
            color: white;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
            font-size: 18px;
            margin-top: 20px;
            width: 100%;
        }
        .apply-button:hover {
            background: #a06a3b;
        }
        .warning {
            background: #ffcc00;
            color: #333;
            padding: 10px;
            text-align: center;
            margin-top: 10px;
            font-weight: bold;
        }
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .left-column {
                border-right: none;
                border-bottom: 1px solid #ddd;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Left Column: Image Gallery -->
    <div class="left-column">
        <div class="image-viewer">
            <?php if (!empty($images)) { ?>
                <img id="mainImage" src="../../propertypictures/uploads/<?php echo htmlspecialchars($images[0]); ?>" alt="Property Image">
                <?php if(count($images) > 1) { ?>
                    <button class="arrow arrow-left" onclick="prevImage()">&lt;</button>
                    <button class="arrow arrow-right" onclick="nextImage()">&gt;</button>
                <?php } ?>
            <?php } else { ?>
                <p style="color:#fff;">No image available.</p>
            <?php } ?>
        </div>
        <?php if (count($images) > 1) { ?>
        <div class="thumbnails">
            <?php foreach ($images as $index => $img) { ?>
                <img src="../../propertypictures/uploads/<?php echo htmlspecialchars($img); ?>" alt="Thumbnail" onclick="showImage(<?php echo $index; ?>)" id="thumb-<?php echo $index; ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>">
            <?php } ?>
        </div>
        <?php } ?>
    </div>

    <!-- Right Column: Property Details -->
    <div class="right-column property-details">
        <h1><?php echo htmlspecialchars($property['name']); ?></h1>
        <p><strong>Price:</strong> ₱<?php echo number_format($property['price'], 2); ?></p>
        <p><strong>Location:</strong> <?php echo htmlspecialchars($property['location']); ?></p>
        <p><strong>Bedrooms:</strong> <?php echo htmlspecialchars($property['bedrooms']); ?></p>
        <p><strong>Bathrooms:</strong> <?php echo htmlspecialchars($property['bathrooms']); ?></p>
        <p><strong>Availability:</strong> <?php echo $property['availability'] === 'available' ? 'Available' : 'Rented'; ?></p>
        <p><strong>Description:</strong></p>
        <p><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>

        <!-- Apply for Lease Button -->
        <?php if (isset($_SESSION['tenant_id'])) { ?>
            <?php if ($profileIncomplete) { ?>
                <p class="warning">⚠️ Please complete your profile (phone number and valid ID) before applying.</p>
            <?php } elseif ($showApplyButton) { ?>
                <a href="../leaseview/leaseapply.php?property_id=<?php echo $property_id; ?>" class="apply-button">Apply for Lease</a>
            <?php } ?>
        <?php } else { ?>
            <p class="warning">⚠️ You must be logged in as a tenant to apply.</p>
        <?php } ?>
    </div>
</div>

<script>
    var images = <?php echo json_encode($images); ?>;
    var currentIndex = 0;
    function showImage(index) {
        document.getElementById('mainImage').src = "../../propertypictures/uploads/" + images[index];
    }
    function prevImage() { showImage((currentIndex = (currentIndex - 1 + images.length) % images.length)); }
    function nextImage() { showImage((currentIndex = (currentIndex + 1) % images.length)); }
</script>

</body>
</html>
