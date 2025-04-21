<?php
session_start();

// Ensure the user is logged in and is an admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

include 'config/db_connect.php';

// Fetch users from the database
$sql = "SELECT id, username, email, is_admin FROM users";
$result = mysqli_query($conn, $sql);
$total_users = mysqli_num_rows($result); // Count total users
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
<style>
    /* Custom CSS for Admin Users Management Page */
    body {
        background-color: #f4f6f9;
        font-family: 'Inter', 'Arial', sans-serif;
    }

    .content {
        padding: 20px;
        margin-left: 250px; /* Adjust based on your sidebar width */
    }

    .card {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        border: none;
    }

    .table {
        margin-bottom: 0;
    }

    .table thead {
        background-color: #f8f9fa;
        color: #495057;
        text-transform: uppercase;
        font-size: 0.9rem;
    }

    .table-striped tbody tr:nth-of-type(even) {
        background-color: rgba(0, 0, 255, 0.05);
    }

    .table td, .table th {
        vertical-align: middle;
        padding: 15px;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }

    .btn-info {
        background-color: #17a2b8;
        border-color: #17a2b8;
    }

    .btn-warning {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #212529;
    }

    .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
    }

    .total-users-count {
        color: #007bff;
        font-weight: bold;
        font-size: 1.2rem;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .content {
            margin-left: 0;
            padding: 10px;
        }

        .table-responsive {
            font-size: 0.9rem;
        }
    }

    /* Hover effects */
    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.075);
        transition: background-color 0.3s ease;
    }

    /* Action buttons styling */
    .btn-group .btn {
        margin-right: 5px;
    }
</style>
<?php include 'admin_sidebar.php'; ?>
<div class="content">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-12">
                <h2>Manage Users</h2>
                <p>Total Registered Users: <strong class="total-users-count"><?php echo $total_users; ?></strong></p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                    <tr data-user-id="<?php echo $row['id']; ?>">
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo $row['is_admin'] ? 'Admin' : 'User'; ?></td>
                                       
                                        <td>
                                            <a href="view_user.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info me-1">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="edit_user.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning me-1">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $row['id']; ?>)">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        fetch('delete_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'user_id=' + userId
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Remove the row from the table
                const row = document.querySelector(`tr[data-user-id="${userId}"]`);
                if (row) {
                    row.remove();
                    // Update total users count
                    const totalUsers = document.querySelector('.total-users-count');
                    if (totalUsers) {
                        totalUsers.textContent = parseInt(totalUsers.textContent) - 1;
                    }
                }
            } else {
                alert('Error deleting user: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting user');
        });
    }
}
</script>
</body>
</html>