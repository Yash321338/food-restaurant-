<?php
include 'config/db_connect.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

$name = '';
$errors = array('name' => '');
$success_message = '';

// Check if ID is provided in URL
if (!isset($_GET['id'])) {
    header("Location: manage_menu_category.php");
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Fetch existing category data
$sql = "SELECT * FROM categories WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    header("Location: manage_menu_category.php");
    exit();
}

$category = $result->fetch_assoc();
$name = $category['name'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    if (empty($_POST['name'])) {
        $errors['name'] = 'Category name is required';
    } else {
        $name = htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8');
        if (strlen($name) < 2 || strlen($name) > 50) {
            $errors['name'] = 'Name must be between 2 and 50 characters';
        }
    }

    // If no errors, update database
    if (!array_filter($errors)) {
        $sql = "UPDATE categories SET name = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $name, $id);

        if ($stmt->execute()) {
            $success_message = "Category updated successfully!";
        } else {
            $error_message = "Error updating category. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .content {
            margin-left: 250px;
            padding: 20px;
        }
        .card {
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .btn-back {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <a href="manage_menu_category.php" class="btn btn-secondary btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Categories
                </a>

                <div class="card">
                    <div class="card-header">
                        <h4>Edit Category</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success_message): ?>
                            <div class="alert alert-success">
                                <?php echo $success_message; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $id); ?>">
                            <div class="mb-3">
                                <label for="name" class="form-label">Category Name</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name" 
                                       name="name" 
                                       value="<?php echo htmlspecialchars($name); ?>">
                                <div class="text-danger"><?php echo $errors['name']; ?></div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Category
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 