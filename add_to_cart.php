<?php
session_start();
include 'config/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "Please login to add items to cart.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $item_id = $_POST['item_id'];

    // Check if item already exists in the cart
    $check_query = $conn->prepare("SELECT * FROM carts WHERE user_id = ? AND item_id = ?");
    $check_query->bind_param("ii", $user_id, $item_id);
    $check_query->execute();
    $result = $check_query->get_result();

    if ($result->num_rows > 0) {
        echo "Item is already in the cart.";
    } else {
        // Add item to cart
        $insert_query = $conn->prepare("INSERT INTO carts (user_id, item_id, quantity) VALUES (?, ?, 1)");
        $insert_query->bind_param("ii", $user_id, $item_id);

        if ($insert_query->execute()) {
            // Remove item from wishlist after adding to cart
            $delete_query = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND item_name = ?");
            $delete_query->bind_param("ii", $user_id, $item_id);
            $delete_query->execute();

            echo "Product moved to cart!";
        } else {
            echo "Failed to add product to cart.";
        }
    }
}
?>
