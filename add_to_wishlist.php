<?php
session_start();
include 'config/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "Please login to add items to your wishlist.";
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['item_name'];

// Check if product is already in the wishlist
$checkQuery = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND item_name = ?");
$checkQuery->bind_param("ii", $user_id, $item_name);
$checkQuery->execute();
$checkQuery->store_result();

if ($checkQuery->num_rows == 0) {
    // Add to wishlist
    $query = $conn->prepare("INSERT INTO wishlist (user_id, item_name) VALUES (?, ?)");
    $query->bind_param("ii", $user_id, $item_name);
    $query->execute();
    echo "Added to wishlist!";
} else {
    echo "Item is already in your wishlist!";
}
?>
