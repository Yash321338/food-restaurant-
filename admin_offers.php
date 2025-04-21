<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "restaurnt (2)";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add new offer
    if (isset($_POST['add_offer'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $expiry_date = $_POST['expiry_date'];
        
        // Handle image upload
        $image = "";
        if(isset($_FILES['offer_image']) && $_FILES['offer_image']['error'] == 0) {
            $target_dir = "uploads/";
            
            // Create upload directory if it doesn't exist
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $target_file = $target_dir . basename($_FILES["offer_image"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            // Check if image file is an actual image
            $check = getimagesize($_FILES["offer_image"]["tmp_name"]);
            if($check !== false) {
                // Generate unique filename to prevent overwriting
                $image = $target_dir . uniqid() . '.' . $imageFileType;
                
                if (move_uploaded_file($_FILES["offer_image"]["tmp_name"], $image)) {
                    // File uploaded successfully
                } else {
                    $error_message = "Sorry, there was an error uploading your file.";
                }
            } else {
                $error_message = "File is not an image.";
            }
        }
        
        $sql = "INSERT INTO offers (title, description, price, expiry_date, image)
                VALUES ('$title', '$description', '$price', '$expiry_date', '$image')";
        
        if ($conn->query($sql) === TRUE) {
            $success_message = "New offer created successfully";
        } else {
            $error_message = "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    
    // Delete offer
    if (isset($_POST['delete_offer'])) {
        $id = $_POST['offer_id'];
        
        // Get image path before deleting
        $img_sql = "SELECT image FROM offers WHERE id=$id";
        $img_result = $conn->query($img_sql);
        if ($img_result->num_rows > 0) {
            $img_row = $img_result->fetch_assoc();
            if (!empty($img_row['image']) && file_exists($img_row['image'])) {
                unlink($img_row['image']); // Delete image file
            }
        }
        
        $sql = "DELETE FROM offers WHERE id=$id";
        
        if ($conn->query($sql) === TRUE) {
            $success_message = "Offer deleted successfully";
        } else {
            $error_message = "Error deleting offer: " . $conn->error;
        }
    }
    
    // Update offer
    if (isset($_POST['update_offer'])) {
        $id = $_POST['offer_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $expiry_date = $_POST['expiry_date'];
        
        // Handle image upload on update
        $image_sql = "";
        if(isset($_FILES['offer_image']) && $_FILES['offer_image']['error'] == 0) {
            $target_dir = "uploads/";
            
            // Create upload directory if it doesn't exist
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $target_file = $target_dir . basename($_FILES["offer_image"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            // Check if image file is an actual image
            $check = getimagesize($_FILES["offer_image"]["tmp_name"]);
            if($check !== false) {
                // Get old image path
                $old_img_sql = "SELECT image FROM offers WHERE id=$id";
                $old_img_result = $conn->query($old_img_sql);
                if ($old_img_result->num_rows > 0) {
                    $old_img_row = $old_img_result->fetch_assoc();
                    if (!empty($old_img_row['image']) && file_exists($old_img_row['image'])) {
                        unlink($old_img_row['image']); // Delete old image file
                    }
                }
                
                // Generate unique filename
                $new_image = $target_dir . uniqid() . '.' . $imageFileType;
                
                if (move_uploaded_file($_FILES["offer_image"]["tmp_name"], $new_image)) {
                    $image_sql = ", image='$new_image'";
                } else {
                    $error_message = "Sorry, there was an error uploading your file.";
                }
            } else {
                $error_message = "File is not an image.";
            }
        }
        
        $sql = "UPDATE offers SET 
                title='$title', 
                description='$description', 
                price='$price', 
                expiry_date='$expiry_date'
                $image_sql 
                WHERE id=$id";
        
        if ($conn->query($sql) === TRUE) {
            $success_message = "Offer updated successfully";
        } else {
            $error_message = "Error updating offer: " . $conn->error;
        }
    }
}

// Get all offers
$sql = "SELECT * FROM offers ORDER BY expiry_date DESC";
$result = $conn->query($sql);

// Get single offer for editing
$edit_offer = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_sql = "SELECT * FROM offers WHERE id = $edit_id";
    $edit_result = $conn->query($edit_sql);
    if ($edit_result->num_rows > 0) {
        $edit_offer = $edit_result->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Offers Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet"> 
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background-color: #333;
            color: white;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="number"],
        input[type="date"],
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea {
            height: 100px;
        }
        .btn {
            display: inline-block;
            padding: 8px 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-danger {
            background-color: #f44336;
        }
        .btn-primary {
            background-color: #2196F3;
        }
        .btn-back {
            background-color: #555;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
        }
        .offer-image {
            max-width: 150px;
            max-height: 100px;
            border-radius: 4px;
            object-fit: cover;
        }
        .image-preview {
            margin-top: 10px;
            max-width: 200px;
            max-height: 150px;
        }
    </style>
</head>

<body>
    <div class="content">
        <div class="header">
            <h1>Admin Offers Dashboard</h1>
            <a href="admin_dashboard.php" class="btn btn-back"><i class="fas fa-arrow-left"></i> Back to Main</a>
        </div>
        
        <?php if (isset($success_message)): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>
    
        <div class="card">
            <h2><?php echo $edit_offer ? 'Edit Offer' : 'Add New Offer'; ?></h2>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                <?php if ($edit_offer): ?>
                    <input type="hidden" name="offer_id" value="<?php echo $edit_offer['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="title">Title:</label>
                    <input type="text" id="title" name="title" required 
                           value="<?php echo $edit_offer ? $edit_offer['title'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" required><?php echo $edit_offer ? $edit_offer['description'] : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="price">Price:</label>
                    <input type="number" id="price" name="price" step="0.01" required 
                           value="<?php echo $edit_offer ? $edit_offer['price'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="expiry_date">Expiry Date:</label>
                    <input type="date" id="expiry_date" name="expiry_date" required 
                           value="<?php echo $edit_offer ? $edit_offer['expiry_date'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="offer_image">Image:</label>
                    <input type="file" id="offer_image" name="offer_image" accept="image/*">
                    
                    <?php if ($edit_offer && !empty($edit_offer['image'])): ?>
                        <div style="margin-top: 10px;">
                            <p>Current image:</p>
                            <img src="<?php echo $edit_offer['image']; ?>" class="image-preview" alt="Offer Image">
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($edit_offer): ?>
                    <button type="submit" name="update_offer" class="btn btn-primary">Update Offer</button>
                    <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="btn">Cancel</a>
                <?php else: ?>
                    <button type="submit" name="add_offer" class="btn">Add Offer</button>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="card">
            <h2>Current Offers</h2>
            <?php if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Expiry Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row["id"]; ?></td>
                                <td>
                                    <?php if (!empty($row["image"]) && file_exists($row["image"])): ?>
                                        <img src="<?php echo $row["image"]; ?>" class="offer-image" alt="Offer Image">
                                    <?php else: ?>
                                        <span>No image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $row["title"]; ?></td>
                                <td><?php echo $row["description"]; ?></td>
                                <td>$<?php echo number_format($row["price"], 2); ?></td>
                                <td><?php echo $row["expiry_date"]; ?></td>
                                <td>
                                    <a href="?edit=<?php echo $row["id"]; ?>" class="btn btn-primary">Edit</a>
                                    <form method="post" style="display: inline-block;" 
                                          onsubmit="return confirm('Are you sure you want to delete this offer?');">
                                        <input type="hidden" name="offer_id" value="<?php echo $row["id"]; ?>">
                                        <button type="submit" name="delete_offer" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No offers found in the database.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Preview image before upload
        document.getElementById('offer_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    // Remove any existing preview
                    const existingPreview = document.querySelector('.image-preview-new');
                    if (existingPreview) {
                        existingPreview.remove();
                    }
                    
                    // Create new preview
                    const imgPreview = document.createElement('img');
                    imgPreview.src = e.target.result;
                    imgPreview.classList.add('image-preview', 'image-preview-new');
                    imgPreview.style.marginTop = '10px';
                    
                    const previewContainer = document.createElement('div');
                    previewContainer.innerHTML = '<p>New image preview:</p>';
                    previewContainer.appendChild(imgPreview);
                    
                    document.getElementById('offer_image').parentNode.appendChild(previewContainer);
                }
                
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>

<?php
// Close connection
$conn->close();
?>