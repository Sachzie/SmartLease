
<?php

$base_url = "/SmartLease/landlord";
?>
<header>
    <div class="nav">
        <div class="nav-left">
            <div class="hamburger" onclick="toggleMenu()">☰</div>
            <div class="logo">
                <a href="<?php echo $base_url; ?>/home.php">SmartLease</a>
            </div>
        </div>
    </div>  

    <nav id="menu">
        <button class="close-btn" onclick="toggleMenu()">× Close</button>
        <ul>
            <li><a href="<?php echo $base_url; ?>/home.php">Dashboard</a></li>
            <li class="dropdown">
                <a href="#" onclick="toggleDropdown(event)">Manage Properties ▾</a>
                <ul class="submenu">
                    <li><a href="<?php echo $base_url; ?>/manageproperties/crudindex.php">View Properties</a></li>
                    <li><a href="<?php echo $base_url; ?>/manageproperties/create.php">Create Property</a></li>
                </ul>
            </li>
            <li><a href="<?php echo $base_url; ?>/applications/viewapplications.php">View Applications</a></li>
            <li><a href="<?php echo $base_url; ?>/viewtenants/tenantsview.php">View Tenants</a></li>
            
            <!-- Payments Section -->
            <li class="dropdown">
                <a href="#" onclick="toggleDropdown(event)">Payments ▾</a>
                <ul class="submenu">
                    <li><a href="<?php echo $base_url; ?>/payments/billing.php">Payment Notices</a></li>
                    <li><a href="<?php echo $base_url; ?>/payments/confirmation.php">Payment Confirmation</a></li>
                </ul>
            </li>

            <li><a href="<?php echo $base_url; ?>/maintenance/maintenance.php">Maintenance Requests</a></li>
            <li><a href="<?php echo $base_url; ?>/reports/reports.php">Reports</a></li>
            <li><a href="<?php echo $base_url; ?>/profile/edit.php">Edit Profile</a></li>
            <li><a href="<?php echo $base_url; ?>/maintenance/logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<style>
    /* General Styles */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Nunito Sans', sans-serif;
    }

    body {
        background: rgb(248, 243, 217);
        padding-top: 70px; /* Ensures content does not overlap with the fixed navbar */
    }

    /* Sticky Navigation Bar */
    .nav {
        background: #8B5A2B;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 15px 20px;
        color: white;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 1000; /* Ensures it stays above other elements */
    }

    /* Logo beside Menu */
    .nav-left {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .hamburger {
        font-size: 26px;
        cursor: pointer;
        padding: 10px;
        background: #A67B5B;
        border-radius: 8px;
        width: 50px;
        text-align: center;
        transition: background 0.3s;
    }

    .hamburger:hover {
        background: #6F4E37;
    }

    /* SmartLease Logo */
    .logo a {
        text-decoration: none;
        font-size: 26px;
        font-weight: bold;
        color:White;
    }

    /* Sidebar Menu */
    #menu {
        position: fixed;
        left: -280px;
        top: 0;
        width: 280px;
        height: 100%;
        background: #A67B5B;
        color: white;
        transition: left 0.4s ease-in-out;
        padding-top: 20px;
        box-shadow: 3px 0 15px rgba(0, 0, 0, 0.3);
        border-top-right-radius: 12px;
        border-bottom-right-radius: 12px;
        z-index: 999; /* Ensures it appears above the content */
    }

    #menu ul {
        list-style: none;
        padding: 0;
    }

    #menu ul li {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid rgba(80, 75, 56, 0.2);
        position: relative;
    }

    #menu ul li a {
        color: white;
        text-decoration: none;
        font-size: 18px;
        display: block;
        padding-left: 20px;
        transition: all 0.3s ease-in-out;
    }

    #menu ul li a:hover {
        background: #6F4E37;
        padding-left: 25px;
        border-radius: 5px;
    }

    /* Dropdown Styles */
    .dropdown .submenu {
        display: none;
        max-height: 0;
        transition: max-height 0.3s ease-in-out;
        background: rgb(235, 229, 194);
    }

    .dropdown.active .submenu {
        display: block;
        max-height: 250px;
    }

    .submenu li {
        border-bottom: 1px solid rgba(80, 75, 56, 0.2);
    }

    .submenu li a {
        padding: 12px 25px;
        display: block;
    }

    .submenu li a:hover {
        background: rgb(185, 178, 138);
    }

    /* Close Button */
    .close-btn {
        background: rgb(80, 75, 56);
        color: white;
        border: none;
        padding: 12px;
        font-size: 18px;
        cursor: pointer;
        display: block;
        margin: 15px auto;
        width: 85%;
        text-align: center;
        border-radius: 8px;
        transition: background 0.3s ease-in-out;
    }

    .close-btn:hover {
        background: rgb(185, 178, 138);
    }
</style>

<script>
    function toggleMenu() {
        var menu = document.getElementById('menu');
        menu.style.left = menu.style.left === '0px' ? '-280px' : '0px';
    }

    function toggleDropdown(event) {
        event.preventDefault();
        var dropdown = event.target.closest('.dropdown');
        dropdown.classList.toggle('active');
    }
</script>
