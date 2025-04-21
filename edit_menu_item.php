<?php
include 'config/db_connect.php';
session_start();

if (!isset($_GET['id'])) {
    header("Location: manage_menu.php");
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Fetch categories for dropdown
$categories_sql = "SELECT id, name FROM categories";
$categories_result = mysqli_query($conn, $categories_sql);

// Fetch existing menu item data
$sql = "SELECT * FROM menu_items WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    header("Location: manage_menu.php");
    exit();
}

$menu_item = $result->fetch_assoc();
$item_name = $menu_item['item_name'];
$category_id = $menu_item['category_id'];
$quantity = $menu_item['quantity'];
$availability = $menu_item['availability'];
$item_image = $menu_item['item_image'];
$price = $menu_item['price'];

$errors = array('item_name' => '', 'category_id' => '', 'quantity' => '', 'availability' => '', 'item_image' => '', 'price' => '');
$success_message = '';

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
                $item_image = $destination;
            } else {
                $errors['item_image'] = "Error uploading file";
            }
        }
    }

    // If no errors, update database
    if (!array_filter($errors)) {
        $sql = "UPDATE menu_items SET item_name = ?, category_id = ?, quantity = ?, availability = ?, price = ?";
        $params = [$item_name, $category_id, $quantity, $availability, $price];
        $types = "siisd";
        
        if(!empty($item_image)) {
            $sql .= ", item_image = ?";
            $params[] = $item_image;
            $types .= "s";
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;
        $types .= "i";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Menu item updated successfully!";
            header("Location: manage_menu.php");
            exit();
        } else {
            $error_message = "Error updating menu item. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Menu Item</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        input[type="number"] {
            text-align: right;
        }
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            opacity: 1;
        }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <a href="manage_menu.php" class="btn btn-secondary mb-3">
                    <i class="fas fa-arrow-left"></i> Back to Menu
                </a>

                <div class="card">
                    <div class="card-header">
                        <h4>Edit Menu Item</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="item_name" class="form-label">Item Name</label>
                                <input type="text" class="form-control" id="item_name" name="item_name" 
                                       value="<?php echo htmlspecialchars($item_name); ?>">
                                <div class="text-danger"><?php echo $errors['item_name']; ?></div>
                            </div>

                            <div class="mb-3">
                                <label for="category_id" class="form-label">Category</label>
                                <select class="form-select" id="category_id" name="category_id">
                                    <option value="">Select Category</option>
                                    <?php 
                                    mysqli_data_seek($categories_result, 0);
                                    while ($category = mysqli_fetch_assoc($categories_result)): 
                                    ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                            <?php echo ($category_id == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <div class="text-danger"><?php echo $errors['category_id']; ?></div>
                            </div>

                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" min="0"
                                       value="<?php echo htmlspecialchars($quantity); ?>">
                                <div class="text-danger"><?php echo $errors['quantity']; ?></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Availability</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="availability" 
                                           id="available" value="available" 
                                           <?php echo ($availability == 'available') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="available">Available</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="availability" 
                                           id="not_available" value="not_available"
                                           <?php echo ($availability == 'not_available') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="not_available">Not Available</label>
                                </div>
                                <div class="text-danger"><?php echo $errors['availability']; ?></div>
                            </div>

                            <div class="mb-3">
                                <label for="price" class="form-label">Price (₹)</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="price" 
                                       name="price" 
                                       min="0" 
                                       step="0.01" 
                                       value="<?php echo htmlspecialchars($price); ?>"
                                       style="text-align: right;">
                                <div class="text-danger"><?php echo $errors['price']; ?></div>
                            </div>

                            <div class="mb-3">
                                <label for="item_image" class="form-label">Item Image</label>
                                <?php if ($item_image): ?>
                                    <div class="mb-2">
                                        <img src="<?php echo htmlspecialchars($item_image); ?>" 
                                             alt="Current Image" style="max-width: 200px;">
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="item_image" name="item_image" accept="image/*" onchange="previewImage(this)">
                                <div class="text-danger"><?php echo $errors['item_image']; ?></div>
                                <img id="preview" class="mt-2" style="max-width: 200px; display: none;">
                            </div>

                            <button type="submit" class="btn btn-primary">Update Menu Item</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Add this to your existing JavaScript
document.getElementById('price').addEventListener('input', function(e) {
    if (this.value < 0) {
        this.value = 0;
    }
});
</script>
</body>
</html> 