<?php
session_start();
include('../includes/config.php');
include('../includes/header.php');

$tenant_id = $_SESSION['tenant_id'];
$query = mysqli_query($conn, "SELECT name, email, date_of_birth FROM tenants WHERE tenant_id = '$tenant_id'") or die('Query failed');
$tenant = mysqli_fetch_assoc($query);

$res_Uname = isset($tenant['name']) ? $tenant['name'] : '';
$res_Email = isset($tenant['email']) ? $tenant['email'] : '';
$showNotification = empty($tenant['date_of_birth']);

$location = isset($_GET['location']) ? $_GET['location'] : '';
$bedrooms = isset($_GET['bedrooms']) ? $_GET['bedrooms'] : '';
$bathrooms = isset($_GET['bathrooms']) ? $_GET['bathrooms'] : '';
$price = isset($_GET['price']) ? $_GET['price'] : '';

$query = "SELECT property_id, name, location, bedrooms, bathrooms, price, availability, images FROM properties WHERE 1=1";

if ($location) {
    $query .= " AND location LIKE '%$location%'";
}
if ($bedrooms) {
    $query .= " AND bedrooms = '$bedrooms'";
}
if ($bathrooms) {
    $query .= " AND bathrooms = '$bathrooms'";
}
if ($price) {
    if ($price == '0-5000') {
        $query .= " AND price BETWEEN 0 AND 5000";
    } elseif ($price == '5000-10000') {
        $query .= " AND price BETWEEN 5000 AND 10000";
    } elseif ($price == '10000-20000') {
        $query .= " AND price BETWEEN 10000 AND 20000";
    } elseif ($price == '20000+') {
        $query .= " AND price > 20000";
    }
}

