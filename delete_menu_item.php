<?php
session_start();
include 'config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['menu_id'])) {
        $menu_id = mysqli_real_escape_string($conn, $_POST['menu_id']);
        
        // Delete the menu item
        $sql = "DELETE FROM menu_items WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $menu_id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Menu item deleted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error deleting menu item']);
        }
        
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Menu ID not provided']);
    }
} else {
    header("Location: manage_menu.php");
    exit();
}
?> 