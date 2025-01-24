<?php  
// Include db.php which handles the session start
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: index.php'); // Redirect to login page if not logged in
    exit();
}

// Fetch the logged-in user's information
$user = $_SESSION['user'];

// Ensure the user is a staff member
if ($user['role'] !== 'user') {
    header('Location: user.php'); // Redirect to dashboard if not a staff user
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>USER Dashboard</title>
</head>

<body>
    
    <nav>
        <div class="logo">
            <div class="logo-image">
                <img src="logo.png" alt="">
            </div>
        </div>
        <div class="menu-items">
            <ul class="navLinks">
                <li class="navList active">
                    <a href="#">
                        <ion-icon name="home-outline"></ion-icon>
                        <span class="links">Dashboard</span>
                    </a>
                </li>
               
                <li class="navList">
                    <a href="#">
                        <ion-icon name="analytics-outline"></ion-icon>
                        <span class="links">View Products</span>
                    </a>
                </li>
              
                <li class="navList">
                    <a href="#">
                        <ion-icon name="chatbubbles-outline"></ion-icon>
                        <span class="links">Reports</span>
                    </a>
                </li>

                <li class="navList">
                    <a href="#">
                    <ion-icon name="file-tray-stacked-outline"></ion-icon>
                        <span class="links">Racks</span>
                    </a>
                </li>
            </ul>
            <ul class="bottom-link">
                <li>
                    <a href="#">
                        <ion-icon name="person-circle-outline"></ion-icon>
                        <span class="links">Profile</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <ion-icon name="log-out-outline"></ion-icon>
                        <span class="links">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <section class="dashboard">
        <div class="container">
            <div class="overview">
                <div class="title">
                    <ion-icon name="speedometer"></ion-icon>
                    <span class="text">Dashboard</span>
                </div>
                <div class="boxes">
                    <div class="box box1">
                        <ion-icon name="eye-outline"></ion-icon>
                        <span class="text">Total Views</span>
                        <span class="number">18345</span>
                    </div>
                    <div class="box box2">
                        <ion-icon name="people-outline"></ion-icon>
                        <span class="text">Active users</span>
                        <span class="number">2745</span>
                    </div>
                    <div class="box box3">
                        <ion-icon name="chatbubbles-outline"></ion-icon>
                        <span class="text">Total Activities</span>
                        <span class="number">1209</span>
                    </div>
                    <div class="box box4">
                        <ion-icon name="car-sport-outline"></ion-icon>
                        <span class="text">Insured Vehicles</span>
                        <span class="number">123</span>
                    </div>
                </div> 
            </div>
            

            <!-- Recent Activities -->
            <div class="data-table activityTable">
                <div class="title">
                    <ion-icon name="time-outline"></ion-icon>
                    <span class="text">Recent Activities</span>
                </div>
                <div>
                    <!-- Enter any table or section here -->
                </div>
            </div>

            
            <!-- Content -->
            <div style="display:none" class="data-table userDetailsTable">
                <div class="title">
                <ion-icon name="folder-outline"></ion-icon>      
                    <span class="text">Products</span>
                </div>
                <div>
                     <!-- Dynamic Table -->
        <table class="product-table" border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Shelf</th>
                    <th>Units in Stock</th>
                    <th>Added at</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Include the database connection
                

                // Fetch products data
                $sql = "SELECT id, product_name, category, shelf, units_in_stock, created_at FROM products";
                $result = $conn->query($sql);

                // Check if there are results
                if ($result->num_rows > 0) {
                    // Output data for each row
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['product_name']}</td>
                                <td>{$row['category']}</td>
                                <td>{$row['shelf']}</td>
                                <td>{$row['units_in_stock']}</td>
                                <td>{$row['created_at']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No products found</td></tr>";
                }
                ?>
            </tbody>
        </table>
                </div>
            </div>

            <!-- Analytics -->
            <div style="display:none" class="data-table EditUserRole">
                <div class="title">
                    <ion-icon name="analytics-outline"></ion-icon>
                    <span class="text">Activity log</span>
                </div>
                <div>
                    <!-- Enter any table or section here -->
                </div>
            </div>

            <!--  Likes -->
            <div style="display:none" class="data-table VehicleDetails">
                <div class="title">
                    <ion-icon name="heart-outline"></ion-icon>
                    <span class="text">Racks</span>
                </div>
                <div>
                    <!-- Enter any table or section here -->
                </div>
            </div>

            <!-- Downloads section -->
            <div style="display:none" class="data-table downloads">
                <div class="title">
                    <ion-icon name="chatbubbles-outline"></ion-icon>
                    <span class="text">Comments</span>
                </div>
                <div>
                    <!-- Enter any table or section here -->
                </div>
            </div>
        </div>
    </section>

    <script src="index.js"></script>
    
    <!-- Sources for icons -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    
</body>

</html>