$properties = mysqli_query($conn, $query) or die('Query failed');
$property_details = [];
while ($row = mysqli_fetch_assoc($properties)) {
    $property_details[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SmartLease - Tenant Home</title>
  <style>
    /* Basic Reset */
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body {
      font-family: Arial, sans-serif;
      background-color: #f4e1c5;
      color: #5a3e2b;
      padding-top: 80px; /* Adjust if your included header occupies space */
    }
    .container {
      max-width: 1200px;
      margin: 20px auto;
      padding: 0 20px;
    }
    /* Search Bar Styles */
    .search-container {
      margin: 20px 0;
      display: flex;
      justify-content: center;
    }
    .search-bar {
      background: #e6c8a0;
      padding: 15px;
      border-radius: 8px;
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      align-items: center;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .search-bar input[type="text"],
    .search-bar select {
      padding: 10px;
      border: 1px solid #8b5a2b;
      border-radius: 5px;
      min-width: 150px;
    }
    .search-bar button {
      padding: 10px 15px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      color: white;
    }
    .search-btn {
      background-color: #8b5a2b;
    }
    .clear-btn {
      background-color: #cc704b;
    }
    h1 {
      text-align: center;
      margin-bottom: 20px;
    }
    /* Grid Container for Property Cards */
    .grid-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
    }
    .card {
      background: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      transition: transform 0.2s;
    }
    .card:hover {
      transform: translateY(-5px);
    }
    .card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      cursor: pointer;
    }
    .card-content {
      padding: 15px;
    }
    .card-content h2 {
      font-size: 1.5em;
      margin-bottom: 10px;
    }
    .card-content p {
      margin: 8px 0;
    }
    /* Check Button Styles */
    .check-btn {
      display: block;
      width: fit-content;
      margin: 15px auto;
      padding: 10px 20px;
      background: #8b5a2b;
      color: #fff;
      text-decoration: none;
      border-radius: 5px;
      font-weight: bold;
    }
    /* Modal Styles for Enlarged Image */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.8);
      justify-content: center;
      align-items: center;
    }
    .modal img {
      max-width: 80%;
      max-height: 80%;
      border-radius: 10px;
    }
    .modal-close {
      position: absolute;
      top: 20px;
      right: 30px;
      font-size: 30px;
      color: white;
      cursor: pointer;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="search-container">
    <form class="search-bar" method="GET" action="home.php">
      <input type="text" name="location" id="location" placeholder="Location" value="<?php echo htmlspecialchars($location); ?>">
      <label for="bedrooms">Bedrooms:</label>
      <select id="bedrooms" name="bedrooms">
        <option value="">Any</option>
        <option value="1" <?php if ($bedrooms == '1') echo 'selected'; ?>>1</option>
        <option value="2" <?php if ($bedrooms == '2') echo 'selected'; ?>>2</option>
        <option value="3" <?php if ($bedrooms == '3') echo 'selected'; ?>>3+</option>
      </select>
      <label for="bathrooms">Bathrooms:</label>
      <select id="bathrooms" name="bathrooms">
        <option value="">Any</option>
        <option value="1" <?php if ($bathrooms == '1') echo 'selected'; ?>>1</option>
        <option value="2" <?php if ($bathrooms == '2') echo 'selected'; ?>>2</option>
        <option value="3" <?php if ($bathrooms == '3') echo 'selected'; ?>>3+</option>
      </select>
      <label for="price">Price Range:</label>
      <select id="price" name="price">
        <option value="">Any</option>
        <option value="0-5000" <?php if ($price == '0-5000') echo 'selected'; ?>>₱0 - ₱5,000</option>
        <option value="5000-10000" <?php if ($price == '5000-10000') echo 'selected'; ?>>₱5,000 - ₱10,000</option>
        <option value="10000-20000" <?php if ($price == '10000-20000') echo 'selected'; ?>>₱10,000 - ₱20,000</option>
        <option value="20000+" <?php if ($price == '20000+') echo 'selected'; ?>>₱20,000+</option>
      </select>
      <button type="button" class="clear-btn" onclick="clearSearch()">Clear</button>
      <button type="submit" class="search-btn">Search</button>
    </form>
  </div>

  <h1>Available Properties</h1>

  <div class="grid-container">
    <?php foreach ($property_details as $details) { 
      // Split the images string and use the first image
      $images = explode(',', $details['images']);
      $propertyImage = !empty($images[0]) 
          ? '../propertypictures/uploads/' . trim($images[0]) 
          : '../propertypictures/uploads/default.jpg';
    ?>
      <div class="card">
        <img src="<?php echo $propertyImage; ?>" alt="Property Image" onclick="openModal(this.src)">
        <div class="card-content">
          <h2><?php echo htmlspecialchars($details['name']); ?></h2>
          <p><strong>Price:</strong> ₱<?php echo number_format($details['price'], 2); ?></p>
          <p><strong>Location:</strong> <?php echo htmlspecialchars($details['location']); ?></p>
          <p><strong>Bedrooms:</strong> <?php echo htmlspecialchars($details['bedrooms']); ?></p>
          <p><strong>Bathrooms:</strong> <?php echo htmlspecialchars($details['bathrooms']); ?></p>
          <p><strong>Availability:</strong> <?php echo $details['availability'] ? 'Available' : 'Not Available'; ?></p>
          <!-- Check button below each card -->
          <a href="/SmartLease/homepage/leaseview/view.php?property_id=<?php echo $details['property_id']; ?>" class="check-btn">Check</a>
        </div>
      </div>
    <?php } ?>
  </div>
</div>

<!-- Modal for Enlarged Image -->
<div class="modal" id="imageModal">
  <span class="modal-close" onclick="closeModal()">&times;</span>
  <img id="modalImage" src="" alt="Enlarged Property Image">
</div>

<script>
  function clearSearch() {
    document.getElementById('location').value = '';
    document.getElementById('bedrooms').value = '';
    document.getElementById('bathrooms').value = '';
    document.getElementById('price').value = '';
    window.location.href = 'home.php';
  }
  function searchProperties() {
    // Implement search functionality as needed.
  }
  function openModal(src) {
    document.getElementById('modalImage').src = src;
    document.getElementById('imageModal').style.display = 'flex';
  }
  function closeModal() {
    document.getElementById('imageModal').style.display = 'none';
  }
  window.onclick = function(event) {
    if (event.target === document.getElementById('imageModal')) {
      closeModal();
    }
  }
</script>

</body>
</html>
