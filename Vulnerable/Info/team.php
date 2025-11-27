<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Team - Healthcare Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
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

        .team-section {
            padding: 60px 0;
        }

        .team-section .container {
            max-width: 1200px;
        }

        .team-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .team-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .team-avatar {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            margin: 0 auto 20px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 4px solid white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .team-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .team-avatar i {
            color: var(--secondary-color);
            font-size: 3rem;
        }

        .team-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .team-code {
            font-size: 1rem;
            color: var(--secondary-color);
            font-weight: 500;
            margin-bottom: 0;
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
            <h1>Our Team</h1>
            <p>Meet the dedicated professionals behind our healthcare management system.</p>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section">
        <div class="container">
            <div class="row" id="teamMembers">
                <!-- Team members will be added here -->
                <!-- Example structure:
                <div class="col-md-4 col-lg-3 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="team-card">
                        <div class="team-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <h3 class="team-name">Member Name</h3>
                        <p class="team-code">Code: 12345</p>
                    </div>
                </div>
                -->
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

        // Team members data - Add your team members here
        const teamMembers = [
            { name: "Omar Magdy Abdallah ElKhazendar", code: "21001646" },
            { name: "Kerelos Zakarya Tadros Botros", code: "21033693" },
            { name: "Ziad Mahmoud Ahmed Abdelgwad", code: "21065393" },
            { name: "Ahmed Mourad Mohamed Anwer", code: "21007626" },
            { name: "Ahmed Mohamed Ashri Mourad", code: "21049580" },
            { name: "Abdelrahman Ashraf Mohamed Abdo", code: "21117329" },
        ];

        // Function to get avatar URL for each team member (from local photos)
        function getAvatarUrl(code) {
            // Photo should be named with the member's code (e.g., 21001646.jpg)
            // Try different formats: jpg, jpeg, png
            // The code will try jpg first, and if not found, show placeholder
            return `uploads/team/${code}.jpg`;
        }

        // Render team members
        function renderTeamMembers() {
            const container = document.getElementById('teamMembers');
            if (teamMembers.length === 0) {
                container.innerHTML = '<div class="col-12 text-center"><p class="text-muted">Team members will be added soon.</p></div>';
                return;
            }

            let html = '';
            teamMembers.forEach((member, index) => {
                const avatarUrl = getAvatarUrl(member.code);
                html += `
                    <div class="col-md-6 col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="${(index % 3) * 100}">
                        <div class="team-card">
                            <div class="team-avatar">
                                <img src="${avatarUrl}" alt="${member.name}" 
                                     onerror="this.style.display='none'; this.parentElement.innerHTML='<i class=\\'fas fa-user\\' style=\\'font-size: 3rem; color: #3498DB;\\'></i>';"
                                     style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                            </div>
                            <h3 class="team-name">${member.name}</h3>
                            <p class="team-code">Code: ${member.code}</p>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        }

        // Initialize on page load
        renderTeamMembers();
    </script>
</body>
</html>

