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
        <title>Restaurant Name</title>
        
        <?php include 'header.php'; ?>
        
        <style>
            /* Video Hero Section Styles */
            .video-hero {
                position: relative;
                height: 80vh;
                overflow: hidden;
                margin-top: -16px; /* To remove gap after navbar */
            }

            .video-container {
                width: 100%;
                height: 100%;
            }

            .video-container video {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                color: white;
                text-align: center;
                padding: 20px;
            }

            .overlay h1 {
                font-size: 3.5rem;
                margin-bottom: 20px;
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            }

            .overlay p {
                font-size: 1.5rem;
                margin-bottom: 30px;
                text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
            }

            .content {
                flex: 1 0 auto;
            }

            /* Responsive adjustments */
            @media (max-width: 768px) {
                .overlay h1 {
                    font-size: 2.5rem;
                }
                
                .overlay p {
                    font-size: 1.2rem;
                }
            }
        </style>
    </head>
    <body>
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Restaurant Hero Slider</title>
    <style>
        .hero-slider {
            position: relative;
            width: 100%;
            height: 600px;
            overflow: hidden;
        }
        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }
        .slide.active {
            opacity: 1;
        }
        .slide img, .slide video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .slide-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: white;
            background: rgba(0,0,0,0.5);
            padding: 20px;
            border-radius: 10px;
        }
        .slider-controls {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
        }
        .slider-dot {
            width: 15px;
            height: 15px;
            background: rgba(255,255,255,0.5);
            border-radius: 50%;
            cursor: pointer;
        }
        .slider-dot.active {
            background: white;
        }
    </style>
