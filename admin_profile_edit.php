<?php
session_start();

// Simple session-based admin check (for demonstration only)
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Default admin data
$admin = [
    'username' => 'Admin',
    'email' => 'admin@example.com',
    'profile_image' => 'assets/images/default-admin.png'
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and process form data
    $errors = [];

    // Username validation
    $new_username = trim($_POST['username'] ?? '');
    if (empty($new_username)) {
        $errors[] = "Username cannot be empty";
    } elseif (strlen($new_username) < 3) {
        $errors[] = "Username must be at least 3 characters long";
    }

    // Email validation
    $new_email = trim($_POST['email'] ?? '');
    if (empty($new_email) || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address";
    }

    // Profile image upload
    $profile_image = $admin['profile_image'];
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "uploads/";
        // Create uploads directory if it doesn't exist
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $filename = uniqid() . "_" . basename($_FILES['profile_image']['name']);
        $target_file = $target_dir . $filename;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate image
        $check = getimagesize($_FILES['profile_image']['tmp_name']);
        if ($check === false) {
            $errors[] = "File is not an image";
        }

        // Check file size (limit to 5MB)
        if ($_FILES['profile_image']['size'] > 5000000) {
            $errors[] = "Sorry, your file is too large";
        }

        // Allow certain file formats
        $allowed_formats = ["jpg", "jpeg", "png", "gif"];
        if (!in_array($imageFileType, $allowed_formats)) {
            $errors[] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed";
        }

        // If no errors, move uploaded file
        if (empty($errors)) {
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                $profile_image = $target_file;
            } else {
                $errors[] = "Sorry, there was an error uploading your file";
            }
        }
    }

    // If no errors, update admin data
    if (empty($errors)) {
        // In a real application, you would update this in a database
        $admin['username'] = $new_username;
        $admin['email'] = $new_email;
        $admin['profile_image'] = $profile_image;

        // Redirect with success message
        $_SESSION['success_message'] = "Profile updated successfully!";
        header("Location: admin_profile.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Admin Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .edit-profile-container {
            max-width: 500px;
            margin: 3rem auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 2.5rem;
        }

        .profile-image-preview {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #e0e4e8;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .profile-image-preview:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .form-control {
            border-radius: 25px;
            padding: 0.75rem 1.5rem;
        }

        .btn-upload {
            background: #1a73e8;
            color: white;
            border-radius: 25px;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }

        .btn-upload:hover {
            background: #1557b0;
            transform: translateY(-2px);
        }

        .error-message {
            color: #dc3545;
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="edit-profile-container">
            <h2 class="text-center mb-4">Edit Admin Profile</h2>

            <?php 
            // Display errors
            if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p class="mb-1"><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="text-center mb-4">
                    <img id="profile-preview" 
                         src="<?php echo $admin['profile_image']; ?>" 
                         class="profile-image-preview" 
                         alt="Profile Preview"
                         onerror="this.src='assets/images/'">
                    
                    <div class="mt-3">
                        <label class="btn btn-upload">
                            <input type="file" name="profile_image" 
                                   id="profile-image-upload" 
                                   accept="image/*" 
                                   style="display:none;">
                            <i class="fas fa-upload me-2"></i>Change Profile Picture
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" 
                           name="username" 
                           class="form-control" 
                           value="<?php echo htmlspecialchars($admin['username']); ?>" 
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" 
                           name="email" 
                           class="form-control" 
                           value="<?php echo htmlspecialchars($admin['email']); ?>" 
                           required>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary px-4 py-2">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                    <a href="admin_profile.php" class="btn btn-secondary ms-2 px-4 py-2">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Profile image preview
        document.getElementById('profile-image-upload').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                document.getElementById('profile-preview').src = e.target.result;
            }

            reader.readAsDataURL(file);
        });
    </script>

    </body>
</html>