<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 8px 15px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .back-button:hover {
            background-color: #5a6268;
        }
        .back-button i {
            margin-right: 5px;
        }
        .review {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            align-items: center;
        }
        .stars {
            color: gold;
            font-size: 1.2em;
        }
        form {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            margin-top: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-family: inherit;
        }
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #45a049;
        }
        h1, h2 {
            color: #333;
        }
        h2 {
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
            margin-top: 30px;
        }
        .success-message {
            background-color: #dff0d8;
            color: #3c763d;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            border-left: 4px solid #3c763d;
        }
        .error-message {
            background-color: #f2dede;
            color: #a94442;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            border-left: 4px solid #a94442;
        }
        .reviews-container {
            margin-bottom: 30px;
        }
        .review-date {
            color: #6c757d;
            font-size: 0.9em;
        }
        @media (max-width: 600px) {
            body {
                padding: 15px;
            }
            .review-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .stars {
                margin-top: 5px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <a href="index.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Back
    </a>
    
    <h1>Customer Reviews</h1>
    
    <?php
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "restaurnt (2)";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("<div class='error-message'>Connection failed: " . $conn->connect_error . "</div>");
    }

    // Process form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = htmlspecialchars(trim($_POST["name"]));
        $email = htmlspecialchars(trim($_POST["email"]));
        $rating = (int)$_POST["rating"];
        $review_text = htmlspecialchars(trim($_POST["review_text"]));
        $date_posted = date("Y-m-d H:i:s");

        // Validate inputs
        $errors = [];
        if (empty($name)) {
            $errors[] = "Name is required";
        }
        if (empty($email)) {
            $errors[] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address";
        }
        if ($rating < 1 || $rating > 5) {
            $errors[] = "Please select a valid rating";
        }
        if (empty($review_text)) {
            $errors[] = "Review text is required";
        } elseif (strlen($review_text) < 10) {
            $errors[] = "Review should be at least 10 characters long";
        }

        if (empty($errors)) {
            // Insert into database
            $stmt = $conn->prepare("INSERT INTO reviews (name, email, rating, review_text, date_posted) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiss", $name, $email, $rating, $review_text, $date_posted);

            if ($stmt->execute()) {
                echo "<div class='success-message'>Thank you for your review! It has been submitted successfully.</div>";
                // Clear form fields after successful submission
                $_POST = array();
            } else {
                echo "<div class='error-message'>Error submitting your review. Please try again later.</div>";
            }
            $stmt->close();
        } else {
            echo "<div class='error-message'>";
            echo "<strong>Please fix the following issues:</strong>";
            echo "<ul>";
            foreach ($errors as $error) {
                echo "<li>" . $error . "</li>";
            }
            echo "</ul>";
            echo "</div>";
        }
    }
    ?>

    <!-- Display existing reviews -->
    <div class="reviews-container">
        <h2>Recent Reviews</h2>
        
        <?php
        // Query to get reviews
        $sql = "SELECT name, rating, review_text, date_posted FROM reviews ORDER BY date_posted DESC LIMIT 10";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<div class='review'>";
                echo "<div class='review-header'>";
                echo "<h3>" . $row["name"] . "</h3>";
                echo "<div class='stars' title='" . $row["rating"] . " out of 5 stars'>";
                // Display solid stars for the rating
                for ($i = 0; $i < 5; $i++) {
                    if ($i < $row["rating"]) {
                        echo "<i class='fas fa-star'></i>";
                    } else {
                        echo "<i class='far fa-star'></i>";
                    }
                }
                echo "</div>";
                echo "</div>";
                echo "<p>" . nl2br($row["review_text"]) . "</p>";
                echo "<p class='review-date'>Posted on: " . date("F j, Y \a\\t g:i a", strtotime($row["date_posted"])) . "</p>";
                echo "</div>";
            }
        } else {
            echo "<p>No reviews yet. Be the first to leave a review!</p>";
        }
        ?>
    </div>

