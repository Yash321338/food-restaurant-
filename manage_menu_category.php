<?php
include 'config/db_connect.php';
session_start();

// Add this near the top of the file, after session_start()
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}

$name = '';
$errors = array('name' => '');
$success_message = '';

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

    // If no errors, insert into database
    if (!array_filter($errors)) {
        // Prepare SQL statement for the "categories" table
        $sql = "INSERT INTO categories (name) VALUES (?)";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $name);

            if ($stmt->execute()) {
                $success_message = "Category added successfully!";
                // Clear form
                $name = '';
            } else {
                $error_message = "Error adding category. Please try again.";
            }

            // Close statement
            $stmt->close();
        } else {
            die("Failed to prepare SQL query: " . $conn->error);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Menu Categories</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
<style>
    /* Custom CSS additions */
    .content {
        margin-left: 250px; /* Adjust based on sidebar width */
        padding: 20px;
    }

    @media (max-width: 768px) {
        .content {
            margin-left: 0;
            padding: 15px;
        }
    }

    .card {
        border: none;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        border-radius: 0.35rem;
    }

    .card-header {
        background-color: #4e73df;
        color: white;
        border-radius: 0.35rem 0.35rem 0 0 !important;
        padding: 1rem 1.25rem;
    }

    .table {
        margin-top: 1rem;
        border-collapse: separate;
        border-spacing: 0 0.5rem;
    }

    .table thead th {
        background-color: #f8f9fc;
        color: #4e73df;
        border-bottom: 2px solid #e3e6f0;
    }

    .table tbody tr {
        background-color: white;
        transition: all 0.3s ease;
    }

    .table tbody tr:hover {
        transform: translateX(5px);
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }

    .btn-primary {
        background-color: #4e73df;
        border-color: #4e73df;
        padding: 0.375rem 1.25rem;
    }

    .btn-primary:hover {
        background-color: #2e59d9;
        border-color: #2e59d9;
    }

    .form-control {
        border-radius: 0.35rem;
        border: 1px solid #d1d3e2;
    }

    .form-control:focus {
        border-color: #bac8f3;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }

    .alert {
        border-radius: 0.35rem;
        padding: 0.75rem 1.25rem;
    }

    .table-responsive {
        border-radius: 0.35rem;
        overflow: hidden;
    }
</style>
<?php include 'admin_sidebar.php'; ?>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">
                        <h4>Manage Menu Categories</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success_message): ?>
                            <div class="alert alert-success">
                                <?php echo $success_message; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="mb-3">
                                <label for="name" class="form-label">Category Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>">
                                <div class="text-danger"><?php echo $errors['name']; ?></div>
                            </div>

                            <button type="submit" class="btn btn-primary">Save Category</button>
                        </form>

                        <div class="table-responsive mt-4">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Fetch categories from the database
                                    $sql = "SELECT id, name FROM categories";
                                    $result = mysqli_query($conn, $sql);
                                    while ($row = mysqli_fetch_assoc($result)) {
                                    ?>
                                    <tr data-category-id="<?php echo $row['id']; ?>">
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td>
                                            <a href="edit_category.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button class="btn btn-sm btn-danger" onclick="deleteCategory(<?php echo $row['id']; ?>)">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteCategory(categoryId) {
    if (confirm('Are you sure you want to delete this category?')) {
        const formData = new FormData();
        formData.append('category_id', categoryId);

        fetch('delete_category.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const row = document.querySelector(`tr[data-category-id="${categoryId}"]`);
                if (row) {
                    row.remove();
                }
                alert(data.message);
            } else {
                alert(data.message || 'Error deleting category');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting category');
        });
    }
}
</script>
</body>
</html>


