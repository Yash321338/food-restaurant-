<?php
session_start();
include 'config/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "Please login to view your wishlist.";
    exit();
}

$user_id = $_SESSION['user_id'];

// Updated query: join on item_id instead of item_name
$query = $conn->prepare("SELECT p.id, p.item_name, p.price
                         FROM wishlist w
                         JOIN menu_items p ON w.item_id = p.id
                         WHERE w.user_id = ?");
                         
if (!$query) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}

$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php include 'header.php'; ?>
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center mb-4">My Wishlist</h2>

    <div class="row">
        <?php while ($row = $result->fetch_assoc()) { ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($row['item_name']); ?></h5>
                        <p class="card-text">Price: $<?php echo number_format($row['price'], 2); ?></p>
                        <button class="btn btn-primary" onclick="addToCart(<?php echo $row['id']; ?>)">Add to Cart</button>
                        <button class="btn btn-danger" onclick="removeFromWishlist(<?php echo $row['id']; ?>)">Remove</button>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<script>
function addToCart(itemId) {
    fetch("add_to_cart.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "item_id=" + itemId
    })
    .then(response => response.text())
    .then(data => {
        alert(data);
        location.reload();
    })
    .catch(error => console.error("Error:", error));
}

function removeFromWishlist(itemId) {
    fetch("remove_from_wishlist.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "item_id=" + itemId
    })
    .then(response => response.text())
    .then(data => {
        alert(data);
        location.reload();
    })
    .catch(error => console.error("Error:", error));
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
