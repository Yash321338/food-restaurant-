<?php
session_start();

include "config/db_connect.php";

if (!$conn) {
    error_log("Failed to connect to MySQL: " . mysqli_connect_error());
    die("Connection failed: " . htmlspecialchars(mysqli_connect_error()));
}

// Set the character set to ensure proper encoding
if (!$conn->set_charset("utf8mb4")) {
    error_log("Error setting charset: " . $conn->error);
    die("Error setting charset: " . htmlspecialchars($conn->error));
}

function handleDatabaseError($conn, $error_message) {
    error_log("Database Error: " . $error_message . " - " . $conn->error);
    return "<div class='alert alert-danger'>
        <strong>Database Error:</strong> " . htmlspecialchars($error_message) . 
        ". Please try again or contact support if the problem persists.</div>";
}

function handlePreparedStatement($conn, $sql) {
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        die("Error preparing statement: " . htmlspecialchars($conn->error));
    }
    return $stmt;
}

if (!$conn) {
    die(handleDatabaseError($conn, "Connection failed"));
}

if (!$conn->select_db('restaurnt (2)')) {
    die(handleDatabaseError($conn, "Could not select database"));
}

try {
    if (!$conn->query($createTableSQL)) {
        die(handleDatabaseError($conn, "Error creating offers table"));
    }
} catch (Exception $e) {
    die(handleDatabaseError($conn, "Exception while creating table: " . $e->getMessage()));
}

function createOffer($conn, $title, $description, $price, $image, $image_type, $valid_until, $terms = null, $status = 'active', $featured = 0) {
    try {
        if ($terms !== null) {
            $sql = "INSERT INTO offers (title, description, price, image, image_type, valid_until, terms, status, featured) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = handlePreparedStatement($conn, $sql);
            $stmt->bind_param("ssdsssssi", $title, $description, $price, $image, $image_type, $valid_until, $terms, $status, $featured);
        } else {
            $sql = "INSERT INTO offers (title, description, price, image, image_type, valid_until, status, featured) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = handlePreparedStatement($conn, $sql);
            $stmt->bind_param("ssdssssi", $title, $description, $price, $image, $image_type, $valid_until, $status, $featured);
        }
        
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return false;
        }
        
        $insert_id = $conn->insert_id;
        $stmt->close();
        return $insert_id;
        
    } catch (Exception $e) {
        error_log("Error in createOffer: " . $e->getMessage());
        return false;
    }
}

function updateOffer($conn, $id, $title, $description, $price, $valid_until, $terms = null, $status = 'active', $featured = 0) {
    try {
        if ($terms !== null) {
            $sql = "UPDATE offers SET title = ?, description = ?, price = ?, valid_until = ?, terms = ?, status = ?, featured = ? 
                   WHERE id = ?";
            $stmt = handlePreparedStatement($conn, $sql);
            $stmt->bind_param("ssdsssii", $title, $description, $price, $valid_until, $terms, $status, $featured, $id);
        } else {
            $sql = "UPDATE offers SET title = ?, description = ?, price = ?, valid_until = ?, status = ?, featured = ? 
                   WHERE id = ?";
            $stmt = handlePreparedStatement($conn, $sql);
            $stmt->bind_param("ssdssii", $title, $description, $price, $valid_until, $status, $featured, $id);
        }
        
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return false;
        }
        
        $success = $stmt->affected_rows > 0;
        $stmt->close();
        return $success;
        
    } catch (Exception $e) {
        error_log("Error in updateOffer: " . $e->getMessage());
        return false;
    }
}

function getOfferById($conn, $id) {
    try {
        $sql = "SELECT * FROM offers WHERE id = ?";
        $stmt = handlePreparedStatement($conn, $sql);
        $stmt->bind_param("i", $id);
        
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return null;
        }
        
        $result = $stmt->get_result();
        $offer = $result->fetch_assoc();
        $stmt->close();
        return $offer;
        
    } catch (Exception $e) {
        error_log("Error in getOfferById: " . $e->getMessage());
        return null;
    }
}

function getOfferImage($conn, $id) {
    try {
        $sql = "SELECT image, image_type FROM offers WHERE id = ?";
        $stmt = handlePreparedStatement($conn, $sql);
        $stmt->bind_param("i", $id);
        
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return null;
        }
        
        $result = $stmt->get_result();
        $image = $result->fetch_assoc();
        $stmt->close();
        return $image;
        
    } catch (Exception $e) {
        error_log("Error in getOfferImage: " . $e->getMessage());
        return null;
    }
}

// In your form processing section
if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST['create_offer']) || isset($_POST['update_offer']))) {
    try {
        // Validate and sanitize inputs
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        $valid_until = filter_input(INPUT_POST, 'valid_until', FILTER_SANITIZE_STRING);
        
        if ($title === false || $description === false || $price === false || $valid_until === false) {
            throw new Exception("Invalid input data");
        }
        
        // Process the form
        if (isset($_POST['create_offer'])) {
            $result = createOffer($conn, $title, $description, $price, $image, $image_type, $valid_until);
            if ($result === false) {
                throw new Exception("Failed to create offer");
            }
        } else {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if ($id === false) {
                throw new Exception("Invalid offer ID");
            }
            $result = updateOffer($conn, $id, $title, $description, $price, $valid_until);
            if ($result === false) {
                throw new Exception("Failed to update offer");
            }
        }
        
    } catch (Exception $e) {
        error_log("Error processing form: " . $e->getMessage());
        $error_message = "An error occurred while processing your request. Please try again.";
        echo "<div class='alert alert-danger'>" . htmlspecialchars($error_message) . "</div>";
    }
} 