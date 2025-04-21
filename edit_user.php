<?php
session_start();

// Ensure the user is logged in and is an admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

include 'config/db_connect.php';

// Check if user ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("No user ID provided");
}

$user_id = mysqli_real_escape_string($conn, $_GET['id']);

// Fetch user details
$sql = "SELECT id, username, email, is_admin FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    die("User not found");
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    // Validate username
    if (empty($username)) {
        $error = "Username cannot be empty";
    }

    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    }

    // Update user if no errors
    if (!isset($error)) {
        $update_sql = "UPDATE users SET 
                       username = '$username', 
                       email = '$email', 
                       is_admin = $is_admin 
                       WHERE id = '$user_id'";
        
        if (mysqli_query($conn, $update_sql)) {
            $success = "User updated successfully";
        } else {
            $error = "Error updating user: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
<?php include 'admin_sidebar.php'; ?>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="card">
                    <div class="card-header">
                        <h3>Edit User Details</h3>
                    </div>
                    <div class="card-body">
                        <?php 
                        // Display success or error messages
                        if (isset($success)) {
                            echo "<div class='alert alert-success'>" . htmlspecialchars($success) . "</div>";
                        }
                        if (isset($error)) {
                            echo "<div class='alert alert-danger'>" . htmlspecialchars($error) . "</div>";
                        }
                        ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="is_admin" name="is_admin" 
                                       <?php echo $user['is_admin'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_admin">Admin User</label>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="manage_users.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Users
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update User
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
<?php
// Close the database connection
mysqli_close($conn);
?>