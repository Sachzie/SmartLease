<?php
session_start();
include('../../includes/config.php');
include('../../includes/landlordheader.php');


$landlord_id = $_SESSION['landlord_id'];

// Fetch properties
$query = mysqli_query($conn, "SELECT property_id, name, description, location, full_address, price, bedrooms, bathrooms, amenities, availability, sale_status, images FROM properties WHERE landlord_id = '$landlord_id'") 
or die(mysqli_error($conn));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Properties - SmartLease</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Nunito Sans', sans-serif;
        }

        body {
            background: rgb(248, 243, 217);
            padding: 30px;
        }

        .container {
            max-width: 1200px;
            margin: 60px auto 0;
            background: rgb(235, 229, 194);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .property-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .property-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
            position: relative;
        }

        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
        }

        .carousel {
            position: relative;
            width: 100%;
            height: 200px;
            overflow: hidden;
            border-radius: 8px;
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

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            text-transform: capitalize;
        }

        .available { background: #28a745; color: white; }
        .rented { background: #dc3545; color: white; }
        .for_rent { background: #007bff; color: white; }
        .for_sale { background: #ffc107; color: black; }
        .sold { background: #6c757d; color: white; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Manage Properties</h2>
    </div>

    <div class="property-grid">
        <?php while ($property = mysqli_fetch_assoc($query)) { 
            $imagePaths = explode(',', $property['images']); 
        ?>
            <div class="property-card">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3><?php echo htmlspecialchars($property['name']); ?></h3>
                    <div>
                        <a href="edit.php?property_id=<?php echo $property['property_id']; ?>" style="padding: 5px 10px; background-color:rgb(255, 191, 0); color: white; border: none; border-radius: 5px; cursor: pointer; text-decoration: none;">
                            Edit
                        </a>
                        <a href="delete.php?property_id=<?php echo $property['property_id']; ?>" style="padding: 5px 10px; background-color:rgb(220, 53, 69); color: white; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; margin-left: 10px;">
                            Delete
                        </a>
                    </div>
                </div>
                <div class="carousel">
                    <?php foreach ($imagePaths as $index => $image) { ?>
                        <img src="../../propertypictures/uploads/<?php echo htmlspecialchars($image); ?>" 
                            class="<?php echo $index === 0 ? 'active' : ''; ?>" 
                            alt="Property Image">
                    <?php } ?>
                    <button class="prev">&#10094;</button>
                    <button class="next">&#10095;</button>
                </div>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($property['location']); ?></p>
                <p><strong>Price:</strong> $<?php echo number_format($property['price'], 2); ?></p>
                <p><strong>Bedrooms:</strong> <?php echo $property['bedrooms']; ?></p>
                <p><strong>Bathrooms:</strong> <?php echo $property['bathrooms']; ?></p>
                <p class="status <?php echo $property['availability']; ?>">
                    <?php echo ucfirst($property['availability']); ?>
                </p>
                <p class="status <?php echo $property['sale_status']; ?>">
                    <?php echo str_replace("_", " ", ucfirst($property['sale_status'])); ?>
                </p>
            </div>
        <?php } ?>
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


