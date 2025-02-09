<?php
include 'db.php';

// Function to log activity
function logActivity($userId, $action, $details) {
    global $conn;
    // Fetch the username from the session
    $username = $_SESSION['user']['username'];

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

// Handle adding a new product
if (isset($_POST['add_product'])) {
    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $shelf = $_POST['shelf'];
    $units_in_stock = $_POST['units_in_stock'];

    // Insert the new product into the database
    $sql = "INSERT INTO products (product_name, category, shelf, units_in_stock) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssi', $product_name, $category, $shelf, $units_in_stock);
    $stmt->execute();

    // Log activity
    $userId = $_SESSION['user']['id'];
    $action = "added product";
    $details = "Product: $product_name, Category: $category, Shelf: $shelf, Stock: $units_in_stock";
    logActivity($userId, $action, $details);

    $_SESSION['message'] = 'Product added successfully!';
    header('Location: manage_products.php');
    exit();
}

// Handle removing a product
if (isset($_POST['remove_product'])) {
    $product_id = (int)$_POST['product_id'];

    // Get the product name before removing it (for logging purposes)
    $sql = "SELECT product_name FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $product_id);

    if ($stmt->execute()) {
        // Log activity
        $userId = $_SESSION['user']['id'];
        $action = "removed product";
        $details = "Product: " . $product['product_name'];
        logActivity($userId, $action, $details);

        $_SESSION['message'] = 'Product removed successfully!';
    } else {
        $_SESSION['message'] = 'Error removing product.';
    }

    // Redirect to prevent form resubmission
    header('Location: manage_products.php');
    exit();
}

// Handle updating a product
if (isset($_POST['update_product'])) {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $shelf = $_POST['shelf'];
    $units_in_stock = $_POST['units_in_stock'];

    $sql = "UPDATE products SET product_name = ?, category = ?, shelf = ?, units_in_stock = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssii', $product_name, $category, $shelf, $units_in_stock, $product_id);
    $stmt->execute();

    // Log activity
    $userId = $_SESSION['user']['id'];
    $action = "updated product";
    $details = "Product ID: $product_id, New Name: $product_name, New Category: $category, New Shelf: $shelf, New Stock: $units_in_stock";
    logActivity($userId, $action, $details);

    $_SESSION['message'] = 'Product updated successfully!';
    header('Location: manage_products.php');
    exit();
}

// Fetch products from the database
$sql = "SELECT * FROM products";
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
    <title>Manage Products</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <!-- DataTables CSS -->
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

        <h2 class="text-center mb-4">Manage Products</h2>

        <?php
        if (isset($_SESSION['message'])) {
            echo "<div class='alert alert-info'>{$_SESSION['message']}</div>";
            unset($_SESSION['message']); // Clear the message after displaying it
        }
        ?>

        <!-- Add Product Button -->
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addProductModal">Add Product</button>

        <!-- Products Table -->
        <table id="productsTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Shelf</th>
                    <th>Units in Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td><?php echo htmlspecialchars($row['shelf']); ?></td>
                        <td><?php echo htmlspecialchars($row['units_in_stock']); ?></td>
                        <td>
                            <!-- Edit Product Button (opens the modal) -->
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editProductModal" 
                                    data-id="<?php echo $row['id']; ?>" 
                                    data-name="<?php echo $row['product_name']; ?>" 
                                    data-category="<?php echo $row['category']; ?>" 
                                    data-shelf="<?php echo $row['shelf']; ?>" 
                                    data-stock="<?php echo $row['units_in_stock']; ?>">Edit</button>

                            <!-- Remove Product Form -->
                            <form action="" method="POST" style="display:inline;">
                                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="remove_product" class="btn btn-danger btn-sm">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <!-- Back to Dashboard Button -->
        <a href="dashboard.php" class="btn btn-secondary btn-sm">Back to Dashboard</a>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="manage_products.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="product_name" class="form-label">Product Name</label>
                            <input type="text" name="product_name" id="product_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select name="category" id="category" class="form-select" required>
                                <option value="coffee">Coffee</option>
                                <option value="tea">Tea</option>
                                <option value="pastries">Pastries</option>
                                <option value="sandwiches">Sandwiches</option>
                                <option value="beverages">Beverages</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="shelf" class="form-label">Shelf</label>
                            <input type="text" name="shelf" id="shelf" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="units_in_stock" class="form-label">Units in Stock</label>
                            <input type="number" name="units_in_stock" id="units_in_stock" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="manage_products.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="product_id" id="edit_product_id">
                        <div class="mb-3">
                            <label for="edit_product_name" class="form-label">Product Name</label>
                            <input type="text" name="product_name" id="edit_product_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_category" class="form-label">Category</label>
                            <select name="category" id="edit_category" class="form-select" required>
                                <option value="coffee">Coffee</option>
                                <option value="tea">Tea</option>
                                <option value="pastries">Pastries</option>
                                <option value="sandwiches">Sandwiches</option>
                                <option value="beverages">Beverages</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_shelf" class="form-label">Shelf</label>
                            <input type="text" name="shelf" id="edit_shelf" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_units_in_stock" class="form-label">Units in Stock</label>
                            <input type="number" name="units_in_stock" id="edit_units_in_stock" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_product" class="btn btn-primary">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and DataTables JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#productsTable').DataTable();

            // Populate the edit modal with product data
            $('#editProductModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget); // Button that triggered the modal
                var productId = button.data('id');
                var productName = button.data('name');
                var category = button.data('category');
                var shelf = button.data('shelf');
                var stock = button.data('stock');

                var modal = $(this);
                modal.find('#edit_product_id').val(productId);
                modal.find('#edit_product_name').val(productName);
                modal.find('#edit_category').val(category);
                modal.find('#edit_shelf').val(shelf);
                modal.find('#edit_units_in_stock').val(stock);
            });
        });
    </script>
</body>
</html>
