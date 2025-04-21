<?php
session_start();
include 'config/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "Please login to modify your wishlist.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $item_id = $_POST['item_id']; // Ensure this matches the parameter in JavaScript

    // Delete the item from the wishlist
    $query = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND item_name = ?");
    $query->bind_param("ii", $user_id, $item_id);

    if ($query->execute()) {
        echo "Item removed from wishlist.";
    } else {
        echo "Failed to remove item.";
    }
}
?>
