<?php
include 'config/db_connect.php';
session_start();

// Initialize variables
$item_name = $category_id = $quantity = $availability = $item_image = $price = '';
$errors = array('item_name' => '', 'category_id' => '', 'quantity' => '', 'availability' => '', 'item_image' => '', 'price' => '');
$success_message = '';
$is_edit_mode = false;
$edit_id = null;

// Fetch categories for dropdown
$categories_sql = "SELECT id, name FROM categories";
$categories_result = mysqli_query($conn, $categories_sql);

// Check if we're editing an item
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $edit_id = mysqli_real_escape_string($conn, $_GET['id']);
    $is_edit_mode = true;
    
    // Fetch item data
    $fetch_sql = "SELECT * FROM menu_items WHERE id = ?";
    $stmt = $conn->prepare($fetch_sql);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $item_name = $row['item_name'];
        $category_id = $row['category_id'];
        $quantity = $row['quantity'];
        $availability = $row['availability'];
        $price = $row['price'];
        $item_image = $row['item_image'];
    }
    $stmt->close();
}

// Check if deleting an item
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Get image path before deleting
    $img_sql = "SELECT item_image FROM menu_items WHERE id = ?";
    $stmt = $conn->prepare($img_sql);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $image_to_delete = $row['item_image'];
    }
    $stmt->close();
    
    // Delete from database
    $delete_sql = "DELETE FROM menu_items WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        // Delete image file if it exists
        if (!empty($image_to_delete) && file_exists($image_to_delete)) {
            unlink($image_to_delete);
        }
        $success_message = "Menu item deleted successfully!";
    } else {
        $error_message = "Error deleting menu item.";
    }
    $stmt->close();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate item name
    if (empty($_POST['item_name'])) {
        $errors['item_name'] = 'Item name is required';
    } else {
        $item_name = htmlspecialchars(trim($_POST['item_name']), ENT_QUOTES, 'UTF-8');
    }

    // Validate category
    if (empty($_POST['category_id'])) {
        $errors['category_id'] = 'Category is required';
    } else {
        $category_id = htmlspecialchars(trim($_POST['category_id']), ENT_QUOTES, 'UTF-8');
    }

    // Validate quantity
    if (empty($_POST['quantity'])) {
        $errors['quantity'] = 'Quantity is required';
    } else {
        $quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);
        if ($quantity === false || $quantity < 0) {
            $errors['quantity'] = 'Please enter a valid quantity';
        }
    }

    // Validate availability
    if (!isset($_POST['availability'])) {
        $errors['availability'] = 'Availability status is required';
    } else {
        $availability = $_POST['availability'];
        if (!in_array($availability, ['available', 'not_available'])) {
            $errors['availability'] = 'Invalid availability status';
        }
    }

    // Validate price
    if (empty($_POST['price'])) {
        $errors['price'] = 'Price is required';
    } else {
        $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
        if ($price === false || $price < 0) {
            $errors['price'] = 'Please enter a valid price';
        }
    }

    // Handle image upload
    $update_image = false;
    if(isset($_FILES["item_image"]) && $_FILES["item_image"]["error"] == 0) {
        $allowed = ["jpg" => "image/jpg", "jpeg" => "image/jpeg", "png" => "image/png"];
        $filename = $_FILES["item_image"]["name"];
        $filetype = $_FILES["item_image"]["type"];
        $filesize = $_FILES["item_image"]["size"];

        // Verify file extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(!array_key_exists($ext, $allowed)) {
            $errors['item_image'] = "Please select a valid format (JPG, JPEG, PNG)";
        }

        // Verify file size - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if($filesize > $maxsize) {
            $errors['item_image'] = "File size is larger than 5MB";
        }

        // If no errors, process the image
        if(empty($errors['item_image'])) {
            $image_path = "uploads/menu_items/";
            if(!file_exists($image_path)) {
                mkdir($image_path, 0777, true);
            }

            // Generate unique filename
            $new_filename = uniqid() . "." . $ext;
            $destination = $image_path . $new_filename;

            if(move_uploaded_file($_FILES["item_image"]["tmp_name"], $destination)) {
                // If updating, delete old image
                if ($is_edit_mode && !empty($item_image) && file_exists($item_image)) {
                    unlink($item_image);
                }
                $item_image = $destination;
                $update_image = true;
            } else {
                $errors['item_image'] = "Error uploading file";
            }
        }
    }

    // If no errors, insert or update database
    if (!array_filter($errors)) {
        if ($is_edit_mode) {
            if ($update_image) {
                $sql = "UPDATE menu_items SET 
                        item_name = ?, 
                        category_id = ?, 
                        quantity = ?, 
                        availability = ?, 
                        price = ?, 
                        item_image = ? 
                        WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("siisdsi", $item_name, $category_id, $quantity, $availability, $price, $item_image, $edit_id);
            } else {
                $sql = "UPDATE menu_items SET 
                        item_name = ?, 
                        category_id = ?, 
                        quantity = ?, 
                        availability = ?, 
                        price = ? 
                        WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("siisdi", $item_name, $category_id, $quantity, $availability, $price, $edit_id);
            }
            
            if ($stmt->execute()) {
                $success_message = "Menu item updated successfully!";
                // Redirect to clear form and avoid resubmission
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $error_message = "Error updating menu item. Please try again.";
            }
        } else {
            $sql = "INSERT INTO menu_items (item_name, category_id, quantity, availability, price, item_image)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("siisds", $item_name, $category_id, $quantity, $availability, $price, $item_image);

            if ($stmt->execute()) {
                $success_message = "Menu item added successfully!";
                // Clear form
                $item_name = $category_id = $quantity = $availability = $item_image = $price = '';
                // Refresh the page to avoid resubmission
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $error_message = "Error adding menu item. Please try again.";
            }
        }
        $stmt->close();
    }
}

