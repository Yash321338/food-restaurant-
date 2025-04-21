<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
    
    <?php include 'header.php'; ?>
    
    <style>
        .hero-section {
            position: relative;
            height: 70vh;
            overflow: hidden;
            margin-top: -16px;
        }

        .hero-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 2rem;
            color: #2c3e50;
        }

        .food-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s ease;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .food-card:hover {
            transform: translateY(-10px);
        }

        .food-img {
            height: 250px;
            object-fit: cover;
        }

        .drinks-section {
            background: #f8f9fa;
            padding: 4rem 0;
        }
    </style>
</head>
<body>
    <!-- Hero Section with Video -->
    <section class="hero-section">
        <style>
            .hero-img {
                width: 100%;
                height: 600px;
            }
        </style>
        <img src="assets/images/about1.jpg" alt="Restaurant Ambience" class="hero-img">
        <div class="hero-overlay">
            <div class="text-center">
                <h1 class="display-3">Our Story</h1>
                <p class="lead">A Journey of Flavors and Traditions</p>
            </div>
        </div>
    </section>

    <!-- About Us Section -->
    <section class="container my-5">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="section-title">About Our Restaurant</h2>
                <p class="lead">Welcome to our culinary haven, where passion meets tradition.</p>
                <p>Established in 2010, our restaurant has been serving exceptional cuisine that combines traditional recipes with modern innovation. Our team of expert chefs brings together flavors from around the world, creating unique dining experiences for our valued guests.</p>
                <p>We take pride in sourcing the finest ingredients locally, supporting our community while ensuring the highest quality in every dish we serve.</p>
            </div>
            <div class="col-lg-6">
                <img src="assets/images/p5.jpg" 
                     alt="Restaurant Interior" 
                     class="img-fluid rounded shadow">
            </div>
        </div>
    </section>

    <!-- Food Section -->
    <section class="container my-5">
        <h2 class="section-title text-center">Our Signature Dishes</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="card food-card">
                    <img src="assets/images/p6.jpg" 
                         class="card-img-top food-img" 
                         alt="Signature Dish 1">
                    <div class="card-body">
                        <h5 class="card-title">Premium Steaks</h5>
                        <p class="card-text">Hand-selected cuts of premium beef, perfectly aged and grilled to your preference.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card food-card">
                    <img src="assets/images/p7.jpg" 
                         class="card-img-top food-img" 
                         alt="Signature Dish 2">
                    <div class="card-body">
                        <h5 class="card-title">Artisanal Pizzas</h5>
                        <p class="card-text">Wood-fired pizzas made with house-made dough and fresh, seasonal toppings.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card food-card">
                    <img src="assets/images/p8.jpg" 
                         class="card-img-top food-img" 
                         alt="Signature Dish 3">
                    <div class="card-body">
                        <h5 class="card-title">Fresh Seafood</h5>
                        <p class="card-text">Daily-caught seafood prepared with Mediterranean-inspired recipes.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Drinks Section -->
    <section class="drinks-section">
        <div class="container">
            <h2 class="section-title text-center">Beverage Selection</h2>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card food-card">
                        <img src="assets/images/p9.jpg" 
                             class="card-img-top food-img" 
                             alt="Wine Selection">
                        <div class="card-body">
                            <h5 class="card-title">Fine Wines</h5>
                            <p class="card-text">Carefully curated selection of wines from renowned vineyards worldwide. Our sommeliers are here to help you find the perfect pairing for your meal.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card food-card">
                        <img src="assets/images/p10.jpg" 
                             class="card-img-top food-img" 
                             alt="Cocktails">
                        <div class="card-body">
                            <h5 class="card-title">Craft Cocktails</h5>
                            <p class="card-text">Innovative cocktails crafted by our expert mixologists using premium spirits and fresh ingredients. Each drink tells a unique story.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script>
        // Add smooth scrolling animation
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>
