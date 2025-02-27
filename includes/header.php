<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base_url = "/SmartLease/homepage";
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
            <li class="menu-header">Dashboard</li>
            <li><a href="<?php echo $base_url; ?>/home.php">📊 Overview</a></li>
            
            <li class="menu-header">Lease Management</li>
            <li><a href="<?php echo $base_url; ?>/mylease/lease.php">🏠 My Lease</a></li>
            <li><a href="<?php echo $base_url; ?>/lease/payments.php">💰 Payments</a></li>
            
            <li class="menu-header">Maintenance & Support</li>
            <li><a href="<?php echo $base_url; ?>/maintenance/requests.php">🛠 Maintenance Requests</a></li>
            <li><a href="<?php echo $base_url; ?>/support/messages.php">📩 Messages</a></li>
            
            <li class="menu-header">Account</li>
            <li><a href="/SmartLease/homepage/profile/profileedit.php">👤 Edit Profile</a></li>
            <li><a href="/SmartLease/landlord/maintenance/logout.php" class="logout">🚪 Logout</a></li>
        </ul>
    </nav>
</header>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: Arial, sans-serif;
    }

    header {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 1000;
    }

    body {
        padding-top: 60px;
        background-color: #f4f4f4;
    }

    .nav {
        background: #8B5A2B;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 15px 20px;
        color: white;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

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

    .logo a {
        text-decoration: none;
        font-size: 26px;
        font-weight: bold;
        color: white;
    }

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
    }

    #menu ul {
        list-style: none;
        padding: 0;
    }

    #menu ul li {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    #menu ul li a {
        color: white;
        text-decoration: none;
        font-size: 18px;
        display: block;
        padding-left: 20px;
        transition: 0.3s;
    }

    #menu ul li a:hover {
        background: #6F4E37;
        padding-left: 25px;
        border-radius: 5px;
    }

    .menu-header {
        font-size: 20px;
        font-weight: bold;
        color: #ffcc00;
        padding: 15px 20px;
        text-transform: uppercase;
        background: rgba(255, 255, 255, 0.1);
    }

    .logout {
        background: #8B5A2B;
        display: block;
        text-align: center;
        padding: 15px;
        border-radius: 8px;
        margin-top: 10px;
        transition: background 0.3s;
    }

    .logout:hover {
        background: #6F4E37;
    }

    .close-btn {
        background: #8B5A2B;
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
        transition: 0.3s;
    }

    .close-btn:hover {
        background: #6F4E37;
    }
</style>

<script>
    function toggleMenu() {
        var menu = document.getElementById('menu');
        menu.style.left = menu.style.left === '0px' ? '-280px' : '0px';
    }
</script>