// Reset edit mode if needed
if (isset($_GET['action']) && $_GET['action'] == 'cancel') {
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch categories for dropdown (again, in case we return to the form)
$categories_result = mysqli_query($conn, $categories_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit_mode ? 'Edit' : 'Add'; ?> Menu Item</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
       /* Custom styles for menu item management page */
body {
    background-color: #f4f6f9;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.content {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 30px;
    margin: 20px;
}

h2, h3 {
    color: #2c3e50;
    border-bottom: 2px solid #3498db;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.form-control, .form-select {
    border-radius: 4px;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
}

.table {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.table thead {
    background-color: #f8f9fa;
}

.table td img {
    border-radius: 4px;
    transition: transform 0.3s ease;
}

.table td img:hover {
    transform: scale(1.1);
}

.action-buttons .btn {
    margin-right: 5px;
    transition: all 0.3s ease;
}

.action-buttons .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.btn-primary {
    background-color: #3498db;
    border-color: #3498db;
}

.btn-primary:hover {
    background-color: #2980b9;
    border-color: #2980b9;
}

.btn-warning {
    background-color: #f39c12;
    border-color: #f39c12;
    color: white;
}

.btn-warning:hover {
    background-color: #d35400;
    border-color: #d35400;
}

.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.modal-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #e9ecef;
}

.badge {
    font-size: 0.85em;
    padding: 0.4em 0.6em;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .content {
        margin: 10px;
        padding: 15px;
    }

    .action-buttons .btn {
        margin-bottom: 5px;
    }
}
    </style>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>
    <div class="content">
        <h2><?php echo $is_edit_mode ? 'Edit' : 'Add'; ?> Menu Item</h2>

        <?php if (isset($success_message) && !empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message) && !empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . ($is_edit_mode ? '?action=edit&id=' . $edit_id : '')); ?>" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="item_name" class="form-label">Item Name</label>
                <input type="text" class="form-control" id="item_name" name="item_name" value="<?php echo htmlspecialchars($item_name); ?>">
                <div class="text-danger"><?php echo $errors['item_name']; ?></div>
            </div>
            <div class="mb-3">
                <label for="category_id" class="form-label">Category</label>
                <select class="form-select" id="category_id" name="category_id">
                    <option value="">Select Category</option>
                    <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo ($category_id == $category['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <div class="text-danger"><?php echo $errors['category_id']; ?></div>
            </div>
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="quantity" name="quantity" min="0" value="<?php echo htmlspecialchars($quantity); ?>">
                <div class="text-danger"><?php echo $errors['quantity']; ?></div>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price ($)</label>
                <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" value="<?php echo htmlspecialchars($price); ?>">
                <div class="text-danger"><?php echo $errors['price']; ?></div>
            </div>
            <div class="mb-3">
                <label for="availability" class="form-label">Availability</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="availability" id="available" value="available" <?php echo ($availability == 'available') ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="available">Available</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="availability" id="not_available" value="not_available" <?php echo ($availability == 'not_available') ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="not_available">Not Available</label>
                </div>
                <div class="text-danger"><?php echo $errors['availability']; ?></div>
            </div>
            <div class="mb-3">
                <label for="item_image" class="form-label">Item Image</label>
                <?php if ($is_edit_mode && !empty($item_image) && file_exists($item_image)): ?>
                    <div class="mb-2">
                        <img src="<?php echo htmlspecialchars($item_image); ?>" alt="Current Image" style="width: 100px; height: 100px; object-fit: cover;">
                        <p class="text-muted">Current image. Upload a new one to replace it.</p>
                    </div>
                <?php endif; ?>
                <input type="file" class="form-control" id="item_image" name="item_image" accept="image/*">
                <div class="text-danger"><?php echo $errors['item_image']; ?></div>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo $is_edit_mode ? 'Update' : 'Save'; ?> Menu Item</button>
            <?php if ($is_edit_mode): ?>
                <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?action=cancel" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </form>

        <!-- Menu Items Table -->
        <h3 class="mt-4">Menu Items</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Availability</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $menu_sql = "SELECT m.*, c.name AS category_name
                             FROM menu_items m
                             JOIN categories c ON m.category_id = c.id";
                $menu_result = mysqli_query($conn, $menu_sql);
                while ($item = mysqli_fetch_assoc($menu_result)):
                ?>
                    <tr>
                        <td>
                            <?php if ($item['item_image'] && file_exists($item['item_image'])): ?>
                                <img src="<?php echo htmlspecialchars($item['item_image']); ?>" alt="Item Image" style="width: 80px; height: 80px; object-fit: cover;">
                            <?php else: ?>
                                <img src="images/" alt="No Image" style="width: 80px; height: 80px; object-fit: cover;">
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                        <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td>
                            <span class="badge <?php echo $item['availability'] == 'available' ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $item['availability'])); ?>
                            </span>
                        </td>
                        <td class="action-buttons">
                            <!-- View Button -->
                            <button type="button" class="btn btn-sm btn-info" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#viewModal<?php echo $item['id']; ?>">
                                <i class="fas fa-eye"></i> View
                            </button>
                            
                            <!-- Edit Button -->
                            <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?action=edit&id=<?php echo $item['id']; ?>" 
                               class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            
                            <!-- Delete Button -->
                            <button type="button" class="btn btn-sm btn-danger" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#deleteModal<?php echo $item['id']; ?>">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                            
                            <!-- View Modal -->
                            <div class="modal fade" id="viewModal<?php echo $item['id']; ?>" tabindex="-1" aria-labelledby="viewModalLabel<?php echo $item['id']; ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="viewModalLabel<?php echo $item['id']; ?>">
                                                <?php echo htmlspecialchars($item['item_name']); ?> Details
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="text-center mb-3">
                                                <?php if ($item['item_image'] && file_exists($item['item_image'])): ?>
                                                    <img src="<?php echo htmlspecialchars($item['item_image']); ?>" alt="Item Image" style="max-width: 200px; max-height: 200px; object-fit: contain;">
                                                <?php else: ?>
                                                    <img src="images/no-image.png" alt="No Image" style="max-width: 200px; max-height: 200px; object-fit: contain;">
                                                <?php endif; ?>
                                            </div>
                                            <table class="table">
                                                <tr>
                                                    <th>Item Name:</th>
                                                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Category:</th>
                                                    <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Price:</th>
                                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Quantity:</th>
                                                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Availability:</th>
                                                    <td>
                                                        <span class="badge <?php echo $item['availability'] == 'available' ? 'bg-success' : 'bg-danger'; ?>">
                                                            <?php echo ucfirst(str_replace('_', ' ', $item['availability'])); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    
                                               
                                                
                                            </table>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Delete Modal -->
                            <div class="modal fade" id="deleteModal<?php echo $item['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $item['id']; ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="deleteModalLabel<?php echo $item['id']; ?>">Confirm Deletion</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            Are you sure you want to delete <strong><?php echo htmlspecialchars($item['item_name']); ?></strong>? This action cannot be undone.
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?action=delete&id=<?php echo $item['id']; ?>" class="btn btn-danger">Delete</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>