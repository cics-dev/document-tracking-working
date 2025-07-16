<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTS-ZPPSU | University Document Tracking System</title>

    <!-- Fonts and Icons -->
    <!-- favicon -->
    <link rel="shortcut icon" href="{{ asset('/assets/img/hd-logo.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
    /* Combined CSS Styles */
    :root {
        --univ-maroon: #800020;
        --univ-dark-maroon: #5a0018;
        --univ-light-maroon: #a30029;
        --univ-cream: #f8f4e9;
        --univ-gold: #d4af37;
        --univ-dark: #1e293b;
        --univ-light: #f8fafc;
        --univ-gray: #94a3b8;
        --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Inter', 'Georgia', 'Times New Roman', serif;
    }

    body {
        background-color: var(--univ-maroon);
        color: var(--univ-dark);
        line-height: 1.6;
    }

    /* Navbar Styles with both login and logout functionality */
    header {
        background: white;
        box-shadow: var(--shadow);
        position: fixed;
        width: 100%;
        top: 0;
        z-index: 1000;
    }

    nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 30px;
    }

    .nav-left {
        display: flex;
        align-items: center;
    }

    .nav-logo {
        height: 50px;
        margin-right: 10px;
    }

    .nav-title {
        display: flex;
        flex-direction: column;
        color: var(--univ-maroon);
    }

    .nav-title span:first-child {
        font-weight: 700;
        font-size: 1.2rem;
    }

    .nav-title span:last-child {
        font-size: 0.8rem;
        color: var(--univ-gray);
    }

    .nav-right {
        display: flex;
        align-items: center;
        gap: 20px;
        position: relative;
    }

    .nav-links {
        display: flex;
        list-style: none;
        align-items: center;
    }

    .nav-links li {
        margin-left: 12px;  /*Adjust the Dashboard buttona and Logout Margin*/
    }

    .nav-links a {
        text-decoration: none;
        color: var(--univ-dark);
        font-weight: 600;
        transition: color 0.3s ease;
    }

    .nav-links a:hover {
        color: var(--univ-maroon);
    }

    .mobile-menu-btn {
        display: none;
        background: none;
        border: none;
        font-size: 1.5rem;
        color: var(--univ-dark);
        cursor: pointer;
    }

    /* Uiverse.io Login Button Styles */
    .login-btn {
        padding: 8px 28px;
        border: unset;
        border-radius: 16px;
        color: var(--univ-maroon);
        z-index: 1;
        background: #f4d03f;
        position: relative;
        font-weight: 850;
        font-size: 17px;
        box-shadow: 4px 8px 19px -3px rgba(0,0,0,0.27);
        transition: all 250ms;
        overflow: hidden;
        cursor: pointer;
        margin: 10px;
    }

    .login-btn::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 0;
        border-radius: 15px;
        background-color: var(--univ-dark-maroon);
        z-index: -1;
        box-shadow: 4px 8px 19px -3px rgba(0,0,0,0.27);
        transition: all 250ms;
    }

    .login-btn:hover {
        color: #e8e8e8;
    }

    .login-btn:hover::before {
        width: 100%;
    }

    .login-tooltip {
        position: absolute;
        top: -45px;
        left: 50%;
        transform: translateX(-50%);
        background-color: var(--univ-dark-maroon);
        color: white;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 0.875rem;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        white-space: nowrap;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .login-tooltip::after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 50%;
        transform: translateX(-50%);
        border-width: 5px 5px 0;
        border-style: solid;
        border-color: var(--univ-dark-maroon) transparent transparent;
    }

    .login-btn:hover .login-tooltip {
        opacity: 1;
        visibility: visible;
        top: -50px;
    }

    /* User Greeting and Dashboard Link */
    .welcome-user {
        font-weight: 600;
        color: var(--univ-gold);
        margin-right: 15px;
    }

    /* Updated Dashboard Button Styles */
    .dashboard-btn {
        background-color: var(--univ-maroon);
        color: white !important;
        padding: 10px 16px;
        border: 2px solid var(--univ-maroon);
        border-radius: 6px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .dashboard-btn:hover {
        background-color: white !important;
        color: var(--univ-maroon) !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(128, 0, 32, 0.2);
    }

    /* Logout Button with Dropdown */
    .logout-container {
        position: relative;
    }

    .logout-icon {
        font-size: 1.2rem;
        color: var(--univ-dark);
        cursor: pointer;
        transition: all 0.3s ease;
        background: none;
        border: none;
        padding: 8px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .logout-icon:hover {
        background-color: rgba(0, 0, 0, 0.1);
    }

    .logout-img {
        width: 28px;
        height: 28px;
        transition: all 0.3s ease;
    }

    .logout-dropdown {
        position: absolute;
        right: 0;
        top: 120%;
        background-color: white;
        border-radius: 12px;
        box-shadow: var(--shadow-lg);
        padding: 10px 0;
        min-width: 180px;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 100;
    }

    .logout-container:hover .logout-dropdown {
        opacity: 1;
        visibility: visible;
        top: 100%;
    }

    .logout-dropdown form {
        display: block;
        width: 100%;
    }

    .logout-dropdown button {
        width: 100%;
        text-align: left;
        padding: 12px 20px;
        background: none;
        border: none;
        color: var(--univ-dark);
        cursor: pointer;
        transition: background-color 0.3s ease;
        font-weight: 500;
        display: flex;
        align-items: center;
    }

    .logout-dropdown button .logout-img {
        margin-right: 10px;
        width: 18px;
        height: 18px;
    }

    .logout-dropdown button:hover {
        background-color: var(--univ-cream);
        color: var(--univ-maroon);
    }

    /* Main Content Styles */
    .container {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    section {
        padding: 80px 0;
        margin-top: 80px; /* Added to account for fixed header */
    }

    h1, h2, h3, h4 {
        margin-bottom: 1.5rem;
        line-height: 1.2;
        font-weight: 600;
        color: var(--univ-maroon);
    }

    h1 {
        font-size: 2.5rem;
        font-weight: 700;
    }

    h2 {
        font-size: 2rem;
    }

    h3 {
        font-size: 1.5rem;
    }

    p {
        margin-bottom: 1rem;
        color: var(--univ-gray);
        font-size: 1.1rem;
    }

    .btn {
        display: inline-block;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        font-size: 1rem;
    }

    .btn-primary {
        background-color: var(--univ-maroon);
        color: white;
    }

    .btn-primary:hover {
        background-color: var(--univ-dark-maroon);
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .btn-secondary {
        background-color: var(--univ-maroon);
        color: white !important;
    }

    .btn-secondary:hover {
        background-color: #c9a227;
        transform: translateY(-9px);
        box-shadow: var(--shadow-lg);
        background-color: white !important;
        color: maroon !important;
        box-shadow: 0 4px 12px rgba(128, 0, 32, 0.2);
    }

    .text-center {
        text-align: center;
    }

    .text-primary {
        color: var(--univ-maroon);
    }

    .text-gold {
        color: var(--univ-gold);
    }

    .mb-4 {
        margin-bottom: 2rem;
    }

    /* Hero Section */
    .hero {
        padding-top: 115px;
        background: linear-gradient(135deg, rgba(248, 244, 233, 0.9) 0%, rgba(248, 244, 233, 0.95) 100%), 
                    url('https://images.unsplash.com/photo-1434030216411-0b793f4b4173?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80');
        background-size: cover;
        background-position: center;
    }

    .hero-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
    }

    .hero-text {
        flex: 1;
        min-width: 300px;
        padding-right: 40px;
    }

    .hero-image {
        flex: 1;
        min-width: 300px;
    }

    .hero-image img {
        width: 100%;
        max-width: 600px;
        height: auto;
        border-radius: 16px;
        box-shadow: var(--shadow-lg);
        border: 8px solid white;
    }

    /* Features Section */
    .features {
        background-color: white;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        margin-top: 50px;
    }

    .feature-card {
        background-color: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: var(--shadow);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-top: 4px solid var(--univ-maroon);
    }

    .feature-card:hover {
        transform: translateY(-10px);
        box-shadow: var(--shadow-lg);
        background: rgba(248, 244, 233, 0.3);
    }

    .feature-icon {
        width: 40px;
        height: 40px;
        margin-bottom: 20px;
    }

    /* How It Works Section */
    .how-it-works {
        background-color: var(--univ-cream);
    }

    .steps {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        margin-top: 50px;
        position: relative;
    }

    .step {
        flex: 1;
        min-width: 200px;
        text-align: center;
        padding: 0 20px;
        position: relative;
        z-index: 1;
    }

    .step-number {
        width: 50px;
        height: 50px;
        background-color: var(--univ-maroon);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.2rem;
        margin: 0 auto 20px;
    }

    .steps::before {
        content: '';
        position: absolute;
        top: 25px;
        left: 0;
        right: 0;
        height: 3px;
        background-color: var(--univ-maroon);
        opacity: 0.2;
        z-index: 0;
    }

    /* CTA Section */
    .cta {
        background: linear-gradient(135deg, var(--univ-maroon) 0%, var(--univ-dark-maroon) 100%);
        color: white;
        text-align: center;
    }

    .cta h2 {
        color: white;
    }

    .cta p {
        color: rgba(255, 255, 255, 0.9);
        max-width: 700px;
        margin: 0 auto 30px;
    }

    .cta-buttons {
        display: flex;
     /*   justify-content: center; */
        gap: 20px;
        flex-wrap: wrap;
    }

    .cta .btn-secondary {
        background-color: var(--univ-gold);
        color: var(--univ-dark);
    }

    .cta .btn-secondary:hover {
        background-color: #c9a227;
    }

    /* Footer Copyright*/
    .copyright {
        text-align: center;
        padding-top: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        color: rgba(12, 11, 11, 0.7);
        font-size: 0.9rem;
    }

    /* Document Tracker Styles */
   

    #trackForm {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    #trackingID {
        flex: 1;
        padding: 0.8rem 1rem;
        border: 2px solid #ddd;
        border-radius: 5px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    #trackingID:focus {
        border-color: var(--univ-maroon);
        outline: none;
        box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.2);
    }

    #trackForm button {
        background: linear-gradient(to right, var(--univ-maroon), var(--univ-dark-maroon));
        color: white;
        border: none;
        padding: 0 1.5rem;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    #trackForm button:hover {
        background: linear-gradient(to right, var(--univ-dark-maroon), var(--univ-maroon));
        transform: translateY(-2px);
    }

    #trackResult {
        margin-top: 1.1rem;
        padding: 1.1rem;
        background: #f8f9fa;
        border-radius: 3px;
        border-left: 4px solid var(--univ-maroon);
        text-align: left;
        display: none;
    }

    .status-item {
        display: flex;
        margin-bottom: 0.8rem;
    }

    .status-item:last-child {
        margin-bottom: 0;
    }

    .status-icon {
        margin-right: 0.8rem;
        color: var(--univ-maroon);
    }

    /* Responsive Styles */
    @media (max-width: 992px) {
        section {
            padding: 60px 0;
        }

        .hero-text {
            padding-right: 0;
            margin-bottom: 40px;
        }

        .hero-content {
            flex-direction: column;
        }

        .steps::before {
            display: none;
        }

        .step {
            margin-bottom: 40px;
        }
    }

    @media (max-width: 768px) {
        h1 {
            font-size: 2rem;
        }

        h2 {
            font-size: 1.8rem;
        }

        nav {
            padding: 15px 20px;
        }

        .nav-title span:first-child {
            font-size: 1rem;
        }

        .nav-title span:last-child {
            font-size: 0.7rem;
        }

        .nav-links {
            position: fixed;
            top: 80px;
            left: -100%;
            width: 100%;
            height: calc(100vh - 80px);
            background-color: white;
            flex-direction: column;
            align-items: center;
            padding: 40px 0;
            transition: left 0.3s ease;
        }

        .nav-links.active {
            left: 0;
        }

        .nav-links li {
            margin: 15px 0;
        }

        .mobile-menu-btn {
            display: block;
        }

        .welcome-user {
            font-size: 0.9rem;
        }

        .dashboard-btn {
            padding: 6px 12px;
            font-size: 0.9rem;
        }

        .logout-dropdown {
            position: static;
            opacity: 1;
            visibility: visible;
            box-shadow: none;
            padding: 0;
            margin-top: 10px;
            background: none;
        }

        .logout-dropdown button {
            padding: 8px 15px;
        }

        /* Adjust Uiverse button for mobile */
        .login-btn {
            padding: 12px 20px;
            font-size: 15px;
        }

        /* Mobile tracker form */
        #trackForm {
            flex-direction: column;
        }

        #trackForm button {
            justify-content: center;
            padding: 0.8rem;
        }
    }

    @media (max-width: 576px) {
        section {
            padding: 50px 0;
        }

        .btn {
            padding: 10px 20px;
        }

        .feature-card {
            padding: 20px;
        }

        .feature-icon {
            width: 35px;
            height: 35px;
        }

        .welcome-user {
            display: none;
        }

        .login-btn {
            padding: 10px 18px;
            font-size: 14px;
        }
    }
