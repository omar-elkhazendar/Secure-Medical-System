<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Healthcare Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --primary-color: #2C3E50;
            --secondary-color: #3498DB;
            --light-color: #ECF0F1;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-color);
        }

        .page-header {
            background: var(--primary-color);
            color: white;
            padding: 80px 0 50px;
            text-align: center;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .contact-section {
            padding: 60px 0;
        }

        .contact-card {
            background: white;
            padding: 50px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            text-align: center;
        }

        .contact-icon {
            font-size: 2.5rem;
            color: var(--secondary-color);
            margin-bottom: 20px;
        }

        .contact-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .contact-text {
            color: #666;
            line-height: 1.8;
            margin: 0;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-heartbeat text-primary me-2"></i>
                HealthCare
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="features.php">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="services.php">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="team.php">Team</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary ms-2" href="login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>Contact Us</h1>
            <p>Get in touch with us for any questions or support you may need.</p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="contact-card">
                                <div class="contact-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <h3 class="contact-title">Address</h3>
                                <p class="contact-text">
                                    1 Mortada St<br>
                                    Zizinia, Alexandria<br>
                                     Egypt
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="contact-card">
                                <div class="contact-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <h3 class="contact-title">Phone</h3>
                                <p class="contact-text">
                                    +20 128-187-594-9<br>
                                    Emergency: (20) 911-HELP
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="contact-card">
                                <div class="contact-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <h3 class="contact-title">Email</h3>
                                <p class="contact-text">
                                    omarr.elkhazendar@gmail.com<br>
                                    support@healthcare.com
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="contact-card">
                                <div class="contact-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <h3 class="contact-title">Business Hours</h3>
                                <p class="contact-text">
                                    Monday - Friday: 8:00 AM - 6:00 PM<br>
                                    Saturday: 9:00 AM - 4:00 PM<br>
                                    Sunday: Closed
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="contact-card">
                                <div class="contact-icon">
                                    <i class="fas fa-headset"></i>
                                </div>
                                <h3 class="contact-title">Support</h3>
                                <p class="contact-text">
                                    Our support team is available to assist you.<br>
                                    We respond to all inquiries within 24 hours<br>
                                    during business days.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-heartbeat text-primary me-2"></i>HealthCare</h5>
                    <p>Your trusted healthcare management solution.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="social-links">
                        <a href="#" class="text-light me-3"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
