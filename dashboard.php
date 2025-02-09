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



?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Admin Dashboard</title>
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
                        <ion-icon name="folder-outline"></ion-icon>
                        <span class="links">Manage Racks</span>
                    </a>
                </li>
                <li class="navList">
                    <a href="manage_roles.php" id="manageProductsLink">
                        <ion-icon name="analytics-outline"></ion-icon>
                        <span class="links">Role Management</span>
                    </a>
                </li>
                <li class="navList">
                    <a href="manage_products.php">
                        <ion-icon name="pricetags-outline"></ion-icon>
                        <span class="links">Product Management</span>
                    </a>
                </li>
                <li class="navList">
                    <a href="activity_log.php">
                        <ion-icon name="chatbubbles-outline"></ion-icon>
                        <span class="links">Reports</span>
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
            <div class="data-table rack">
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
                    <span class="text">Content</span>
                </div>
                <div>
                    <!-- Enter any table or section here -->
                </div>
            </div>

            <!-- Analytics -->
            <div style="display:none" class="data-table EditUserRole">
                <div class="title">
                    <ion-icon name="analytics-outline"></ion-icon>
                    <span class="text">USER ROLES</span>
                </div>
                <div>
                    <!-- Enter any table or section here -->
                </div>
            </div>

            <!--  Likes -->
            <div style="display:none" class="data-table VehicleDetails">
                <div class="title">
                    <ion-icon name="heart-outline"></ion-icon>
                    <span class="text">PRODUCTS</span>
                </div>
                <div>
                    <!-- Enter any table or section here -->
                </div>
            </div>

           <!-- Reports (activity_log) section -->
           <div style="display:none" class="data-table downloads">
                <div class="title">
                    <ion-icon name="chatbubbles-outline"></ion-icon>
                    <span class="text">Product Activity Log</span>
                </div>
         <div class="table-design">
       

                     <!-- Activity Log Table -->
         <table id="example" class="display" style="width:100%">
            <thead>
                <tr>
                    <th >ID</th>
                    <th >User</th>
                    <th >Action</th>
                    <th >Details</th>
                    <th >Timestamp</th>
                </tr>
            </thead>
            <tbody id="table-body">
                <?php
                // Fetch the activity log for product actions
                $sql = "SELECT al.id, u.username, al.action, al.details, al.created_at 
                        FROM activity_log al 
                        JOIN users u ON al.user_id = u.id 
                        WHERE al.action LIKE '%product%' 
                        ORDER BY al.created_at DESC";
                $result = $conn->query($sql);

                if (!$result) {
                    die("Query Failed: " . $conn->error);
                }

                while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td data-label="ID"><?php echo htmlspecialchars($row['id']); ?></td>
                        <td data-label="User"><?php echo htmlspecialchars($row['username']); ?></td>
                        <td data-label="Action"><?php echo htmlspecialchars($row['action']); ?></td>
                        <td data-label="Details"><?php echo htmlspecialchars($row['details']); ?></td>
                        <td data-label="Timestamp"><?php echo htmlspecialchars($row['created_at']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
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