</head>
<body>
    <div class="hero-slider">
        <div class="slide active">
        <img src="assets/images/sider1.jpg" alt="Restaurant Interior">
            </video>
            <div class="slide-overlay">
                <h1>Welcome to Our Restaurant</h1>
                <p>Experience the finest dining in town</p>
            </div>
        </div>
        <div class="slide">
            <img src="assets/images/sider2.jpg" alt="Restaurant Interior">
            <div class="slide-overlay">
                <h1>Elegant Dining Space</h1>
                <p>Sophisticated atmosphere for memorable moments</p>
            </div>
        </div>
        <div class="slide">
            <img src="assets/images/sider3.jpg" alt="Restaurant Interior">
            <div class="slide-overlay">
            <h1>Culinary Artistry</h1>
                <p>Crafted with passion by our expert chefs</p>
            
                 </div>
        </div>
        
        <div class="slider-controls">
            <div class="slider-dot active" data-slide="0"></div>
            <div class="slider-dot" data-slide="1"></div>
            <div class="slider-dot" data-slide="2"></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const slides = document.querySelectorAll('.slide');
            const dots = document.querySelectorAll('.slider-dot');
            let currentSlide = 0;

            function changeSlide(index) {
                // Remove active class from current slides and dots
                slides.forEach(slide => slide.classList.remove('active'));
                dots.forEach(dot => dot.classList.remove('active'));

                // Add active class to new slides and dots
                slides[index].classList.add('active');
                dots[index].classList.add('active');

                currentSlide = index;
            }

            // Auto-slide functionality
            function autoSlide() {
                let nextSlide = (currentSlide + 1) % slides.length;
                changeSlide(nextSlide);
            }

            // Auto-slide every 5 seconds
            setInterval(autoSlide, 5000);

            // Click event for dots
            dots.forEach(dot => {
                dot.addEventListener('click', () => {
                    const slideIndex = parseInt(dot.getAttribute('data-slide'));
                    changeSlide(slideIndex);
                });
            });
        });
    </script>


        <div class="container my-5">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="mb-4">About Us</h2>
                    <p class="lead">Welcome to our restaurant, where culinary excellence meets warm hospitality. We take pride in serving exceptional dishes crafted with the finest ingredients and prepared with passion by our expert chefs.</p>
                    <p>Our commitment to quality and innovation has made us a favorite dining destination. Whether you're joining us for a casual meal or a special celebration, we strive to create memorable experiences for all our guests.</p>
                    <a href="about.php" class="btn btn-primary mt-3">Learn More</a>
                </div>
                <div class="col-md-6">
                            <img src="assets/images/about.jpg" 
                        class="img-fluid rounded shadow" 
                        style="width: 100%; height: 400px; object-fit: cover;">
                </div>
            </div>
        </div>

        <div class="container my-5">
            <h2 class="text-center mb-4">Our Menu Categories</h2>
            <div class="row">
                <!-- Breakfast Section -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100" style="cursor: pointer;" onclick="window.location.href='menu.php?category=breakfast'">
                        <img src="assets/images/dish-2.png" 
                            class="card-img-top"
                            alt="Breakfast"
                            style="height: 250px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title">Maggie</h5>
                            
                            <p class="card-text">Start your day with our delicious breakfast options</p>
                            <div class="menu-preview">
                                <p>• Classic English Breakfast - $15.99</p>
                                <p>• Belgian Waffles - $12.99</p>
                                <p>• Eggs Benedict - $14.99</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lunch Section -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100" style="cursor: pointer;" onclick="window.location.href='menu.php?category=lunch'">
                        <img src="assets/images/burger-1.png" 
                            class="card-img-top"
                            alt="Lunch"
                            style="height: 250px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title">Burger</h5>
                            <p class="card-text">Perfect midday meals for your lunch break</p>
                            <div class="menu-preview">
                                <p>• Grilled Burger  - $16.99</p>
                                <p>• cheese Burger - $18.99</p>
                                <p>• peri peri Pasta - $17.99</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dinner Section -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100" style="cursor: pointer;" onclick="window.location.href='menu.php?category=dinner'">
                        <img src="assets/images/pizza-1.png" 
                            class="card-img-top"
                            alt="Dinner"
                            style="height: 250px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title">pizza</h5>
                            <p class="card-text">Exquisite dinner selections for a perfect evening</p>
                            <div class="menu-preview">
                                <p>• Grilled Salmon - $24.99</p>
                                <p>• Prime Rib Steak - $29.99</p>
                                <p>• Seafood Risotto - $26.99</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                .menu-preview {
                    font-size: 0.9rem;
                    color: #666;
                    margin-top: 10px;
                }
                .menu-preview p {
                    margin-bottom: 5px;
                }
                .card {
                    transition: transform 0.2s;
                }
                .card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                }
            </style>
        </div>

        <!-- Additional Menu Items -->
        <div class="container mt-5">
            <div class="row">
                <!-- Drinks -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100" style="cursor: pointer;" onclick="window.location.href='menu.php?category=drinks'">
                        <img src="assets/images/drink-1.png" 
                            class="card-img-top"
                            alt="Drinks"
                            style="height: 250px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title">orange juice</h5>
                            <p class="card-text">Refresh yourself with our signature beverages</p>
                            <div class="menu-preview">
                                <p>• Craft Cocktails - $12.99</p>
                                <p>• Premium Wines - $9.99</p>
                                <p>• Fresh Mocktails - $7.99</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Soups -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100" style="cursor: pointer;" onclick="window.location.href='menu.php?category=soups'">
                        <img src="assets/images/suop1.png" 
                            class="card-img-top"
                            alt="Soups"
                            style="height: 250px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title">Pngtree hot soup bowl</h5>
                            <p class="card-text">Warm up with our homemade soups</p>
                            <div class="menu-preview">
                                <p>• French Onion Soup - $7.99</p>
                                <p>• Lobster Bisque - $9.99</p>
                                <p>• Tomato Basil Soup - $6.99</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desserts -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100" style="cursor: pointer;" onclick="window.location.href='menu.php?category=desserts'">
                            <img src="assets/images/dessert-1.png" 
                            class="card-img-top"
                            alt="Desserts"
                            style="height: 250px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title">strawberry juice Desserts</h5>
                            <p class="card-text">End your meal on a sweet note</p>
                            <div class="menu-preview">
                                <p>• Tiramisu - $8.99</p>
                                <p>• Chocolate Lava Cake - $9.99</p>
                                <p>• Crème Brûlée - $7.99</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Reviews Section
        <div class="container reviews-section my-5">
            <h2 class="text-center mb-4">What Our Customers Say</h2>
            
            <div id="reviewCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <div class="review-card">
                            <div class="review-content">
                                <div class="stars mb-3">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <p class="review-text">"Amazing food and atmosphere! The service was impeccable and every dish was perfectly prepared. Will definitely be coming back!"</p>
                                <p class="reviewer">- Sarah Johnson</p>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="review-card">
                            <div class="review-content">
                                <div class="stars mb-3">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                                <p class="review-text">"The best fine dining experience in the city! Their wine selection is outstanding and pairs perfectly with their dishes."</p>
                                <p class="reviewer">- Michael Chen</p>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="review-card">
                            <div class="review-content">
                                <div class="stars mb-3">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <p class="review-text">"From appetizers to desserts, everything was exceptional. The ambiance makes it perfect for special occasions!"</p>
                                <p class="reviewer">- Emily Rodriguez</p>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#reviewCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#reviewCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div> -->
        
        <li class="center">
  <a href="review.php" class="custom-review-btn">
    <i class="fas fa-star"></i> Add Review
  </a>
