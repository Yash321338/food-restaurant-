<?php
include 'config/db_connect.php';

$sql = "SELECT COUNT(*) as count FROM orders";
$result = $conn->query($sql);
$count = $result->fetch_assoc()['count'];

echo $count;

$conn->close();
?> 