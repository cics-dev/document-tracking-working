<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTS-ZPPSU | University Document Tracking System</title>

    <!-- PWA  -->
    <meta name="theme-color" content="#6777ef"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="apple-touch-icon" href="{{ asset('/assets/img/hd-logo.png') }}">
    <link rel="icon" href="{{ asset('/assets/img/hd-logo.png') }}" type="image/x-icon">
    <link rel="manifest" href="{{ asset('/manifest.json') }}">

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
        /* ===== Global Styles ===== */
        :root {
            --primary: #800000;              /* ZPPSU Maroon */
            --secondary: #660710;            /* Darker Maroon */
            --accent: #FFD700;               /* Gold accent */
            --light: #f8f9fa;
            --dark: #343a40;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
            --univ-maroon: #800020;
            --univ-dark-maroon: #5a0018;
            --univ-light-maroon: #a30029;
            --univ-cream: #f8f4e9;
            --univ-gold: #d4af37;
            --univ-gray: #94a3b8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e6e6e6 100%);
            min-height: 100vh;
            color: var(--dark);
            line-height: 1.6;
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
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ===== Navigation ===== */
        nav:not(.policy-links) {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background: white;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
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
            line-height: 1.2;
        }

        .nav-title span:last-child {
            font-size: 0.8rem;
            color: var(--univ-gray);
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .nav-links {
            display: flex;
            list-style: none;
            align-items: center;
            gap: 15px;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--univ-maroon);
            font-weight: 600;
            padding: 8px 28px;
            border-radius: 16px;
            z-index: 1;
            background: #f4d03f;
            position: relative;
            font-size: 17px;
            box-shadow: 4px 8px 19px -3px rgba(0,0,0,0.27);
            transition: all 250ms;
            overflow: hidden;
            cursor: pointer;
            display: inline-block;
        }

        .nav-links a::before {
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

        .nav-links a:hover {
            color: #e8e8e8;
        }

        .nav-links a:hover::before {
            width: 100%;
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--univ-dark);
            cursor: pointer;
            padding: 5px;
        }

        /* ===== Main Content ===== */
        .container {
            max-width: 1200px;                 /* widened slightly to comfortably fit 5 rectangles */
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
            gap: 1.5rem;
        }

        .features-grid h1 {
            text-align: center;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .circles-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 1.5rem;                     /* reduced from 2rem to fit 5 cards in one row on desktop */
            width: 100%;
        }

        /* ===== Portrait Rectangle Cards (was circle) ===== */
        .feature-card {
            background: white;
            padding: 1.5rem;
            border-radius: 20px;             /* changed from 50% to make portrait rectangle */
            box-shadow: var(--shadow);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
            width: 180px;                    /* slightly narrower to fit 5 in a row */
            height: 260px;                   /* taller than before – portrait rectangle */
            text-align: center;
            cursor: pointer;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            background: #f8f8f8;
        }

        .feature-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            transition: var(--transition);
            border-radius: 20px 20px 0 0;    /* match card's top corners */
        }

        .feature-card:hover::after {
            height: 8px;
        }

        .feature-card h3 {
            color: var(--primary);
            margin-bottom: 0.8rem;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .feature-card h3 img {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }

        .feature-card p {
            color: #555;
            font-size: 0.85rem;
            line-height: 1.4;
            display: none;
        }

        /* ===== Modal Styles (unchanged) ===== */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-container {
            background-color: white;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            transform: translateY(-50px);
            transition: transform 0.4s ease;
            position: relative;
        }

        .modal-overlay.active .modal-container {
            transform: translateY(0);
        }

        .modal-header {
            background: linear-gradient(to right, var(--secondary), var(--primary));
            color: white;
            padding: 1.2rem 1.5rem;
            border-radius: 12px 12px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 1.5rem;
            margin: 0;
        }

        .modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            transition: background-color 0.2s;
        }

        .modal-close:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-image {
            width: 100%;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .modal-image img {
            width: 100%;
            display: block;
        }

        .modal-text {
            line-height: 1.6;
        }

        .modal-text p {
            margin-bottom: 1rem;
        }

        /* ===== Responsive (adjusted for portrait rectangles and straight alignment) ===== */
        @media (max-width: 1024px) {
            .feature-card {
                width: 170px;
                height: 240px;
            }
            .feature-card h3 img {
                width: 75px;
                height: 75px;
            }
        }

        @media (max-width: 900px) {
            .circles-container {
                gap: 1.2rem;
            }
            .feature-card {
                width: 160px;
                height: 230px;
                padding: 1.2rem;
            }
            .feature-card h3 img {
                width: 70px;
                height: 70px;
            }
            header h1 {
                font-size: 1.8rem;
            }
            .container {
                margin: 1.5rem auto;
                padding: 0 1rem;
            }
            .mobile-menu-btn {
                display: block;
            }
            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                right: 0;
                width: 100%;
                background-color: white;
                flex-direction: column;
                padding: 20px;
                box-shadow: var(--shadow);
                gap: 10px;
            }
            .nav-links.active {
                display: flex;
            }
            .nav-links li {
                width: 100%;
            }
            .nav-links a {
                display: block;
                padding: 12px;
                border-radius: 4px;
                text-align: center;
            }
        }

        @media (max-width: 768px) {
            .circles-container {
                gap: 1rem;
                justify-content: center;
            }
            .feature-card {
                width: calc(50% - 1rem);    /* two cards per row with equal spacing */
                max-width: 200px;
                height: 220px;
                padding: 1rem;
            }
            .feature-card h3 img {
                width: 65px;
                height: 65px;
            }
            .feature-card h3 {
                font-size: 1.1rem;
            }
        }

        @media (max-width: 500px) {
            .feature-card {
                width: 100%;                 /* single column on very small screens */
                max-width: 260px;
                height: 210px;
                padding: 0.8rem;
            }
            .feature-card h3 img {
                width: 60px;
                height: 60px;
            }
            .feature-card h3 {
                font-size: 1rem;
            }
            .modal-container {
                width: 95%;
            }
            .modal-header {
                padding: 1rem;
            }
            .modal-body {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="nav-left">
            <img src="https://zppsu.edu.ph/wp-content/uploads/2023/09/1111.png" alt="ZPPSU Logo" class="nav-logo">
            <div class="nav-title">
                <span>DTS-ZPPSU</span>
                <span>Document Tracking System</span>
            </div>
        </div>
        
        <div class="nav-right">
            <ul class="nav-links">
                <li><a href="{{ url('/') }}">Home</a></li>
                @if(Route::has('dashboard'))
                    <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                @endif
                @if(Route::has('documents'))
                    <li><a href="{{ route('documents') }}">Documents</a></li>
                @endif
            </ul>
            
            <button class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <div class="container">
        <div class="features-grid">
            <h1>How To Use DTS-ZPPSU Document Tracking System</h1>
            <div class="circles-container">
                <div class="feature-card" id="step1-card">
                    <h3>
                        <img src="{{ asset('/assets/img/login.png') }}" alt="Send Document">
                    </h3>
                    <p>Welcome Page</p>
                    <h5>Welcome Page</h5>
                    <h5 style="color: maroon;"><i>Step 1</i></h5>
                </div>
                <div class="feature-card" id="step2-card">
                    <h3>
                        <img src="{{ asset('/assets/img/security.png') }}" alt="Fast Processing">
                    </h3>
                    <p>Login </p>
                    <h5>Login Page</h5>
                    <h5 style="color: maroon;"><i>Step 2</i></h5>
                </div>
                <div class="feature-card" id="step3-card">
                    <h3>
                        <img src="{{ asset('/assets/img/profile1.png') }}" alt="Collaborate">
                    </h3>
                    <p>Dashboard</p>
                    <h5>Landing Page</h5>
                    <h5 style="color: maroon;"><i>Step 3</i></h5>
                </div>
                <div class="feature-card" id="step4-card">
                    <h3>
                        <img src="{{ asset('/assets/img/contract.png') }}" alt="Secure Document">
                    </h3>
                    <p>Admin Dashboard</p>
                    <h5>Admin Dashboard</h5>
                    <h5 style="color: maroon;"><i>Step 4</i></h5>
                </div>
                <div class="feature-card" id="step5-card">
                    <h3>
                        <img src="{{ asset('/assets/img/profile.png') }}" alt="Efficient Data Management">
                    </h3>
                    <p>User Dashboard</p>
                    <h5>User Dashboard</h5>
                    <h5 style="color: maroon;"><i>Step 5</i></h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal-overlay" id="step1-modal">
        <div class="modal-container">
            <div class="modal-header">
                <h2>Landing Page Section</h2>
                <button class="modal-close"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="modal-image">
                    <img src="{{ asset('/assets/img/1.png') }}" alt="DTS-ZPPSU Instructions">
                </div>
                <div class="modal-text">
                    <center><p><b>DTS-ZPPSU Document Tracking System Follow these steps</b></p></center>
                    <p style="color: green; font-weight: bold;">Welcome to the DTS-ZPPSU Document Tracking System! Follow these steps to get started:</p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="step2-modal">
        <div class="modal-container">
            <div class="modal-header">
                <h2>Login Your Account</h2>
                <button class="modal-close"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="modal-image">
                    <img src="{{ asset('/assets/img/log.png') }}" alt="Fast Processing">
                </div>
                <div class="modal-text">
                    <center><p style="color: green; font-weight: bold;">Input your Email and Password</p></center>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="step3-modal">
        <div class="modal-container">
            <div class="modal-header">
                <h2>Welcome to Dashboard</h2>
                <button class="modal-close"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="modal-image">
                    <img src="{{ asset('/assets/img/2.png') }}" alt="Team Collaboration">
                </div>
                <div class="modal-text">
                    <p style="color: green; font-weight: bold;">
                        After Login you will automatically redirect to 
                        <span style="color: blue;">landing Page</span>
                        <span style="color: green;"> and you can click the Dashboard button to redirect to the</span>
                        <span style="color: blue;">Dashboard</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="step4-modal">
        <div class="modal-container">
            <div class="modal-header">
                <h2>Welcome to Admin Dashboard</h2>
                <button class="modal-close"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="modal-image">
                    <img src="{{ asset('/assets/img/Dadmin.png') }}" alt="Admin Dashboard">
                </div>
                <div class="modal-text">
                    <p style="color: green; font-weight: bold;">Welcome to the <span style="color: blue;">Admin Dashboard</span></p>
                </div>
                <div class="modal-image">
                    <img src="{{ asset('/assets/img/dadmin1.png') }}" alt="Offices">
                </div>
                <div class="modal-text">
                    <p style="color: green; font-weight: bold;">
                        Click the <span style="color: blue;">Offices</span>
                        <span style="color: green;">then you can click </span>
                        <span style="color: blue;">+ Add Office</span>...
                    </p>
                </div>
                <div class="modal-image">
                    <img src="{{ asset('/assets/img/dadmin2.png') }}" alt="Users">
                </div>
                <div class="modal-text">
                    <p style="color: green; font-weight: bold;">
                        Click the <span style="color: blue;">Users</span>
                        <span style="color: green;">then you can click </span>
                        <span style="color: blue;">+ Add Users</span>...
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="step5-modal">
        <div class="modal-container">
            <div class="modal-header">
                <h2>End Users Dashboard</h2>
                <button class="modal-close"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="modal-image">
                    <img src="{{ asset('/assets/img/udash.png') }}" alt="User Dashboard">
                </div>
                <div class="modal-text">
                    <p style="color: green; font-weight: bold;">
                        This is the End Users <span style="color: blue;">Dashboard</span>...
                    </p>
                </div>
            </div>
        </div>
    </div>

    @include('footer')

    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('.nav-links').classList.toggle('active');
        });

        // Animation & Modal logic
        document.addEventListener('DOMContentLoaded', function() {
            const featureCards = document.querySelectorAll('.feature-card');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = 1;
                        entry.target.style.transform = 'translateY(0) scale(1)';
                    }
                });
            }, { threshold: 0.1 });
            
            featureCards.forEach(card => {
                card.style.opacity = 0;
                card.style.transform = 'translateY(20px) scale(0.9)';
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                observer.observe(card);
            });

            const stepCards = {
                'step1-card': 'step1-modal',
                'step2-card': 'step2-modal',
                'step3-card': 'step3-modal',
                'step4-card': 'step4-modal',
                'step5-card': 'step5-modal'
            };
            
            Object.keys(stepCards).forEach(cardId => {
                const card = document.getElementById(cardId);
                const modal = document.getElementById(stepCards[cardId]);
                if (card && modal) {
                    card.addEventListener('click', () => {
                        modal.classList.add('active');
                        document.body.style.overflow = 'hidden';
                    });
                }
            });

            document.querySelectorAll('.modal-close').forEach(btn => {
                btn.addEventListener('click', function() {
                    this.closest('.modal-overlay').classList.remove('active');
                    document.body.style.overflow = '';
                });
            });

            document.querySelectorAll('.modal-overlay').forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        modal.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                });
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.modal-overlay.active').forEach(m => {
                        m.classList.remove('active');
                    });
                    document.body.style.overflow = '';
                }
            });
        });
    </script>
    
</body>
</html>