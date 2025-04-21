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
    die("Connection failed: " . $conn->connect_error);
}

// Get all active offers (where expiry date is in the future)
$current_date = date('Y-m-d');
$sql = "SELECT * FROM offers WHERE expiry_date >= '$current_date' ORDER BY expiry_date ASC";
$result = $conn->query($sql);

// Initialize offer count
$offer_count = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Special Offers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <?php include 'header.php'; ?><br><br>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            padding-top: 20px;
            scroll-behavior: smooth;
        }
        .header {
            background-color: #343a40;
            color: white;
            padding: 15px;
            margin-bottom: 30px;
            border-radius: 5px;
            transform: translateY(-20px);
            opacity: 0;
            transition: transform 0.5s ease, opacity 0.5s ease;
        }
        .header.visible {
            transform: translateY(0);
            opacity: 1;
        }
        .offer-card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: all 0.5s ease;
            margin-bottom: 25px;
            background-color: white;
            transform: translateY(50px);
            opacity: 0;
        }
        .offer-card.visible {
            transform: translateY(0);
            opacity: 1;
        }
        .offer-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        .offer-img {
            height: 200px;
            object-fit: cover;
            width: 100%;
            transition: transform 0.3s ease;
        }
        .offer-card:hover .offer-img {
            transform: scale(1.05);
        }
        .offer-title {
            font-weight: bold;
            color: #343a40;
            margin-bottom: 10px;
        }
        .offer-price {
            font-size: 1.5rem;
            color: #28a745;
            font-weight: bold;
        }
        .offer-expiry {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .badge-expiring {
            background-color: #ffc107;
            color: #343a40;
        }
        .section-title {
            position: relative;
            margin-bottom: 30px;
            padding-bottom: 10px;
        }
        .section-title:after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 3px;
            background: #28a745;
        }
        .no-offers {
            text-align: center;
            padding: 50px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            transform: scale(0.95);
            opacity: 0;
            transition: all 0.5s ease;
        }
        .no-offers.visible {
            transform: scale(1);
            opacity: 1;
        }
        .scroll-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background-color: #343a40;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        .scroll-top.active {
            opacity: 1;
            visibility: visible;
        }
        .scroll-top:hover {
            background-color: #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header text-center">
            <h1><i class="fas fa-tag"></i> Special Offers</h1>
            <p class="lead">Check out our limited-time deals and discounts</p>
        </div>
        
        <?php if ($result->num_rows > 0): ?>
            <div class="row">
                <?php while($row = $result->fetch_assoc()): 
                    // Check if offer is expiring soon (within 3 days)
                    $expiring_soon = (strtotime($row['expiry_date']) - strtotime($current_date)) <= (3 * 24 * 60 * 60);
                ?>
                    <div class="col-md-4">
                        <div class="offer-card">
                            <?php if (!empty($row["image"]) && file_exists($row["image"])): ?>
                                <img src="<?php echo $row["image"]; ?>" class="offer-img" alt="<?php echo htmlspecialchars($row["title"]); ?>">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/400x200?text=No+Image" class="offer-img" alt="No Image Available">
                            <?php endif; ?>
                            
                            <div class="p-3">
                                <h3 class="offer-title"><?php echo htmlspecialchars($row["title"]); ?></h3>
                                <p><?php echo htmlspecialchars($row["description"]); ?></p>
                                
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="offer-price">$<?php echo number_format($row["price"], 2); ?></span>
                                    <span class="offer-expiry">
                                        <?php if ($expiring_soon): ?>
                                            <span class="badge badge-expiring p-2">Expires Soon!</span>
                                        <?php endif; ?>
                                        Valid until: <?php echo date('M j, Y', strtotime($row["expiry_date"])); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-offers">
                <i class="fas fa-tag fa-4x mb-3" style="color: #6c757d;"></i>
                <h3>No Current Offers Available</h3>
                <p class="text-muted">Check back later for special deals!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Scroll to Top Button -->
    <div class="scroll-top">
        <i class="fas fa-arrow-up"></i>
    </div>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Scroll animation for elements
        document.addEventListener('DOMContentLoaded', function() {
            // Animate header on load
            const header = document.querySelector('.header');
            setTimeout(() => {
                header.classList.add('visible');
            }, 100);

            // Animate cards when they come into view
            const animateOnScroll = () => {
                const offers = document.querySelectorAll('.offer-card');
                const noOffers = document.querySelector('.no-offers');
                
                offers.forEach((offer, index) => {
                    const offerPosition = offer.getBoundingClientRect().top;
                    const screenPosition = window.innerHeight / 1.2;
                    
                    if (offerPosition < screenPosition) {
                        setTimeout(() => {
                            offer.classList.add('visible');
                        }, index * 100); // Staggered delay
                    }
                });
                
                if (noOffers) {
                    const noOffersPosition = noOffers.getBoundingClientRect().top;
                    const screenPosition = window.innerHeight / 1.2;
                    
                    if (noOffersPosition < screenPosition) {
                        noOffers.classList.add('visible');
                    }
                }
            };
            
            // Run once on load and then on scroll
            animateOnScroll();
            window.addEventListener('scroll', animateOnScroll);
            
            // Scroll to top button
            const scrollTopBtn = document.querySelector('.scroll-top');
            
            window.addEventListener('scroll', () => {
                if (window.pageYOffset > 300) {
                    scrollTopBtn.classList.add('active');
                } else {
                    scrollTopBtn.classList.remove('active');
                }
            });
            
            scrollTopBtn.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
            
            // Add hover effect to cards
            const cards = document.querySelectorAll('.offer-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.transform = 'translateY(-5px) scale(1.02)';
                    card.style.boxShadow = '0 10px 20px rgba(0,0,0,0.15)';
                });
                
                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'translateY(0) scale(1)';
                    card.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
                });
            });
        });
    </script>
</body>
</html>

<?php
// Close connection
$conn->close();
?>