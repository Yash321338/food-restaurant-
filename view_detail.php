<?php
session_start();

// Ensure the user is logged in and is an admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Order Details - Admin Panel</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
  <!-- Optionally include your sidebar here -->
  <!-- <?php include 'admin_sidebar.php'; ?> -->

  <div class="container mt-4">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8">
        <h1 class="text-center mb-4">Order Details</h1>
        <div class="card">
          <div class="card-header">
            <h5>Order #1001</h5>
          </div>
          <div class="card-body">
            <p><strong>Customer:</strong> John Doe</p>
            <p><strong>Items:</strong> 2x Burger, 1x Fries</p>
            <p><strong>Total:</strong> $25.99</p>
            <p>
              <strong>Status:</strong> 
              <span class="badge bg-warning">In Progress</span>
            </p>
            <p><strong>Order Time:</strong> 2023-10-20 14:30</p>
            <hr>
            <p><strong>Additional Details:</strong></p>
            <p>This order contains two burgers with extra cheese and one large fries. The customer requested no pickles.</p>
            <a href="manage_orders.php" class="btn btn-secondary">
              <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
