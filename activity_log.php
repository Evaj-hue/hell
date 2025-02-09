<?php
include 'db.php';


// Check if the user is logged in and has a valid role
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'moderator'])) {
    header('Location: index.php');
    exit();
}

// Fetch activity log from the database
$sql = "SELECT id, username, action, details, created_at FROM activity_log ORDER BY created_at DESC";
$result = $conn->query($sql);

// Check for query errors
if (!$result) {
    die("Error fetching activity log: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="navbar navbar-expand-lg navbar-dark bg-success mb-4 p-3 rounded">
            <div class="container-fluid">
                <a class="navbar-brand" href="dashboard.php">Dashboard</a>
                <div class="d-flex">
                    <span class="navbar-text me-3">Welcome, <?php echo htmlspecialchars($_SESSION['user']['username']); ?>!</span>
                    <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
                </div>
            </div>
        </div>

        <h2 class="text-center mb-4">Activity Log</h2>

        <div class="card shadow-lg p-4">
            <div class="table-responsive">
                <table id="activityLogTable" class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['action']); ?></td>
                                <td><?php echo htmlspecialchars($row['details']); ?></td>
                                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS, jQuery, and DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#activityLogTable').DataTable({
                "paging": true,       // Enables pagination
                "searching": true,    // Enables search bar
                "ordering": true,     // Enables sorting
                "info": true,         // Shows table info
                "lengthMenu": [5, 10, 25, 50, 100], // Dropdown for number of rows
                "pageLength": 10      // Default rows per page
            });
        });
    </script>
</body>
</html>