<!-- Add review form -->
<div class="add-review">
    <h2>Add Your Review</h2>
    
    <?php
    // Initialize error array
    $errors = [];
    
    // Form validation and processing
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validate name
        if (empty($_POST['name'])) {
            $errors['name'] = "Name is required";
        } else {
            $name = htmlspecialchars(trim($_POST['name']));
            if (!preg_match("/^[a-zA-Z ]*$/", $name)) {
                $errors['name'] = "Only letters and white space allowed";
            }
        }
        
        // Validate email
        if (empty($_POST['email'])) {
            $errors['email'] = "Email is required";
        } else {
            $email = htmlspecialchars(trim($_POST['email']));
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = "Invalid email format";
            }
        }
        
        // Validate rating
        if (empty($_POST['rating'])) {
            $errors['rating'] = "Please select a rating";
        } else {
            $rating = intval($_POST['rating']);
            if ($rating < 1 || $rating > 5) {
                $errors['rating'] = "Invalid rating value";
            }
        }
        
        // Validate review text
        if (empty($_POST['review_text'])) {
            $errors['review_text'] = "Review text is required";
        } else {
            $review_text = htmlspecialchars(trim($_POST['review_text']));
            if (strlen($review_text) < 10) {
                $errors['review_text'] = "Review should be at least 10 characters";
            }
        }
        
        // If no errors, process the form
        if (empty($errors)) {
            // Insert into database (your existing code here)
            // $sql = "INSERT INTO reviews ...";
            
            // For demonstration, we'll just show a success message
            echo '<div class="alert alert-success">Thank you for your review!</div>';
            
            // Clear POST data so form appears empty
            $_POST = array();
        }
    }
    ?>
    
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div>
            <label for="name">Your Name:</label>
            <input type="text" id="name" name="name" required
                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                   class="<?php echo isset($errors['name']) ? 'error-field' : ''; ?>">
            <?php if (isset($errors['name'])): ?>
                <span class="error-text"><?php echo $errors['name']; ?></span>
            <?php endif; ?>
        </div>
        
        <div>
            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" required
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                   class="<?php echo isset($errors['email']) ? 'error-field' : ''; ?>">
            <?php if (isset($errors['email'])): ?>
                <span class="error-text"><?php echo $errors['email']; ?></span>
            <?php endif; ?>
        </div>
        
        <div>
            <label for="rating">Rating:</label>
            <select id="rating" name="rating" required
                    class="<?php echo isset($errors['rating']) ? 'error-field' : ''; ?>">
                <option value="">Select Rating</option>
                <option value="5" <?php echo (isset($_POST['rating']) && $_POST['rating'] == 5) ? 'selected' : ''; ?>>5 Stars - Excellent</option>
                <option value="4" <?php echo (isset($_POST['rating']) && $_POST['rating'] == 4) ? 'selected' : ''; ?>>4 Stars - Very Good</option>
                <option value="3" <?php echo (isset($_POST['rating']) && $_POST['rating'] == 3) ? 'selected' : ''; ?>>3 Stars - Good</option>
                <option value="2" <?php echo (isset($_POST['rating']) && $_POST['rating'] == 2) ? 'selected' : ''; ?>>2 Stars - Fair</option>
                <option value="1" <?php echo (isset($_POST['rating']) && $_POST['rating'] == 1) ? 'selected' : ''; ?>>1 Star - Poor</option>
            </select>
            <?php if (isset($errors['rating'])): ?>
                <span class="error-text"><?php echo $errors['rating']; ?></span>
            <?php endif; ?>
        </div>
        
        <div>
            <label for="review_text">Your Review:</label>
            <textarea id="review_text" name="review_text" rows="5" required
                      class="<?php echo isset($errors['review_text']) ? 'error-field' : ''; ?>"><?php 
                echo isset($_POST['review_text']) ? htmlspecialchars($_POST['review_text']) : ''; 
            ?></textarea>
            <?php if (isset($errors['review_text'])): ?>
                <span class="error-text"><?php echo $errors['review_text']; ?></span>
            <?php endif; ?>
        </div>
        
        <button type="submit">
            <i class="fas fa-paper-plane"></i> Submit Review
        </button>
    </form>
</div>

<style>
    .error-field {
        border: 1px solid #ff0000;
        background-color: #ffeeee;
    }
    
    .error-text {
        color: #ff0000;
        font-size: 0.9em;
        margin-top: 5px;
        display: block;
    }
    
    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
        padding: 10px;
        margin-bottom: 20px;
        border-radius: 4px;
    }
</style>

<?php
// Close the database connection
$conn->close();
?>
</body>
</html>