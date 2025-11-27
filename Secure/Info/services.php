<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services - Healthcare Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --primary-color: #2C3E50;
            --secondary-color: #3498DB;
            --accent-color: #E74C3C;
            --light-color: #ECF0F1;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-color);
        }

        .page-header {
            background: linear-gradient(135deg, var(--accent-color) 0%, #C0392B 100%);
            color: white;
            padding: 100px 0 60px;
            text-align: center;
        }

        .page-header h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .page-header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .services-section {
            padding: 80px 0;
        }

        .service-card {
            background: white;
            padding: 50px 35px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            transition: all 0.4s ease;
            border: 1px solid rgba(0,0,0,0.05);
            height: 100%;
            text-align: center;
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .service-icon {
            font-size: 4rem;
            margin-bottom: 30px;
            color: var(--accent-color);
            transition: transform 0.4s ease;
        }

        .service-card:hover .service-icon {
            transform: scale(1.15) rotate(-5deg);
        }

        .service-title {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--primary-color);
        }

        .service-text {
            color: #666;
            line-height: 1.8;
            font-size: 1.05rem;
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
            color: var(--primary-color);
            font-size: 2.5rem;
            font-weight: 700;
        }

        .service-list {
            list-style: none;
            padding: 0;
            margin-top: 20px;
        }

        .service-list li {
            padding: 10px 0;
            color: #555;
            border-bottom: 1px solid #eee;
        }

        .service-list li:last-child {
            border-bottom: none;
        }

        .service-list li i {
            color: var(--secondary-color);
            margin-right: 10px;
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
            <h1 data-aos="fade-up">Our Services</h1>
            <p data-aos="fade-up" data-aos-delay="100">Comprehensive healthcare services designed to meet all your medical needs with excellence and care.</p>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services-section">
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-stethoscope"></i>
                        </div>
                        <h3 class="service-title">Primary Care</h3>
                        <p class="service-text">Comprehensive primary healthcare services including routine check-ups, preventive care, and treatment of common illnesses. Our primary care physicians provide personalized attention to maintain your overall health and wellness.</p>
                        <ul class="service-list">
                            <li><i class="fas fa-check-circle"></i> Annual physical examinations</li>
                            <li><i class="fas fa-check-circle"></i> Preventive screenings</li>
                            <li><i class="fas fa-check-circle"></i> Chronic disease management</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <h3 class="service-title">Specialist Consultations</h3>
                        <p class="service-text">Access to a wide range of medical specialists including cardiologists, dermatologists, neurologists, and more. Get expert opinions and specialized treatment plans tailored to your specific health conditions.</p>
                        <ul class="service-list">
                            <li><i class="fas fa-check-circle"></i> Cardiology services</li>
                            <li><i class="fas fa-check-circle"></i> Dermatology consultations</li>
                            <li><i class="fas fa-check-circle"></i> Neurology assessments</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-vial"></i>
                        </div>
                        <h3 class="service-title">Laboratory Services</h3>
                        <p class="service-text">State-of-the-art laboratory testing and diagnostic services. Get accurate results quickly with our advanced testing facilities and experienced laboratory technicians.</p>
                        <ul class="service-list">
                            <li><i class="fas fa-check-circle"></i> Blood tests and analysis</li>
                            <li><i class="fas fa-check-circle"></i> Urine and stool tests</li>
                            <li><i class="fas fa-check-circle"></i> Pathology services</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-x-ray"></i>
                        </div>
                        <h3 class="service-title">Diagnostic Imaging</h3>
                        <p class="service-text">Advanced imaging services including X-rays, MRIs, CT scans, and ultrasounds. Our modern imaging equipment provides clear, accurate results to aid in diagnosis and treatment planning.</p>
                        <ul class="service-list">
                            <li><i class="fas fa-check-circle"></i> X-ray imaging</li>
                            <li><i class="fas fa-check-circle"></i> MRI and CT scans</li>
                            <li><i class="fas fa-check-circle"></i> Ultrasound services</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-pills"></i>
                        </div>
                        <h3 class="service-title">Pharmacy Services</h3>
                        <p class="service-text">Convenient pharmacy services with prescription management and medication counseling. Our pharmacists ensure you receive the right medications with proper instructions for safe and effective use.</p>
                        <ul class="service-list">
                            <li><i class="fas fa-check-circle"></i> Prescription fulfillment</li>
                            <li><i class="fas fa-check-circle"></i> Medication counseling</li>
                            <li><i class="fas fa-check-circle"></i> Refill reminders</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-user-nurse"></i>
                        </div>
                        <h3 class="service-title">Telemedicine</h3>
                        <p class="service-text">Virtual consultations with healthcare providers from the comfort of your home. Access medical advice, follow-up appointments, and consultations through secure video conferencing technology.</p>
                        <ul class="service-list">
                            <li><i class="fas fa-check-circle"></i> Video consultations</li>
                            <li><i class="fas fa-check-circle"></i> Remote monitoring</li>
                            <li><i class="fas fa-check-circle"></i> Online prescriptions</li>
                        </ul>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });
    </script>
</body>
</html>

