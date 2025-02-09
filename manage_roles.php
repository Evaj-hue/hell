<?php  
include 'db.php';

// Function to log activity
function logActivity($userId, $action, $details) {
    global $conn;

    // Get the username of the logged-in user
    $sql = "SELECT username FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $username = '';
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $username = $row['username'];
    }

    // Insert the activity log with username
    $sql = "INSERT INTO activity_log (user_id, username, action, details) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $userId, $username, $action, $details);
    $stmt->execute();
}

// Check if the user is logged in and has a valid role
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'moderator'])) {
    header('Location: index.php');
    exit();
}

// Logic for adding a new user
if (isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $full_name = $_POST['full_name'];
    $contact_number = $_POST['contact_number'];

    // Check for existing username or email
    $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Username or email already exists!'); window.location.href='manage_roles.php';</script>";
        exit();
    }

    // Insert the new user into the database
    $sql = "INSERT INTO users (username, email, password, role, full_name, contact_number, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'active')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $username, $email, $password, $role, $full_name, $contact_number);

    if ($stmt->execute()) {
        logActivity($_SESSION['user']['id'], "Add User", "Added user: $username");
        echo "<script>alert('New user added successfully!'); window.location.href='manage_roles.php';</script>";
    } else {
        echo "<script>alert('Error adding new user.'); window.location.href='manage_roles.php';</script>";
    }
    exit();
}

// Logic for changing roles
if (isset($_POST['change_role'])) {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['role'];
    
    // Update the user's role
    $sql = "UPDATE users SET role = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $new_role, $user_id);

    if ($stmt->execute()) {
        logActivity($_SESSION['user']['id'], "Change Role", "Updated user ID $user_id to role $new_role");
        echo "<script>alert('User role updated successfully!'); window.location.href='manage_roles.php';</script>";
    } else {
        echo "<script>alert('Error updating user role.'); window.location.href='manage_roles.php';</script>";
    }
    exit();
}

// Logic for removing/deactivating/reactivating users
if (isset($_GET['action']) && isset($_GET['user_id'])) {
    $action = $_GET['action'];
    $user_id = $_GET['user_id'];
    $new_status = '';
    
    if ($action === 'remove') {
        // Remove the user from the database
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        logActivity($_SESSION['user']['id'], "Remove User", "Removed user ID $user_id");
        echo "<script>alert('User removed successfully!'); window.location.href='manage_roles.php';</script>";
    } elseif ($action === 'deactivate') {
        // Set the user's status to 'inactive'
        $new_status = 'inactive';
        $sql = "UPDATE users SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $new_status, $user_id);
        $stmt->execute();
        logActivity($_SESSION['user']['id'], "Deactivate User", "Deactivated user ID $user_id");
        echo "<script>alert('User deactivated successfully!'); window.location.href='manage_roles.php';</script>";
    } elseif ($action === 'reactivate') {
        // Set the user's status to 'active'
        $new_status = 'active';
        $sql = "UPDATE users SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $new_status, $user_id);
        $stmt->execute();
        logActivity($_SESSION['user']['id'], "Reactivate User", "Reactivated user ID $user_id");
        echo "<script>alert('User reactivated successfully!'); window.location.href='manage_roles.php';</script>";
    }
    exit();
}

// Fetch all users from the database
$sql = "SELECT id, username, email, contact_number AS mobile, role, status, created_at, full_name FROM users";
$result = $conn->query($sql);

if (!$result) {
    die("Query Failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Roles</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="navbar navbar-expand-lg navbar-dark bg-success mb-4">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">Dashboard</a>
                <div class="d-flex">
                    <span class="navbar-text me-3">Welcome, <?php echo htmlspecialchars($_SESSION['user']['username']); ?>!</span>
                    <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
                </div>
            </div>
        </div>

        <h2 class="text-center mb-4">Manage User Roles</h2>

        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal">Add User</button>

        <table id="usersTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                     <th>Id</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Contact Number</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['mobile']); ?></td>
                        <td><?php echo htmlspecialchars($row['role']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        <td>
                            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#editUserModal"
                                    data-user-id="<?php echo $row['id']; ?>"
                                    data-username="<?php echo $row['username']; ?>"
                                    data-email="<?php echo $row['email']; ?>"
                                    data-full-name="<?php echo $row['full_name']; ?>"
                                    data-contact-number="<?php echo $row['mobile']; ?>"
                                    data-role="<?php echo $row['role']; ?>"
                                    data-status="<?php echo $row['status']; ?>">Edit</button>

                            <a href="manage_roles.php?action=remove&user_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Remove</a>

                            <?php if ($row['status'] !== 'inactive'): ?>
                                <a href="manage_roles.php?action=deactivate&user_id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Deactivate</a>
                            <?php endif; ?>

                            <?php if ($row['status'] === 'inactive'): ?>
                                <a href="manage_roles.php?action=reactivate&user_id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm">Reactivate</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <a href="dashboard.php" class="btn btn-secondary btn-sm">Back to Dashboard</a>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="manage_roles.php" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select name="role" class="form-select" required>
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                                <option value="moderator">Moderator</option>
                                <option value="restocker">Restocker</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="contact_number" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="contact_number" name="contact_number" required>
                        </div>
                        <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="manage_roles.php" method="POST">
                        <input type="hidden" id="user_id" name="user_id">
                        <div class="mb-3">
                            <label for="edit_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="edit_username" name="username" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit_full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="edit_full_name" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_contact_number" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="edit_contact_number" name="contact_number" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_role" class="form-label">Role</label>
                            <select name="role" class="form-select" id="edit_role" required>
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                                <option value="moderator">Moderator</option>
                                <option value="restocker">Restocker</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_status" class="form-label">Status</label>
                            <select name="status" class="form-select" id="edit_status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <button type="submit" name="change_role" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#usersTable').DataTable();
        });

        // Edit user modal data population
        $('#editUserModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var userId = button.data('user-id');
            var username = button.data('username');
            var email = button.data('email');
            var fullName = button.data('full-name');
            var contactNumber = button.data('contact-number');
            var role = button.data('role');
            var status = button.data('status');

            var modal = $(this);
            modal.find('#user_id').val(userId);
            modal.find('#edit_username').val(username);
            modal.find('#edit_email').val(email);
            modal.find('#edit_full_name').val(fullName);
            modal.find('#edit_contact_number').val(contactNumber);
            modal.find('#edit_role').val(role);
            modal.find('#edit_status').val(status);
        });
    </script>
</body>
</html>