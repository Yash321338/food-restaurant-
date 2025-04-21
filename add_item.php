<?php
// File to store menu data
$menuFile = 'menu.json';

// Initialize menu array
$menu = [];

// Check if the menu file exists
if (file_exists($menuFile)) {
    $menu = json_decode(file_get_contents($menuFile), true);
}

// Validate and process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $status = trim($_POST['status']);

    // Validation rules
    $errors = [];

    if (empty($name)) {
        $errors[] = 'Name is required.';
    }

    if (empty($category)) {
        $errors[] = 'Category is required.';
    }

    if (empty($description)) {
        $errors[] = 'Description is required.';
    }

    if (empty($price) || !is_numeric($price) || $price <= 0) {
        $errors[] = 'Price must be a valid positive number.';
    }

    if (empty($status)) {
        $errors[] = 'Status is required.';
    }

    // If no errors, add the item to the menu
    if (empty($errors)) {
        $newItem = [
            'id' => uniqid(), // Generate a unique ID
            'name' => $name,
            'category' => $category,
            'description' => $description,
            'price' => $price,
            'status' => $status,
        ];

        // Add the new item to the menu array
        $menu[] = $newItem;

        // Save the updated menu to the JSON file
        file_put_contents($menuFile, json_encode($menu));

        echo 'Item added successfully!';
    } else {
        // Display validation errors
        foreach ($errors as $error) {
            echo "<p style='color: red;'>$error</p>";
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Item</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Add New Item</h4>
                        <form action="add_item.php" method="POST">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="category">Category</label>
                                <select class="form-control" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="Appetizer">Appetizer</option>
                                    <option value="Main Course">Main Course</option>
                                    <option value="Dessert">Dessert</option>
                                    <option value="Beverage">Beverage</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="price">Price</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                            </div>
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="Available">Available</option>
                                    <option value="Unavailable">Unavailable</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Item</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>