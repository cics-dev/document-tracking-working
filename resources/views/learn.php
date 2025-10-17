<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTS-ZPPSU | Document Tracking System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ===== Global Styles ===== */
        :root {
            --primary: #800000; /* ZPPSU Maroon */
            --secondary: #660710; /* Darker Maroon */
            --accent: #FFD700; /* Gold accent */
            --light: #f8f9fa;
            --dark: #343a40;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
            --univ-maroon: #800020;
            --univ-dark-maroon: #5a0018;
            --univ-light-maroon: #a30029;
            --univ-cream: #f8f4e9;
            --univ-gold: #d4af37;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e6e6e6 100%);
            min-height: 100vh;
            color: var(--dark);
            line-height: 1.5;
            display: flex;
            flex-direction: column;
        }

        /* ===== Header ===== */
        header {
            background: linear-gradient(to right, var(--secondary), var(--primary));
            color: white;
            padding: 1.8rem 0;
            text-align: center;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }

        header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: pulse 15s infinite linear;
        }

        @keyframes pulse {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        header h1 {
            font-size: 2.2rem;
            margin-bottom: 0.4rem;
            position: relative;
            animation: fadeInDown 1s ease;
        }

        header p {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            animation: fadeInUp 1s ease 0.3s both;
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

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Home Button */
        .home-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: var(--accent);
            color: var(--primary);
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: var(--shadow);
            z-index: 10;
        }

        .home-btn:hover, .home-btn:active, .home-btn:focus {
            background-color: white;
            transform: translateY(-2px);
            outline: none;
        }

        /* ===== Main Content ===== */
        .container {
            max-width: 1100px;
            margin: 2rem auto;
            padding: 0 1.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .features-grid {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .top-row, .bottom-row {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 1.2rem;
            width: 100%;
        }

        .top-row {
            margin-bottom: 1.2rem;
        }

        .feature-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            min-height: 150px;
            width: calc(33.333% - 1.2rem);
            max-width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        /* Adjust width for bottom row cards */
        .bottom-row .feature-card {
            width: calc(50% - 1.2rem);
            max-width: 400px;
        }

        /* Hover effects from first code */
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .feature-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--accent), var(--primary));
            transition: var(--transition);
        }

        .feature-card:hover::after {
            height: 10px;
        }

        .feature-card h3 {
            color: var(--primary);
            margin-bottom: 0.8rem;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .feature-card h3 i {
            color: var(--accent);
            font-size: 1.3rem;
        }

        .feature-card p {
            color: #555;
            font-size: 0.95rem;
            line-height: 1.4;
            margin-top: auto;
        }

        /* ===== Responsive ===== */
        @media (max-width: 900px) {
            .top-row, .bottom-row {
                flex-direction: column;
                align-items: center;
            }
            
            .feature-card, 
            .bottom-row .feature-card {
                width: 100%;
                max-width: 400px;
            }
            
            header h1 {
                font-size: 1.8rem;
            }
            
            .home-btn {
                position: relative;
                top: auto;
                left: auto;
                margin: 0 auto 1rem;
                display: inline-flex;
            }
            
            .container {
                margin: 1.5rem auto;
                padding: 0 1rem;
            }
            
            .feature-card {
                padding: 1.2rem;
                min-height: 140px;
            }
        }

        /* ===== Footer Styles ===== */
        .footer {
            width: 100%;
            padding: 40px 0 20px;
            background: linear-gradient(135deg, var(--univ-dark-maroon) 0%, var(--univ-gold) 100%);
            color: var(--univ-cream);
        }

        .footer .container {
            max-width: 1800px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .footer-top {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 2rem;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(248, 244, 233, 0.2);
        }

        .footer-col:first-child {
            flex: 2;
            min-width: 300px;
        }

        .footer-col {
            flex: 1;
            min-width: 180px;
        }

        .footer-logo-title {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
        }

        .footer-logo-title img {
            width: 60px;
            height: 60px;
            margin-right: 15px;
        }

        .footer-logo-title h4 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            color: var(--univ-cream);
        }

        .footer-logo-subtitle {
            font-size: 16px;
            color: var(--univ-gold);
            margin-bottom: 16px;
            font-weight: 500;
        }

        .footer-description {
            font-size: 15px;
            color: rgba(248, 244, 233, 0.8);
            max-width: 400px;
        }

        .footer-col h3 {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 12px;
            color: var(--univ-cream);
        }

        .footer-links,
        .footer-contact {
            list-style: none;
            padding: 0;
            margin: 0;
            color: rgba(248, 244, 233, 0.8);
        }

        .footer-links li,
        .footer-contact li {
            margin-bottom: 10px;
            cursor: default;
            transition: color 0.25s ease;
        }

        .footer-links li:hover,
        .footer-contact li:hover {
            color: var(--univ-gold);
        }

        .footer-contact li {
            font-size: 14px;
            white-space: pre-line;
        }

        .footer-bottom {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-top: 24px;
            font-size: 13px;
            color: rgba(248, 244, 233, 0.6);
        }

        .footer-bottom .copyright {
            flex: 1 1 300px;
        }

        .footer-bottom .policy-links {
            display: flex;
            gap: 20px;
            flex: 1 1 300px;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .footer-bottom .policy-links a {
            color: rgba(248, 244, 233, 0.6);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.25s ease;
        }

        .footer-bottom .policy-links a:hover {
            color: var(--univ-gold);
        }

        @media (max-width: 720px) {
            .footer-top {
                flex-direction: column;
            }
            .footer-bottom {
                justify-content: center;
                text-align: center;
            }
            .footer-bottom .policy-links {
                justify-content: center;
                margin-top: 8px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <button class="home-btn" onclick="window.location.href='/'">
            <i class="fas fa-home"></i> 
        </button>
        <h1><i class="fas fa-file-alt"></i> DTS-ZPPSU</h1>
        <p>Zamboanga Peninsula Polytechnic State University</p>
    </header>

    <!-- Main Content -->
    <div class="container">
        <div class="features-grid">
            <!-- Top 3 Centered Cards -->
            <div class="top-row">
                <div class="feature-card">
                    <h3><i class="fas fa-paper-plane"></i> Easy Document Requests</h3>
                    <p>Submit and track document requests with real-time status updates for better workflow management.</p>
                </div>
                <div class="feature-card">
                    <h3><i class="fas fa-bolt"></i> Quick Processing</h3>
                    <p>Experience faster turnaround times for all your academic document requirements.</p>
                </div>
                <div class="feature-card">
                    <h3><i class="fas fa-user-friends"></i> User-Friendly Interface</h3>
                    <p>Simple and intuitive design ensures easy navigation for all users.</p>
                </div>
            </div>
            
            <!-- Bottom 2 Centered Cards -->
            <div class="bottom-row">
                <div class="feature-card">
                    <h3><i class="fas fa-lock"></i> Secure & Confidential</h3>
                    <p>Your sensitive data is protected with enterprise-grade security measures.</p>
                </div>
                <div class="feature-card">
                    <h3><i class="fas fa-database"></i> Centralized System</h3>
                    <p>Automated document workflows with complete tracking and audit trails.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Updated Footer Section -->
    <footer class="footer" aria-label="Site Footer">
        <div class="container">
            <div class="footer-top">
                <div class="footer-col">
                    <div class="footer-logo-title">
                        <img src="https://zppsu.edu.ph/wp-content/uploads/2023/09/1111.png" alt="Zamboanga Peninsula Polytechnic State University official seal in red and gold with detailed emblematic design" />
                        <div>
                            <h4>DTS-ZPPSU</h4>
                            <div class="footer-logo-subtitle">Document Tracking System</div>
                        </div>
                    </div>
                    <p class="footer-description">
                        Streamlining document management for Zamboanga Peninsula Polytechnic State University with cutting-edge technology and user-friendly interfaces.
                    </p>
                </div>

                <div class="footer-col" aria-labelledby="quick-links-title">
                    <h3 id="quick-links-title">Quick Links</h3>
                    <ul class="footer-links" role="list">
                        <li tabindex="0">Dashboard</li>
                        <li tabindex="0">Track Document</li>
                        <li tabindex="0">Submit Request</li>
                        <li tabindex="0">Help Center</li>
                    </ul>
                </div>

                <div class="footer-col" aria-labelledby="services-title">
                    <h3 id="services-title">Services</h3>
                    <ul class="footer-links" role="list">
                        <li tabindex="0">Transcript of Records</li>
                        <li tabindex="0">Diploma Processing</li>
                        <li tabindex="0">Certificate Requests</li>
                        <li tabindex="0">Official Documents</li>
                    </ul>
                </div>

                <div class="footer-col" aria-labelledby="contact-title">
                    <h3 id="contact-title">Contact</h3>
                    <ul class="footer-contact" role="list">
                        <li tabindex="0">Zamboanga Peninsula Polytechnic State University</li>
                        <li tabindex="0">R.T. Lim Boulevard, Zamboanga City</li>
                        <li tabindex="0"><a href="mailto:support@zppsu.edu.ph" style="color:var(--univ-cream); text-decoration:none;">support@zppsu.edu.ph</a></li>
                        <li tabindex="0">+63 912 345 6789</li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <div class="copyright" aria-label="Copyright notice">
                    © 2025 Zamboanga Peninsula Polytechnic State University. All rights reserved.
                </div>
                <nav class="policy-links" aria-label="Privacy and terms navigation">
                    <a href="#" tabindex="0">Privacy Policy</a>
                    <a href="#" tabindex="0">Terms of Service</a>
                    <a href="#" tabindex="0">Support</a>
                </nav>
            </div>
        </div>
    </footer>

    <!-- JavaScript for enhanced button functionality -->
    <script>
        // Improved button responsiveness
        const homeBtn = document.querySelector('.home-btn');
        
        // For better touch devices support
        homeBtn.addEventListener('touchstart', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        homeBtn.addEventListener('touchend', function() {
            this.style.transform = '';
            window.location.href = '/';
        });
        
        // For better keyboard accessibility
        homeBtn.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                window.location.href = '/';
            }
        });
    </script>
</body>
</html>