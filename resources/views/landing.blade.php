<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTS-ZPPSU | University Document Tracking System</title>

    <!-- Fonts and Icons -->
    <link rel="shortcut icon" href="{{ asset('/assets/img/hd-logo.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Three.js import map -->
    <script type="importmap">
    {
        "imports": {
            "three": "https://unpkg.com/three@0.162.0/build/three.module.js",
            "three/addons/": "https://unpkg.com/three@0.162.0/examples/jsm/"
        }
    }
    </script>

    <style>
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
        --transition: all 0.3s ease;
        --accent: var(--univ-gold);
        --primary: var(--univ-maroon);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Inter', 'Georgia', 'Times New Roman', serif;
    }

    body {
        background: linear-gradient(54deg, var(--univ-maroon) 75%, var(--univ-gold) 100%);
        color: var(--univ-dark);
        padding-right: 0 !important;
    }

    body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown) {
        overflow-y: auto !important;
    }

    /* Navbar Styles */
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
        position: relative;
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
    }

    .nav-links {
        display: flex;
        list-style: none;
        align-items: center;
    }

    .nav-links li {
        margin-left: 12px;
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

    /* Login Button Styles */
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

    /* User Greeting */
    .welcome-user {
        font-weight: 600;
        color: var(--univ-maroon);
        margin-right: 15px;
    }

    /* Dashboard and Logout Buttons */
    .nav-button {
        background-color: var(--univ-maroon);
        color: white !important;
        padding: 10px 16px;
        border: 2px solid var(--univ-maroon);
        border-radius: 6px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .nav-button:hover {
        background-color: white !important;
        color: var(--univ-maroon) !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(128, 0, 32, 0.2);
    }

    .nav-button i {
        margin-right: 8px;
    }

    /* Mobile Dropdown Menu */
    .mobile-dropdown {
        display: none;
        position: absolute;
        top: 100%;
        right: 20px;
        background-color: white;
        border-radius: 8px;
        box-shadow: var(--shadow-lg);
        padding: 10px;
        min-width: 200px;
        z-index: 1000;
    }

    .mobile-dropdown.active {
        display: block;
    }

    .mobile-dropdown .nav-button {
        width: 100%;
        margin: 5px 0;
        text-align: left;
    }

    /* Main Content Styles */
    .container {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    section {
        padding: 60px 0;
        margin-top: 60px;
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
        font-size: 1.1rem;
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
        background-color: white !important;
        color: maroon !important;
        transform: translateY(-6px);
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
        padding-top: 90px;
        background: linear-gradient(135deg, rgba(248, 233, 233, 0.9)25%, rgba(248, 244, 233, 0.95) 100%), 
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
        animation: bounce 4s ease-in-out infinite;
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
        margin-top: 30px;
    }

    .feature-card {
        background: white;
        padding: 1.5rem;
        border-radius: 10px;
        box-shadow: var(--shadow);
        transition: var(--transition);
        min-height: 150px;
        max-width: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
    }

    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(128, 0, 0, 0.4), 0 0 15px rgba(255, 215, 0, 0.5);
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

    .feature-icon {
        width: 50px;
        height: 50px;
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
        margin-top: 30px;
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
        font-size: 1.5rem;
        margin: 0 auto 20px;
    }

    .steps::before {
        content: '';
        position: absolute;
        top: 35px;
        left: 0;
        right: 0;
        height: 3px;
        background-color: var(--univ-maroon);
        opacity: 0.2;
        z-index: 0;
    }

    /* CTA Section */
    .cta {
        background: linear-gradient(135deg, var(--univ-dark-maroon) 0%, var(--univ-gold) 100%);
        color: white;
        text-align: center;
        padding: 40px 0;
    }

    .cta h2 {
        color: white;
        margin-bottom: 1rem;
    }

    .cta p {
        color: rgba(255, 255, 255, 0.9);
        max-width: 700px;
        margin: 0 auto 20px;
    }

    .cta-buttons {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    .cta .btn-secondary {
        background-color: var(--univ-gold);
        color: var(--univ-dark);
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
        border: 2px solid #A9A9A9;
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
        font-size: 1rem;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    #trackForm button:hover {
        background: linear-gradient(to right, var(--univ-dark-maroon), var(--univ-maroon));
        transform: translateY(-4px);
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

        /* Mobile Navigation Styles */
        .mobile-menu-btn {
            display: block;
        }

        .nav-links {
            display: none;
        }

        .welcome-user {
            font-size: 0.9rem;
            margin: 10px 0;
            color: var(--univ-maroon);
        }

        .nav-button {
            width: 100%;
            margin: 5px 0;
            text-align: left;
        }

        /* Adjust Uiverse button for mobile */
        .login-btn {
            padding: 12px 20px;
            font-size: 15px;
            margin: 10px 0;
            width: 100%;
        }

        /* Mobile tracker form */
        #trackForm {
            flex-direction: column;
        }

        #trackForm button {
            justify-content: center;
            padding: 0.8rem;
            width: 100%;
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
            width: 40px;
            height: 40px;
        }

        .welcome-user {
            display: block;
        }

        .login-btn {
            padding: 10px 18px;
            font-size: 14px;
        }
        
        .step-number {
            width: 60px;
            height: 60px;
            font-size: 1.3rem;
        }
    }

    /* Custom SweetAlert Popup Styles */
    .swal2-popup {
        border-radius: 12px !important;
        padding: 1.5rem !important;
        width: 450px !important;
        max-width: 90% !important;
        margin-right: 0 !important;
    }

    .swal2-container {
        padding-right: 0 !important;
    }

    .swal2-title {
        color: dark !important;
        font-size: 1.5rem !important;
        margin-bottom: 1.5rem !important;
    }

    .swal2-html-container {
        text-align: left !important;
        font-size: 1rem !important;
        margin-bottom: 1.5rem !important;
    }

    .swal2-confirm {
        background-color: var(--univ-maroon) !important;
        border: none !important;
        padding: 10px 24px !important;
        border-radius: 8px !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
    }

    .swal2-confirm:hover {
        background-color: var(--univ-dark-maroon) !important;
        transform: translateY(-2px) !important;
        box-shadow: var(--shadow-lg) !important;
    }

    /* Updated Footer Styles */
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
        width: 70px;
        height: 70px;
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

    /* 3D Viewer Styles */
    #viewModelText {
        color: var(--univ-gold);
        cursor: pointer;
        text-decoration: underline;
        transition: all 0.3s ease;
    }

    #viewModelText:hover {
        color: white;
    }

    #three-container {
        width: 100%;
        height: 400px;
        background: transparent;
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
                    <a href="{{ route('dashboard') }}" class="nav-button">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li>
                    <form action="{{ route('logout') }}" method="POST" id="logout-form">
                        @csrf
                        <button type="submit" class="nav-button">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </li>
                @endguest
            </ul>

            <!-- Mobile Dropdown Menu -->
            <div class="mobile-dropdown" id="mobile-dropdown">
                @guest
                <form action="{{ route('login') }}" method="GET">
                    <button type="submit" class="login-btn">
                        LOGIN
                    </button>
                </form>
                @else
                <div class="welcome-user">
                    Welcome, {{ Auth::user()->name }}
                </div>
                <a href="{{ route('dashboard') }}" class="nav-button">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="nav-button">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
                @endguest
            </div>
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
                                <button type="submit"><i class="fas fa-search"></i> Track</button>
                                <input type="text" id="trackingID" placeholder="Enter Tracking ID" required>
                            </div>
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
                        <img src="https://cdn-icons-png.flaticon.com/128/9080/9080367.png" alt="Real-time Tracking" style="width: 50px; height: 50px;">
                    </div>
                    <h3>Real-time Tracking</h3>
                    <p>Monitor document status in real-time with our intuitive dashboard. Know exactly where your documents are at any moment.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="https://cdn-icons-png.flaticon.com/128/9821/9821144.png" alt="Automated Notifications" style="width: 50px; height: 50px;">
                    </div>
                    <h3>Automated Notifications</h3>
                    <p>Receive instant alerts for document actions, approvals, and deadlines. Never miss an important update again.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="https://cdn-icons-png.flaticon.com/128/2041/2041643.png" alt="Advanced Analytics" style="width: 50px; height: 50px;">
                    </div>
                    <h3>Advanced Analytics</h3>
                    <p>Gain valuable insights with comprehensive reporting tools. Identify bottlenecks and optimize your workflow.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="https://cdn-icons-png.flaticon.com/128/2592/2592258.png" alt="Secure Access" style="width: 50px; height: 50px;">
                    </div>
                    <h3>Secure Access</h3>
                    <p>Role-based permissions ensure sensitive documents are only accessible to authorized personnel.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="https://cdn-icons-png.flaticon.com/128/2920/2920329.png" alt="Mobile Friendly" style="width: 50px; height: 50px;">
                    </div>
                    <h3>Mobile Friendly</h3>
                    <p>Access the system from any device, anywhere. Our responsive design works perfectly on all screen sizes.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="https://cdn-icons-png.flaticon.com/128/10435/10435204.png" alt="Version Control" style="width: 50px; height: 50px;">
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
            </div>
        </div>
    </section>

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
                       <span id="viewModelText">3D LOGO</span>
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

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <!-- SweetAlert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Initialize AOS animations
        AOS.init({ duration: 1000, once: true });
        
        // Mobile menu toggle
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        const mobileDropdown = document.getElementById('mobile-dropdown');
        
        mobileMenuBtn.addEventListener('click', () => {
            mobileDropdown.classList.toggle('active');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.mobile-menu-btn') && !e.target.closest('.mobile-dropdown')) {
                mobileDropdown.classList.remove('active');
            }
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

            @guest
            // If user is not logged in, show login required alert
            Swal.fire({
                icon: 'warning',
                title: 'Login Required',
                html: '<center><b>For Security Purpose</b></center><br><center>You need to login first before tracking documents.</center>',
                confirmButtonColor: '#800020',
                showCancelButton: true,
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Login Now',
                cancelButtonText: 'Cancel',
                scrollbarPadding: false,
                width: '400px'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '{{ route('login') }}';
                }
            });
            return;
            @endguest

            if (!id) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please enter a valid Tracking ID.',
                    confirmButtonColor: '#800020',
                    scrollbarPadding: false,
                    width: '400px'
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
                },
                scrollbarPadding: false,
                width: '400px'
            });

            // Simulate API delay
            setTimeout(() => {
                if (sampleData[id]) {
                    const data = sampleData[id];
                    
                    // Create HTML content for the popup
                    const htmlContent = `
                        <div style="text-align: left;">
                            <div style="display: flex; align-items: center; margin-bottom: 15px;">
                                <i class="fas fa-file-alt" style="font-size: 28px; color: ${data.color}; margin-right: 10px;"></i>
                                <strong style="font-size: 16px;">Document:</strong> ${data.document}
                            </div>
                            <div style="display: flex; align-items: center; margin-bottom: 15px;">
                                <i class="fas fa-calendar-check" style="font-size: 28px; color: ${data.color}; margin-right: 10px;"></i>
                                <strong style="font-size: 16px;">Request Date:</strong> ${data.date}
                            </div>
                            <div style="display: flex; align-items: center; margin-bottom: 15px;">
                                <i class="fas fa-tasks" style="font-size: 28px; color: ${data.color}; margin-right: 10px;"></i>
                                <strong style="font-size: 16px;">Status:</strong> <span style="color: ${data.color}">${data.status}</span>
                            </div>
                            <div style="display: flex; align-items: center;">
                                <i class="fas fa-clock" style="font-size: 28px; color: ${data.color}; margin-right: 10px;"></i>
                                <strong style="font-size: 16px;">Estimated:</strong> ${data.estimated}
                            </div>
                        </div>
                    `;

                    Swal.fire({
                        icon: data.icon,
                        title: 'Document Tracking Result',
                        html: htmlContent,
                        confirmButtonColor: '#800020',
                        scrollbarPadding: false,
                        width: '400px',
                        customClass: {
                            popup: 'custom-swal-popup'
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Not Found',
                        text: 'No record found for this Tracking ID. Please verify and try again.',
                        confirmButtonColor: '#800020',
                        scrollbarPadding: false,
                        width: '400px'
                    });
                }
            }, 1500);
        });

        // 3D Logo Viewer Functionality
