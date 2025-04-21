<?php
session_start();
require_once 'config/db_connect.php'; // Include your database connection

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user details from database
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email, profile_image, created_at, is_admin FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
} else {
    session_destroy();
    header("Location: login.php");
    exit();
}
$stmt->close();
?>

<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <div class="card p-4">
        <div class="text-center mb-4">
            <img src="<?php echo !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : 'assets/images/default.png'; ?>" 
                 class="rounded-circle" width="150" height="150" alt="Profile Image">
            <h2>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h2>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Account Created:</strong> <?php echo htmlspecialchars($user['created_at']); ?></p>
        </div>

        <?php if ($user['is_admin'] == 1): ?>
            <a href="admin_dashboard.php" class="btn btn-primary w-100 mb-2">Go to Admin Dashboard</a>
        <?php else: ?>
            <a href="edit_profile.php" class="btn btn-primary w-100 mb-2">Edit Profile</a>
        <?php endif; ?>

        <a href="logout.php" class="btn btn-danger w-100">Logout</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'footer.php'; ?>
</body>
</html>