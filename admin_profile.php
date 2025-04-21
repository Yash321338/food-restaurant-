<?php
session_start();
include 'config/db_connect.php';

// Ensure the user is logged in as an admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Get admin ID from session
$admin_id = $_SESSION['admin_id'] ?? 0;

// Fetch admin details from database
$stmt = $conn->prepare("SELECT 
    username, 
    email, 
    profile_image, 
    last_login, 
    'Administrator' as role, 
    created_at 
FROM admins 
WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

// Set default values if no record is found
if (!$admin) {
    $admin = [
        'username' => 'Admin',
        'email' => 'admin@gmail.com',
        'profile_image' => 'assets/images/',
        'last_login' => date('Y-m-d H:i:s'),
        'role' => 'Administrator',
        'created_at' => date('Y-m-d H:i:s')
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1a73e8;
            --secondary-color: #34d374;
            --bg-gradient: linear-gradient(135deg, #1a73e8 0%, #34d374 100%);
            --card-shadow: 0 12px 24px rgba(0,0,0,0.1);
        }

        body {
            background: #f0f2f5;
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .profile-container {
            max-width: 700px;
            margin: 3rem auto;
            perspective: 1000px;
        }

        .profile-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: all 0.4s ease-in-out;
            transform-style: preserve-3d;
        }

        .profile-header {
            background: var(--bg-gradient);
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
            position: relative;
        }

        .profile-img {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            border: 6px solid white;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }

        .profile-img:hover {
            transform: scale(1.05) rotate(3deg);
        }

        .profile-badge {
            background: rgba(255,255,255,0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            display: inline-block;
            margin-top: 1rem;
        }

        .profile-details {
            padding: 2rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid #f1f3f5;
            padding-bottom: 1rem;
        }

        .detail-item i {
            color: var(--primary-color);
            margin-right: 1rem;
            font-size: 1.5rem;
            width: 40px;
            text-align: center;
        }

        .btn-edit-profile {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }

        .btn-edit-profile:hover {
            background: #1557b0;
            transform: translateY(-3px);
            box-shadow: 0 7px 14px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>

    <div class="container profile-container">
        <div class="profile-card">
            <div class="profile-header">
                <img src="<?php echo htmlspecialchars($admin['profile_image']); ?>" 
                     class="profile-img" 
                     alt="Admin Profile"
                     onerror="this.src='assets/images/pic-3.png';">
                <h2 class="mt-3 mb-2"><?php echo htmlspecialchars($admin['username']); ?></h2>
                <div class="profile-badge">
                    <i class="fas fa-shield-alt me-2"></i>
                    <?php echo htmlspecialchars($admin['role']); ?>
                </div>
            </div>

            <div class="profile-details">
                <div class="detail-item">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <h6 class="mb-1 text-muted">Email Address</h6>
                        <p class="mb-0"><?php echo htmlspecialchars($admin['email']); ?></p>
                    </div>
                </div>

                <div class="detail-item">
                    <i class="fas fa-clock"></i>
                    <div>
                        <h6 class="mb-1 text-muted">Last Login</h6>
                        <p class="mb-0"><?php echo date('F j, Y g:i A', strtotime($admin['last_login'])); ?></p>
                    </div>
                </div>

                <div class="detail-item">
                    <i class="fas fa-calendar-alt"></i>
                    <div>
                        <h6 class="mb-1 text-muted">Account Created</h6>
                        <p class="mb-0"><?php echo date('F j, Y', strtotime($admin['created_at'])); ?></p>
                    </div>
                </div>

                <a href="admin_profile_edit.php" class="btn btn-edit-profile mt-3">
                    <i class="fas fa-edit me-2"></i> Edit Profile
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>