<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTS-ZPPSU | Document Tracking System</title>
    <link rel="icon" href="{{ asset('/assets/img/hd-logo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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

        /* ===== Navigation ===== */
        nav {
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

        /* Navigation links styled as buttons */
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
            gap: 2rem;
            width: 100%;
        }

        .feature-card {
            background: white;
            padding: 1.5rem;
            border-radius: 50%; /* Changed to circle */
            box-shadow: var(--shadow);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
            width: 180px; /* Slightly smaller than before */
            height: 180px; /* Slightly smaller than before */
            text-align: center;
            cursor: pointer;
        }

        /* Hover effects - Removed maroon-gold effect */
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
            border-radius: 50% 50% 0 0;
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
            width: 80px; /* Increased from 60px */
            height: 80px; /* Increased from 60px */
            object-fit: contain;
        }

        .feature-card p {
            color: #555;
            font-size: 0.85rem; /* Slightly smaller text for circular layout */
            line-height: 1.4;
            display: none; /* Hide text in circle layout */
        }
        
        /* ===== Modal Styles ===== */
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
        
        /* ===== Responsive ===== */
        @media (max-width: 1024px) {
            .feature-card {
                width: 170px; /* Increased for better visibility */
                height: 170px; /* Increased for better visibility */
            }
            
            .feature-card h3 img {
                width: 75px; /* Increased for better visibility */
                height: 75px; /* Increased for better visibility */
            }
        }

        @media (max-width: 900px) {
            .circles-container {
                gap: 1.5rem;
            }
            
            .feature-card {
                width: 160px; /* Increased for better visibility */
                height: 160px; /* Increased for better visibility */
                padding: 1.2rem;
            }
            
            .feature-card h3 img {
                width: 70px; /* Increased for better visibility */
                height: 70px; /* Increased for better visibility */
            }
            
            header h1 {
                font-size: 1.8rem;
            }
            
            .container {
                margin: 1.5rem auto;
                padding: 0 1rem;
            }

            /* Mobile Navigation Styles */
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
                gap: 1.2rem;
            }
            
            .feature-card {
                width: 170px; /* Increased for better mobile visibility */
                height: 170px; /* Increased for better mobile visibility */
                padding: 1rem;
            }
            
            .feature-card h3 img {
                width: 70px; /* Increased for better mobile visibility */
                height: 70px; /* Increased for better mobile visibility */
            }
            
            .feature-card h3 {
                font-size: 1.1rem;
            }
        }

        @media (max-width: 600px) {
            .circles-container {
                gap: 1rem;
            }
            
            .feature-card {
                width: 150px; /* Increased for better mobile visibility */
                height: 150px; /* Increased for better mobile visibility */
                padding: 1rem;
            }
            
            .feature-card h3 img {
                width: 70px; /* Increased for better mobile visibility */
                height: 70px; /* Increased for better mobile visibility */
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

        @media (max-width: 480px) {
            .circles-container {
                gap: 0.8rem;
            }
            
            .feature-card {
                width: 130px; /* Increased for better mobile visibility */
                height: 130px; /* Increased for better mobile visibility */
                padding: 1rem;
            }
            
            .feature-card h3 img {
                width: 55px; /* Increased for better mobile visibility */
                height: 55px; /* Increased for better mobile visibility */
            }
            
            .feature-card h3 {
                font-size: 0.95rem;
            }
        }

        @media (max-width: 400px) {
            .circles-container {
                gap: 0.7rem;
            }
            
            .feature-card {
                width: 120px; /* Minimum size for very small screens */
                height: 120px; /* Minimum size for very small screens */
                padding: 0.9rem;
            }
            
            .feature-card h3 img {
                width: 50px; /* Minimum size for very small screens */
                height: 50px; /* Minimum size for very small screens */
            }
            
            .feature-card h3 {
                font-size: 0.9rem;
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
                <span>ZPPSU</span>
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
            <!-- All circles in a single row -->
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

    <!-- Modal for Step 1 Instructions -->
    <div class="modal-overlay" id="step1-modal">
        <div class="modal-container">
            <div class="modal-header">
                <h2>Landing Page Section</h2>
                <button class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="modal-image">
                    <img src="{{ asset('/assets/img/1.png') }}" alt="DTS-ZPPSU Instructions">
                </div>
                <div class="modal-text">
                    <center><p><b>DTS-ZPPSU Document Tracking System Follow these steps</b></p></center>
                    <p style="color: green; font-weight: bold;">
                     Welcome to the DTS-ZPPSU Document Tracking System! Follow these steps to get started:</p>


                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Step 2 -->
    <div class="modal-overlay" id="step2-modal">
        <div class="modal-container">
            <div class="modal-header">
                <h2>Login Your Account</h2>
                <button class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="modal-image">
                    <img src="{{ asset('/assets/img/log.png') }}" alt="Fast Processing">
                     <div class="modal-text">
                       <center> <p style="color: green; font-weight: bold;"><br>
                            Input your Email and Password 
                        <!--      <span style="color: blue; font-size: smaller;"><i>Click Signup</i></span> -->
                </div> </center>
                 <!--   <img src="{{ asset('/assets/img/1.3.png') }}" alt="Fast Processing">
                </div>
                <div class="modal-text">
                    <p style="color: green; font-weight: bold;">
                            Signup the Form then
                              <span style="color: blue; "><i>Click Signup</i></span>
                              <span style="color: green; ">and Proceed</span>
                              <span style="color: blue; "><i>Login</i></span> -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Step 3 -->
    <div class="modal-overlay" id="step3-modal">
        <div class="modal-container">
            <div class="modal-header">
                <h2>Welcome to Dashboard</h2>
                <button class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="modal-image">
                    <img src="{{ asset('/assets/img/2.png') }}" alt="Team Collaboration">
                </div>
                <div class="modal-text">
                    <p style="color: green; font-weight: bold;">
                            After Login you will automatically redirect to 
                              <span style="color: blue; bold;">landing Page</span>
                              <span style="color: green; bold;"> and you can click the Dashboard button to redirect to the</span>
                              <span style="color: blue; bold;">Dashboard</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Step 4 -->
    <div class="modal-overlay" id="step4-modal">
        <div class="modal-container">
            <div class="modal-header">
                <h2>Welcome to Admin Dashboard</h2>
                <button class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="modal-image">
                    <img src="{{ asset('/assets/img/Dadmin.png') }}" alt="Secure Document Handling">
                </div>
                <div class="modal-text">
                    <p style="color: green; font-weight: bold;">
                            Welcome to the
                              <span style="color: blue; bold;">Admin Dashboard</span>
                </div>
                <div class="modal-image">
                    <img src="{{ asset('/assets/img/dadmin1.png') }}" alt="Secure Document Handling">
                </div>
                <div class="modal-text">
                    <p style="color: green; font-weight: bold;">
                            Click the 
                              <span style="color: blue; bold;">Offices</span>
                              <span style="color: green; bold;">then you can click the </span>
                              <span style="color: blue; bold;">+ Add Office to Add Offices</span>
                              <span style="color: blue; bold;">to Add Offices</span>
                              <span style="color: green; bold;">you can also see the</span>
                              <span style="color: red; bold;">"Action" that you can </span>
                              <span style="color: orange; bold;"> "EDIT" </span>
                              <span style="color: red; bold;"> "DELETE" </span>
                              <span style="color: green; bold;"> the Offices</span>
                </div>
                <div class="modal-image">
                    <img src="{{ asset('/assets/img/dadmin2.png') }}" alt="Secure Document Handling">
                </div>
                <div class="modal-text">
                    <p style="color: green; font-weight: bold;">
                            Click the 
                              <span style="color: blue; bold;">Users</span>
                              <span style="color: green; bold;">then you can click the </span>
                              <span style="color: blue; bold;">+ Add Users to Add Users</span>
                              <span style="color: blue; bold;">to Add Users</span>
                              <span style="color: green; bold;">you can also see the</span>
                              <span style="color: red; bold;">"Action" that you can </span>
                              <span style="color: orange; bold;"> "EDIT" </span>
                              <span style="color: red; bold;"> "DELETE" </span>
                              <span style="color: green; bold;"> the Users</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Step 5 -->
    <div class="modal-overlay" id="step5-modal">
        <div class="modal-container">
            <div class="modal-header">
                <h2>End Users Dashboard</h2>
                <button class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="modal-image">
                    <img src="{{ asset('/assets/img/udash.png') }}" alt="Efficient Data Management">
                </div>
                <div class="modal-text">
                    <p style="color: green; font-weight: bold;">
                           This is the End Users
                              <span style="color: blue; bold;">Dashboard</span>
                              <span style="color: green; bold;">and it 2 buttons </span>
                              <span style="color: blue; bold;">Recieved Documents </span>
                              <span style="color: green; bold;">and </span>
                              <span style="color: blue; bold;">Send Documebts </span>
                </div>
                <div class="modal-image">
                    <img src="{{ asset('/assets/img/udash1.png') }}" alt="Efficient Data Management">
                </div>
                <div class="modal-text">
                    <p style="color: green; font-weight: bold;">
                         You can click 
                            <span style="color: blue; font-weight: bold;">Received Documents</span>
                            <span style="color: green; font-weight: bold;">to see if there are documents you have received</span>
                            <span style="color: red; font-weight: bold;">Inter-Office Documents</span>
                            <span style="color: green; font-weight: bold;">or</span>
                            <span style="color: red; font-weight: bold;">Intra-Office Documents</span> </p>
                </div>
                <div class="modal-image">
                    <img src="{{ asset('/assets/img/send.png') }}" alt="Efficient Data Management">
                </div>
                <div class="modal-text">
                    <p style="color: green; font-weight: bold;">
                     Click 
                     <span style="color: blue; font-weight: bold;">Send Documents</span>
                    <span style="color: green; font-weight: bold;">and click</span>
                    <span style="color: maroon; font-weight: bold;">+ Create Documents</span>

                    <div class="modal-image">
                    <img src="{{ asset('/assets/img/c1.png') }}" alt="Efficient Data Management">
                </div>
                <div class="modal-text">
                    <p style="color: green; font-weight: bold;">
                     Fill out 
                     <span style="color: blue; font-weight: bold;">Document Details</span>

                     <div class="modal-image">
                    <img src="{{ asset('/assets/img/c2.png') }}" alt="Efficient Data Management">
                </div>
                <div class="modal-text">
                    <p style="color: green; font-weight: bold;">
                     After the form is filled out completely
                     <span style="color: green; font-weight: bold;">You can </span>
                     <span style="color: maroon; font-weight: bold;">"Save as Draft" </span>
                     <span style="color: green; font-weight: bold;"> or </span>
                     <span style="color: maroon; font-weight: bold;">"Preview"</span>
                     <span style="color: green; font-weight: bold;">and</span>
                     <span style="color: maroon; font-weight: bold;">"Send"</span>
                     <span style="color: green; font-weight: bold;">the Document</span>

                    <div class="modal-image">
                    <img src="{{ asset('/assets/img/track.png') }}" alt="Efficient Data Management">
                </div>
                <div class="modal-text">
                    <p style="color: green; font-weight: bold;">
                     <span style="color: green; font-weight: bold;">After That on the </span>
                     <span style="color: maroon; font-weight: bold;">"Sidebar"</span>
                     <span style="color: green; font-weight: bold;">click the</span>
                     <span style="color: maroon; font-weight: bold;">"Send Documents"</span>
                     <span style="color: green; font-weight: bold;">and you can see the </span>
                     <span style="color: maroon; font-weight: bold;">"Track button"</span>
                     <span style="color: green; font-weight: bold;">and click it to </span>
                     <span style="color: maroon; font-weight: bold;">Track the Documents</span>

                </div>
                </div>
                </div>
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
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('.nav-links').classList.toggle('active');
        });

        // Animation for feature cards on scroll
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
            
            // Modal functionality for all steps
            const stepCards = {
                'step1-card': 'step1-modal',
                'step2-card': 'step2-modal',
                'step3-card': 'step3-modal',
                'step4-card': 'step4-modal',
                'step5-card': 'step5-modal'
            };
            
            // Add click event to each card
            Object.keys(stepCards).forEach(cardId => {
                const card = document.getElementById(cardId);
                const modalId = stepCards[cardId];
                const modal = document.getElementById(modalId);
                
                if (card && modal) {
                    card.addEventListener('click', function() {
                        modal.classList.add('active');
                        document.body.style.overflow = 'hidden'; // Prevent scrolling when modal is open
                    });
                }
            });
            
            // Close modal functionality
            const modalCloseButtons = document.querySelectorAll('.modal-close');
            modalCloseButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const modal = this.closest('.modal-overlay');
                    modal.classList.remove('active');
                    document.body.style.overflow = ''; // Re-enable scrolling
                });
            });
            
            // Close modal when clicking outside the modal content
            const modals = document.querySelectorAll('.modal-overlay');
            modals.forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        modal.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                });
            });
            
            // Close modal with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    modals.forEach(modal => {
                        if (modal.classList.contains('active')) {
                            modal.classList.remove('active');
                            document.body.style.overflow = '';
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>