<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/db_connect.php';
// Include header
include 'header.php';

// Initialize variables
$name = $email = $subject = $message = "";
$nameErr = $emailErr = $subjectErr = $messageErr = "";
$success = false;

// Form validation when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate name
    if (empty($_POST["name"])) {
        $nameErr = "Name is required";
    } else {
        $name = test_input($_POST["name"]);
        // More robust name validation
        if (!preg_match("/^[a-zA-Z\s'-]+$/u", $name)) {
            $nameErr = "Invalid name format. Use letters, spaces, hyphens, and apostrophes only.";
        } elseif (strlen($name) < 2 || strlen($name) > 50) {
            $nameErr = "Name must be between 2 and 50 characters";
        }
    }
    
    // Validate email
    if (empty($_POST["email"])) {
        $emailErr = "Email is required";
    } else {
        $email = test_input($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format";
        } elseif (strlen($email) > 100) {
            $emailErr = "Email address is too long";
        }
    }
    
    // Validate subject
    if (empty($_POST["subject"])) {
        $subjectErr = "Subject is required";
    } else {
        $subject = test_input($_POST["subject"]);
        if (strlen($subject) < 3 || strlen($subject) > 100) {
            $subjectErr = "Subject must be between 3 and 100 characters";
        }
    }
    
    // Validate message
    if (empty($_POST["message"])) {
        $messageErr = "Message is required";
    } else {
        $message = test_input($_POST["message"]);
        if (strlen($message) < 10 || strlen($message) > 1000) {
            $messageErr = "Message must be between 10 and 1000 characters";
        }
    }

    // If no errors, insert into database and set success flag
    if (empty($nameErr) && empty($emailErr) && empty($subjectErr) && empty($messageErr)) {
        // Check for duplicate submission to prevent spam
        $duplicate_check_sql = "SELECT COUNT(*) as count FROM contact_messages 
                                WHERE name = ? AND email = ? AND subject = ? AND message = ? 
                                AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
        $duplicate_stmt = $conn->prepare($duplicate_check_sql);
        $duplicate_stmt->bind_param("ssss", $name, $email, $subject, $message);
        $duplicate_stmt->execute();
        $duplicate_result = $duplicate_stmt->get_result();
        $duplicate_row = $duplicate_result->fetch_assoc();
        $duplicate_stmt->close();

        if ($duplicate_row['count'] > 0) {
            // Prevent duplicate submissions
            $dbError = "You have recently submitted this message. Please wait before sending again.";
        } else {
            // Prepare and execute SQL insert statement with IP tracking
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $sql = "INSERT INTO contact_messages (name, email, subject, message, ip_address, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $name, $email, $subject, $message, $ip_address);
            
            try {
                if ($stmt->execute()) {
                    $success = true;
                    $_SESSION['contact_success'] = true;
                    
                    // Optional: Send email notification
                    $to = "admin@restaurant.com";
                    $email_subject = "New Contact Form Submission";
                    $email_body = "Name: $name\n";
                    $email_body .= "Email: $email\n";
                    $email_body .= "Subject: $subject\n\n";
                    $email_body .= "Message:\n$message\n";
                    $headers = "From: contact@restaurant.com";
                    
                    // Uncomment the line below if you want to send an email
                    // mail($to, $email_subject, $email_body, $headers);
                    
                    // Clear form fields after successful submission
                    $name = $email = $subject = $message = "";
                } else {
                    // If there was an error with the database
                    $dbError = "Error: " . $stmt->error;
                }
            } catch (Exception $e) {
                $dbError = "Database error: " . $e->getMessage();
            }
            
            $stmt->close();
        }
    }
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}
?>

<!-- Rest of the existing HTML remains the same -->

<div class="contact-container">
    <!-- Info Section -->
    <div class="container py-5">
        <div class="row mb-5">
            <div class="col-md-4 text-center">
                <div class="contact-info-box">
                    <i class="fas fa-map-marker-alt fa-2x mb-3"></i>
                    <h4>Our Location</h4>
                    <p>123 Restaurant Street<br>New York, NY 10001</p>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="contact-info-box">
                    <i class="fas fa-phone fa-2x mb-3"></i>
                    <h4>Phone Number</h4>
                    <p>+1 (555) 123-4567<br>+1 (555) 987-6543</p>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="contact-info-box">
                    <i class="fas fa-envelope fa-2x mb-3"></i>
                    <h4>Email Address</h4>
                    <p>info@restaurant.com<br>support@restaurant.com</p>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="contact-form-box">
                    <h2 class="text-center mb-4">Send Us a Message</h2>

                    <?php if (isset($_SESSION['contact_success']) && $_SESSION['contact_success']): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            Your message has been sent successfully!
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['contact_success']); ?>
                    <?php endif; ?>
                    
                    <?php if (isset($dbError)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $dbError; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control <?php echo (!empty($nameErr)) ? 'is-invalid' : ''; ?>" 
                                           id="name" name="name" value="<?php echo $name; ?>" placeholder="Your name">
                                    <div class="invalid-feedback"><?php echo $nameErr; ?></div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control <?php echo (!empty($emailErr)) ? 'is-invalid' : ''; ?>" 
                                           id="email" name="email" value="<?php echo $email; ?>" placeholder="Your email">
                                    <div class="invalid-feedback"><?php echo $emailErr; ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-heading"></i></span>
                                <input type="text" class="form-control <?php echo (!empty($subjectErr)) ? 'is-invalid' : ''; ?>" 
                                       id="subject" name="subject" value="<?php echo $subject; ?>" placeholder="Message subject">
                                <div class="invalid-feedback"><?php echo $subjectErr; ?></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="message" class="form-label">Message</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-comment"></i></span>
                                <textarea class="form-control <?php echo (!empty($messageErr)) ? 'is-invalid' : ''; ?>" 
                                          id="message" name="message" rows="5" placeholder="Your message"><?php echo $message; ?></textarea>
                                <div class="invalid-feedback"><?php echo $messageErr; ?></div>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.contact-container {
    background-color: #f8f9fa;
    padding: 40px 0;
}

.contact-info-box {
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    height: 100%;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.contact-info-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.contact-info-box i {
    color: #0d6efd;
}

.contact-info-box h4 {
    margin: 15px 0;
    font-weight: 600;
    color: #343a40;
}

.contact-form-box {
    background: #fff;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.form-label {
    font-weight: 500;
    color: #495057;
}

.input-group-text {
    background-color: #0d6efd;
    color: white;
    border: none;
}

.form-control {
    border: 1px solid #ced4da;
    padding: 12px;
}

.form-control:focus {
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    border-color: #86b7fe;
}

.btn-primary {
    background-color: #0d6efd;
    border: none;
    padding: 12px 30px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background-color: #0b5ed7;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(13, 110, 253, 0.4);
}

.btn-lg {
    padding: 12px 30px;
    border-radius: 30px;
}

/* Animation for success message */
.alert-success {
    animation: fadeInDown 0.5s ease;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<?php include 'footer.php'; ?>