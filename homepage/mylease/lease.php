<?php
session_start();
include('../../includes/config.php');

// Ensure tenant is logged in
if (!isset($_SESSION['tenant_id'])) {
    header("Location: ../../login.php");
    exit();
}

$tenant_id = $_SESSION['tenant_id']; // Tenant's unique ID

// Fetch tenant's lease details from SmartLease database
$query = "
    SELECT l.lease_id, l.start_date, l.end_date, l.rent_amount, l.status, 
           p.name AS property_name, p.location, p.full_address, p.price, p.bedrooms, 
           p.bathrooms, p.amenities, p.images, 
           la.name AS landlord_name, la.phone AS landlord_phone
    FROM leases l
    JOIN properties p ON l.property_id = p.property_id
    JOIN landlords la ON l.landlord_id = la.landlord_id
    WHERE l.tenant_id = '$tenant_id' 
    LIMIT 1";


$result = mysqli_query($conn, $query) or die(mysqli_error($conn));
$lease = mysqli_fetch_assoc($result);

// Redirect if no lease found
if (!$lease) {
    echo "<p class='error'>No lease found for your account.</p>";
    exit();
}

// Process images
$imagePaths = explode(',', $lease['images']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Lease - SmartLease</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Nunito Sans', sans-serif;
        }
        
        body {
            background: #f4f4f4;
            padding: 30px;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .details {
            margin-bottom: 15px;
        }

        .details p {
            margin: 8px 0;
            font-size: 16px;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            text-transform: capitalize;
        }

        .active { background: #28a745; color: white; }
        .completed { background: #007bff; color: white; }
        .terminated { background: #dc3545; color: white; }

        .carousel {
            position: relative;
            width: 100%;
            height: 250px;
            overflow: hidden;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .carousel img {
            width: 100%;
            height: 250px;
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

        .carousel .prev { left: 10px; }
        .carousel .next { right: 10px; }
    </style>
</head>
<body>

<div class="container">
    <h2>My Lease Details</h2>

    <!-- Property Images Carousel -->
    <div class="carousel">
        <?php foreach ($imagePaths as $index => $image) { ?>
            <img src="../../propertypictures/uploads/<?php echo htmlspecialchars($image); ?>" 
                 class="<?php echo $index === 0 ? 'active' : ''; ?>" 
                 alt="Property Image">
        <?php } ?>
        <button class="prev">&#10094;</button>
        <button class="next">&#10095;</button>
    </div>

    <div class="details">
        <h3>Property Information</h3>
        <p><strong>Property:</strong> <?php echo htmlspecialchars($lease['property_name']); ?></p>
        <p><strong>Location:</strong> <?php echo htmlspecialchars($lease['location']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($lease['full_address']); ?></p>
        <p><strong>Price:</strong> $<?php echo number_format($lease['price'], 2); ?></p>
        <p><strong>Bedrooms:</strong> <?php echo $lease['bedrooms']; ?></p>
        <p><strong>Bathrooms:</strong> <?php echo $lease['bathrooms']; ?></p>
        <p><strong>Amenities:</strong> <?php echo nl2br(htmlspecialchars($lease['amenities'])); ?></p>
    </div>

    <div class="details">
        <h3>Lease Information</h3>
        <p><strong>Start Date:</strong> <?php echo date('F d, Y', strtotime($lease['start_date'])); ?></p>
        <p><strong>End Date:</strong> <?php echo date('F d, Y', strtotime($lease['end_date'])); ?></p>
        <p><strong>Rent Amount:</strong> $<?php echo number_format($lease['rent_amount'], 2); ?> / month</p>
        <p class="status <?php echo $lease['status']; ?>">
            Lease Status: <?php echo ucfirst($lease['status']); ?>
        </p>
    </div>

    <div class="details">
        <h3>Landlord Information</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($lease['landlord_name']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($lease['landlord_phone']); ?></p>
    </div>
</div>

<script>
    document.querySelectorAll('.carousel').forEach(carousel => {
        let images = carousel.querySelectorAll('img');
        let currentIndex = 0;

        carousel.querySelector('.next').addEventListener('click', () => {
            images[currentIndex].classList.remove('active');
            currentIndex = (currentIndex + 1) % images.length;
            images[currentIndex].classList.add('active');
        });

        carousel.querySelector('.prev').addEventListener('click', () => {
            images[currentIndex].classList.remove('active');
            currentIndex = (currentIndex - 1 + images.length) % images.length;
            images[currentIndex].classList.add('active');
        });
    });
</script>

</body>
</html>
