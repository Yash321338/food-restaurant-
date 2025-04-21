<?php
session_start();

// Ensure the user is logged in and is an admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Database connection
include 'config/db_connect.php';

// Handle form submissions
$successMessage = '';
$errorMessage = '';

// General Settings Form Handling
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['general_settings'])) {
    $restaurantName = $conn->real_escape_string($_POST['restaurantName']);
    $contactEmail = $conn->real_escape_string($_POST['contactEmail']);
    $phoneNumber = $conn->real_escape_string($_POST['phoneNumber']);

    // Validate inputs
    if (empty($restaurantName) || empty($contactEmail) || empty($phoneNumber)) {
        $errorMessage = "All fields are required.";
    } else {
        $query = "UPDATE system_settings SET 
            restaurant_name = '$restaurantName', 
            contact_email = '$contactEmail', 
            phone_number = '$phoneNumber' 
            WHERE id = 1";
        
        if ($conn->query($query)) {
            $successMessage = "General settings updated successfully!";
        } else {
            $errorMessage = "Error updating settings: " . $conn->error;
        }
    }
}

// Business Hours Form Handling
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['business_hours'])) {
    $weekdayOpen = $conn->real_escape_string($_POST['weekday_open']);
    $weekdayClose = $conn->real_escape_string($_POST['weekday_close']);
    $weekendOpen = $conn->real_escape_string($_POST['weekend_open']);
    $weekendClose = $conn->real_escape_string($_POST['weekend_close']);

    $query = "UPDATE system_settings SET 
        weekday_open_time = '$weekdayOpen', 
        weekday_close_time = '$weekdayClose', 
        weekend_open_time = '$weekendOpen', 
        weekend_close_time = '$weekendClose' 
        WHERE id = 1";
    
    if ($conn->query($query)) {
        $successMessage = "Business hours updated successfully!";
    } else {
        $errorMessage = "Error updating business hours: " . $conn->error;
    }
}

// Fetch current settings
$settings = [];
$result = $conn->query("SELECT * FROM system_settings WHERE id = 1");
if ($result) {
    $settings = $result->fetch_assoc();
} else {
    $errorMessage = "Error fetching settings: " . $conn->error;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Panel</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            background-color: #f4f6f9;
        }
        .content {
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .card {
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: none;
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            background-color: #007bff;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-header h5 {
            margin: 0;
        }
        .form-label {
            font-weight: 600;
            color: #495057;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>

    <div class="content">
        <h1 class="mb-4"><i class="fas fa-cog"></i> System Settings</h1>

        <?php 
        // Display success or error messages
        if (!empty($successMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($successMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($errorMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> General Settings</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="general_settings" value="1">
                            <div class="mb-3">
                                <label for="restaurantName" class="form-label">Restaurant Name</label>
                                <input type="text" class="form-control" id="restaurantName" name="restaurantName" 
                                       value="<?php echo htmlspecialchars($settings['restaurant_name'] ?? 'Your Restaurant'); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="contactEmail" class="form-label">Contact Email</label>
                                <input type="email" class="form-control" id="contactEmail" name="contactEmail" 
                                       value="<?php echo htmlspecialchars($settings['contact_email'] ?? 'contact@restaurant.com'); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="phoneNumber" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" 
                                       value="<?php echo htmlspecialchars($settings['phone_number'] ?? '+1 234 567 8900'); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="far fa-clock"></i> Business Hours</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="business_hours" value="1">
                            <div class="mb-3">
                                <label class="form-label">Monday - Friday</label>
                                <div class="d-flex gap-2">
                                    <input type="time" class="form-control" name="weekday_open" 
                                           value="<?php echo htmlspecialchars($settings['weekday_open_time'] ?? '09:00'); ?>">
                                    <span class="align-self-center">to</span>
                                    <input type="time" class="form-control" name="weekday_close" 
                                           value="<?php echo htmlspecialchars($settings['weekday_close_time'] ?? '22:00'); ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Saturday - Sunday</label>
                                <div class="d-flex gap-2">
                                    <input type="time" class="form-control" name="weekend_open" 
                                           value="<?php echo htmlspecialchars($settings['weekend_open_time'] ?? '10:00'); ?>">
                                    <span class="align-self-center">to</span>
                                    <input type="time" class="form-control" name="weekend_close" 
                                           value="<?php echo htmlspecialchars($settings['weekend_close_time'] ?? '23:00'); ?>">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Hours
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>