</style>
</head>
<body>
    <!-- Header with both login and logout functionality -->
    <header>
        <nav>
            <div class="nav-left">
                <img src="https://zppsu.edu.ph/wp-content/uploads/2023/09/1111.png" alt="Logo" class="nav-logo">
                <div class="nav-title">
                    <span>DTS-ZPPSU</span>
                    <span>Document Tracking System</span>
                </div>
            </div>
            
            <button class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>
            
            <ul class="nav-links">
                @guest
                <li>
                    
                    <form action="{{ route('login') }}" method="GET">
                        <button type="submit" class="login-btn">
                            LOGIN
                            <div class="login-tooltip">Access your DTS-ZPPSU account</div>
                        </button>
                    </form>
                </li>
                @else
                <li class="welcome-user">
                    Welcome, {{ Auth::user()->name }}
                </li>
                <li>
                    <a href="{{ route('dashboard') }}" class="dashboard-btn">Dashboard</a>
                </li>
                <li class="logout-container">
                    <button class="logout-icon" title="Logout">
                        <img src="{{ asset('/assets/img/switch.png') }}" alt="Logout" class="logout-img">
                    </button>
                    <div class="logout-dropdown">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit">
                                <img src="{{ asset('/assets/img/exit.png') }}" alt="Logout" class="logout-img"> <b>Logout</b>
                            </button>
                        </form>
                    </div>
                </li>
                @endguest
            </ul>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Streamline Your Documents Workflow with <span class="text-primary">DTS-ZPPSU</span></h1>
                    <p>The advanced document tracking system designed to revolutionize how ZPPSU manages, tracks, and processes official documents. Save time, reduce errors, and enhance productivity.</p>
                    <div class="tracker">
                        
                        <form id="trackForm">
                            <div class="cta-buttons">
                        <a href="{{ route('learn') }}" class="btn btn-secondary">Learn More</a>
                    </div>
                            <button type="submit"><i class="fas fa-search"></i> Track</button>
                            <input type="text" id="trackingID" placeholder="Enter Tracking ID" required>
                            
                        </form>
                        <div id="trackResult"></div>
                    </div>
                    
                </div>
                <div class="hero-image">
                    <img src="{{ asset('/assets/img/field.png') }}" alt="Logout" class="logout-img">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="text-center mb-4">
                <h2>Powerful Features for <span class="text-primary">Efficient Document Management</span></h2>
                <p>DTS-ZPPSU offers a comprehensive suite of tools designed to meet all your document tracking needs</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="https://cdn-icons-png.flaticon.com/128/9080/9080367.png" alt="Real-time Tracking" style="width: 40px; height: 40px;">
                    </div>
                    <h3>Real-time Tracking</h3>
                    <p>Monitor document status in real-time with our intuitive dashboard. Know exactly where your documents are at any moment.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="https://cdn-icons-png.flaticon.com/128/9821/9821144.png" alt="Automated Notifications" style="width: 40px; height: 40px;">
                    </div>
                    <h3>Automated Notifications</h3>
                    <p>Receive instant alerts for document actions, approvals, and deadlines. Never miss an important update again.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="https://cdn-icons-png.flaticon.com/128/2041/2041643.png" alt="Advanced Analytics" style="width: 40px; height: 40px;">
                    </div>
                    <h3>Advanced Analytics</h3>
                    <p>Gain valuable insights with comprehensive reporting tools. Identify bottlenecks and optimize your workflow.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="https://cdn-icons-png.flaticon.com/128/2592/2592258.png" alt="Secure Access" style="width: 40px; height: 40px;">
                    </div>
                    <h3>Secure Access</h3>
                    <p>Role-based permissions ensure sensitive documents are only accessible to authorized personnel.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="https://cdn-icons-png.flaticon.com/128/2920/2920329.png" alt="Mobile Friendly" style="width: 40px; height: 40px;">
                    </div>
                    <h3>Mobile Friendly</h3>
                    <p>Access the system from any device, anywhere. Our responsive design works perfectly on all screen sizes.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="https://cdn-icons-png.flaticon.com/128/10435/10435204.png" alt="Version Control" style="width: 40px; height: 40px;">
                    </div>
                    <h3>Version Control</h3>
                    <p>Maintain complete document history with our robust version control system. Track changes and revert when needed.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works">
        <div class="container">
            <div class="text-center mb-4">
                <h2>How <span class="text-primary">DTS-ZPPSU</span> Works</h2>
                <p>Simple steps to transform your document management process</p>
            </div>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Upload Documents</h3>
                    <p>Easily upload files through our secure portal or integrate with your existing systems.</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Assign Workflow</h3>
                    <p>Set up custom approval workflows tailored to your organization's needs.</p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Track Progress</h3>
                    <p>Monitor document movement in real-time with our visual tracking system.</p>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <h3>Archive & Retrieve</h3>
                    <p>Securely store completed documents with powerful search and retrieval tools.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="text-center">
                <h2>Ready to Transform Your Document Management?</h2>
                <p>Join dozens of departments at ZPPSU who have streamlined their workflows with our advanced tracking system</p>
                <div class="cta-buttons">
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">Dashboard</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="copyright">
            <p>&copy; All rights reserved. Zamboanga Peninsula Polytechnic State University</p>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <!-- SweetAlert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Initialize AOS animations
        AOS.init({ duration: 1000, once: true });
        
        // Mobile menu toggle
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        const navLinks = document.querySelector('.nav-links');
        
        mobileMenuBtn.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });
        
        // Add shadow to header on scroll
        window.addEventListener('scroll', () => {
            const header = document.querySelector('header');
            if (window.scrollY > 10) {
                header.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)';
            } else {
                header.style.boxShadow = 'none';
            }
        });

        // Enhanced logout functionality
        document.addEventListener('DOMContentLoaded', function() {
            const logoutContainer = document.querySelector('.logout-container');
            const logoutDropdown = document.querySelector('.logout-dropdown');
            
            logoutContainer.addEventListener('click', function(e) {
                e.stopPropagation();
                const isVisible = logoutDropdown.style.opacity === '1';
                logoutDropdown.style.opacity = isVisible ? '0' : '1';
                logoutDropdown.style.visibility = isVisible ? 'hidden' : 'visible';
                logoutDropdown.style.top = isVisible ? '120%' : '100%';
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function() {
                logoutDropdown.style.opacity = '0';
                logoutDropdown.style.visibility = 'hidden';
                logoutDropdown.style.top = '120%';
            });

            // Document tracking functionality
            const trackForm = document.getElementById('trackForm');
            const trackingID = document.getElementById('trackingID');

            // Sample tracking data (replace with API calls in a real system)
            const sampleData = {
                "DTS-2023-001": {
                    status: "Approved",
                    document: "Transcript of Records",
                    date: "2023-10-15",
                    estimated: "Ready for pickup",
                    icon: "success",
                    color: "#28a745"
                },
                "DTS-2023-002": {
                    status: "Processing",
                    document: "Certificate of Enrollment",
                    date: "2023-10-18",
                    estimated: "3 business days",
                    icon: "info",
                    color: "#17a2b8"
                },
                "DTS-2023-003": {
                    status: "Pending",
                    document: "Diploma Copy",
                    date: "2023-10-20",
                    estimated: "Under review",
                    icon: "warning",
                    color: "#ffc107"
                }
            };

            // Form submission
            trackForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const id = trackingID.value.trim();

                if (!id) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Please enter a valid Tracking ID.',
                        confirmButtonColor: '#800020'
                    });
                    return;
                }

                // Show loading state
                Swal.fire({
                    title: 'Searching...',
                    html: 'Please wait while we search for your document.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Simulate API delay
                setTimeout(() => {
                    if (sampleData[id]) {
                        const data = sampleData[id];
                        
                        // Create HTML content for the popup
                        const htmlContent = `
                            <div style="text-align: left;">
                                <div style="display: flex; align-items: center; margin-bottom: 15px;">
                                    <i class="fas fa-file-alt" style="font-size: 24px; color: ${data.color}; margin-right: 10px;"></i>
                                    <strong style="font-size: 16px;">Document:</strong> ${data.document}
                                </div>
                                <div style="display: flex; align-items: center; margin-bottom: 15px;">
                                    <i class="fas fa-calendar-check" style="font-size: 24px; color: ${data.color}; margin-right: 10px;"></i>
                                    <strong style="font-size: 16px;">Request Date:</strong> ${data.date}
                                </div>
                                <div style="display: flex; align-items: center; margin-bottom: 15px;">
                                    <i class="fas fa-tasks" style="font-size: 24px; color: ${data.color}; margin-right: 10px;"></i>
                                    <strong style="font-size: 16px;">Status:</strong> <span style="color: ${data.color}">${data.status}</span>
                                </div>
                                <div style="display: flex; align-items: center;">
                                    <i class="fas fa-clock" style="font-size: 24px; color: ${data.color}; margin-right: 10px;"></i>
                                    <strong style="font-size: 16px;">Estimated:</strong> ${data.estimated}
                                </div>
                            </div>
                        `;

                        Swal.fire({
                            icon: data.icon,
                            title: 'Document Tracking Result',
                            html: htmlContent,
                            confirmButtonColor: '#800020',
                            customClass: {
                                popup: 'custom-swal-popup'
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Not Found',
                            text: 'No record found for this Tracking ID. Please verify and try again.',
                            confirmButtonColor: '#800020'
                        });
                    }
                }, 1500);
            });
        });
    </script>
</body>
</html>