</li>

<style>
.custom-review-btn {
  display: inline-block;
  padding: 8px ;
  background-color: #4a6baf;
  color: white;
  border-radius: 4px;
  text-decoration: none;
  font-weight: 500;
  transition: all 0.3s ease;
  border: 1px solid #3a5a9f;
}

.custom-review-btn:hover {
  background-color: #3a5a9f;
  transform: translateY(-1px);
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.custom-review-btn i {
  margin-right: 8px;
}
</style>
        <style>
            .reviews-section {
                padding: 60px 0;
                background-color: #f8f9fa;
                border-radius: 15px;
            }

            .review-card {
                background: white;
                border-radius: 15px;
                padding: 40px;
                margin: 0 auto;
                max-width: 800px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            }

            .review-content {
                text-align: center;
            }

            .stars {
                color: #ffd700;
                font-size: 24px;
            }

            .review-text {
                font-size: 1.2rem;
                line-height: 1.6;
                color: #555;
                font-style: italic;
                margin-bottom: 20px;
            }

            .reviewer {
                font-weight: bold;
                color: #333;
            }

            .carousel-control-prev,
            .carousel-control-next {
                width: 50px;
                height: 50px;
                background-color: rgba(0,0,0,0.3);
                border-radius: 50%;
                top: 50%;
                transform: translateY(-50%);
            }

            .carousel-control-prev {
                left: 20px;
            }

            .carousel-control-next {
                right: 20px;
            }

            .carousel-control-prev-icon,
            .carousel-control-next-icon {
                width: 20px;
                height: 20px;
            }

            @media (max-width: 768px) {
                .review-card {
                    padding: 20px;
                    margin: 0 15px;
                }

                .review-text {
                    font-size: 1rem;
                }

                .stars {
                    font-size: 20px;
                }
            }
        </style>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the carousel with 1-second interval
            var myCarousel = new bootstrap.Carousel(document.getElementById('reviewCarousel'), {
                interval: 1000,  // 1 second
                wrap: true,      // Continuous loop
                pause: 'hover'   // Pause on hover
            });

            // Optional: Pause on hover
            document.getElementById('reviewCarousel').addEventListener('mouseenter', function() {
                myCarousel.pause();
            });

            // Optional: Resume on mouse leave
            document.getElementById('reviewCarousel').addEventListener('mouseleave', function() {
                myCarousel.cycle();
            });
        });
        </script>
        
        <?php include 'footer.php'; ?>
    </body>
    </html>