document.getElementById('viewModelText').addEventListener('click', async () => {
    await Swal.fire({
        title: '<span style="color: white;">ZPPSU 3D LOGO VIEWER</span>', // Added white color here
        html: '<div id="three-container"></div>',
        width: 600,
        padding: '1em',
        background: 'linear-gradient(135deg, #800000, #ffcc00)', // Maroon-Gold gradient
        showCloseButton: true,
        showConfirmButton: false,
        didOpen: () => {
            init3DViewer();
        }
    });
});

        async function init3DViewer() {
            const THREE = await import('three');
            const { OrbitControls } = await import('three/addons/controls/OrbitControls.js');

            let scene, camera, renderer, controls, particleSystem, logoPlane;
            const clock = new THREE.Clock();
            const container = document.getElementById('three-container');
            const numParticles = 25000;

            const params = {
                particleSize: 0.035,
                particleColor: 0xff5900,
                rotationSpeed: 0.1
            };

            init();
            animate();

            function init() {
                scene = new THREE.Scene();
                scene.background = null;

                camera = new THREE.PerspectiveCamera(60, container.clientWidth / container.clientHeight, 0.1, 1000);
                camera.position.z = 5;

                renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
                renderer.setSize(container.clientWidth, container.clientHeight);
                renderer.setPixelRatio(window.devicePixelRatio);
                container.appendChild(renderer.domElement);

                controls = new OrbitControls(camera, renderer.domElement);
                controls.enableDamping = true;

                scene.add(new THREE.AmbientLight(0xffffff, 0.8));
                const light = new THREE.DirectionalLight(0xffffff, 1);
                light.position.set(2, 2, 2);
                scene.add(light);

                createParticles();
                createLogo();
                window.addEventListener('resize', onWindowResize);
            }

            function createParticles() {
                const geometry = new THREE.BufferGeometry();
                const positions = new Float32Array(numParticles * 3);
                const colors = new Float32Array(numParticles * 3);

                for (let i = 0; i < numParticles; i++) {
                    const phi = Math.acos(-1 + (2 * i) / numParticles);
                    const theta = Math.sqrt(numParticles * Math.PI) * phi;
                    const x = Math.sin(phi) * Math.cos(theta);
                    const y = Math.sin(phi) * Math.sin(theta);
                    const z = Math.cos(phi);

                    const index = i * 3;
                    positions[index] = x * 1.5;
                    positions[index + 1] = y * 1.5;
                    positions[index + 2] = z * 1.5;

                    const color = new THREE.Color(params.particleColor);
                    color.offsetHSL(0, 0, (Math.random() - 0.5) * 0.5);
                    colors[index] = color.r;
                    colors[index + 1] = color.g;
                    colors[index + 2] = color.b;
                }

                geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
                geometry.setAttribute('color', new THREE.BufferAttribute(colors, 3));

                const material = new THREE.PointsMaterial({
                    size: params.particleSize,
                    vertexColors: true,
                    transparent: true,
                    opacity: 0.9,
                    sizeAttenuation: true,
                    blending: THREE.AdditiveBlending
                });

                particleSystem = new THREE.Points(geometry, material);
                scene.add(particleSystem);
            }

            function createLogo() {
                const textureLoader = new THREE.TextureLoader();
                const logoURL = 'https://upload.wikimedia.org/wikipedia/en/8/8e/Zamboanga_Peninsula_Polytechnic_State_University_-_Emblem.png';

                textureLoader.load(logoURL, (texture) => {
                    texture.encoding = THREE.sRGBEncoding;
                    texture.anisotropy = 16;

                    const geometry = new THREE.PlaneGeometry(2.5, 2.5); // Increased logo size
                    const material = new THREE.MeshStandardMaterial({
                        map: texture,
                        side: THREE.DoubleSide,
                        roughness: 0.4,
                        metalness: 0.2,
                        transparent: true,
                        alphaTest: 0.1,
                        opacity: 1
                    });

                    logoPlane = new THREE.Mesh(geometry, material);
                    logoPlane.position.set(0, 0, 0);
                    scene.add(logoPlane);
                });
            }

            function onWindowResize() {
                camera.aspect = container.clientWidth / container.clientHeight;
                camera.updateProjectionMatrix();
                renderer.setSize(container.clientWidth, container.clientHeight);
            }

            function animate() {
                requestAnimationFrame(animate);
                const delta = clock.getDelta();

                if (particleSystem) particleSystem.rotation.y += delta * params.rotationSpeed;
                if (logoPlane) logoPlane.rotation.y += delta * 0.3;

                controls.update();
                renderer.render(scene, camera);
            }
        }
    </script>
</body>
</html>