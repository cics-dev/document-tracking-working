<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTS-ZPPSU | University Document Tracking System</title>

    <!-- PWA  -->
    <meta name="theme-color" content="#6777ef"/>
    <link rel="apple-touch-icon" href="{{ asset('/assets/img/hd-logo.png') }}">
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
        
        /* Chat widget variables */
        --chat-accent: var(--univ-maroon);
        --chat-bg: #ffffff;
        --chat-text: #0f172a;
        --chat-muted: #64748b;
        --chat-border: rgba(15,23,42,0.12);
        --radius: 16px;
        --z: 2147483000;
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
        padding: 5px;
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
        top: 25px;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(135deg, var(--univ-dark-maroon) 50%, var(--univ-gold) 50%);
        opacity: 1.0;
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
        background: linear-gradient(to right, var(--univ-maroon), var(--univ-gold));
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

    /* Mobile Navigation Bar Styles (only visible on small screens) */
    .mobile-navbar {
        display: none;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        height: 70px;
        background: linear-gradient(90deg, var(--univ-maroon), var(--univ-gold));
        background-color: rgba(128, 0, 32, 0.3);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        opacity: 0.95;
        box-shadow: 0 8px 32px rgba(128, 0, 32, 0.25);
        border-top: 1px solid rgba(255, 255, 255, 0.2);
        justify-content: space-around;
        align-items: center;
        z-index: 1000;
        padding: 0 10px;
    }

    .mobile-nav-item {
        flex: 1;
        text-align: center;
        color: var(--univ-cream);
        text-decoration: none;
        opacity: 0.85;
        transition: all 0.3s ease;
        padding: 0 4px;
    }

    .mobile-nav-item i {
        font-size: 1.2rem;
        margin-bottom: 3px;
        display: block;
        transition: transform 0.2s;
    }

    .mobile-nav-item span {
        font-size: 0.65rem;
        font-weight: 500;
        letter-spacing: 0.3px;
    }

    .mobile-nav-item:hover {
        color: #ffffff;
    }

    .mobile-nav-item.active {
        color: var(--univ-cream);
        opacity: 1;
        transform: translateY(-3px);
    }

    .mobile-nav-item.active i {
        transform: scale(1.15);
        filter: drop-shadow(0 0 3px rgba(212, 175, 55, 0.6));
    }
    
    .mobile-dashboard-item {
        position: absolute;
        top: -30px;
        left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(135deg, var(--univ-maroon), var(--univ-gold));
        border-radius: 50%;
        width: 60px;
        height: 60px;
        display: flex;
        justify-content: center;
        align-items: center;
        box-shadow: 0 10px 20px rgba(128, 0, 32, 0.5);
        z-index: 10;
        border: 2px solid var(--univ-cream);
    }

    .mobile-dashboard-item:hover {
        transform: translateX(-50%) scale(1.05);
    }

    .mobile-dashboard-item img {
        width: 53px;
        height: 53px;
    }

    .mobile-dashboard-item::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        border-radius: 50%;
        animation: pulse 2s infinite;
        z-index: -1;
    }

    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(212, 175, 55, 0.5); }
        70% { box-shadow: 0 0 0 10px rgba(212, 175, 55, 0); }
        100% { box-shadow: 0 0 0 0 rgba(212, 175, 55, 0); }
    }

    .mobile-highlight {
        position: absolute;
        bottom: 8px;
        left: 0;
        height: 2px;
        width: 20%;
        background: var(--univ-cream);
        border-radius: 2px;
        transition: left 0.3s ease;
        z-index: 1;
    }

    .mobile-navbar::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(128, 0, 32, 0.1) 0%, transparent 70%);
        animation: rotate 15s linear infinite;
        z-index: 0;
    }

    /* Responsive Styles */
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

        /* Show mobile navbar only on small screens */
        .mobile-navbar {
            display: flex;
        }

        /* Adjust body padding to account for mobile navbar */
        body {
            padding-bottom: 70px;
        }
    }

    @media (min-width: 769px) {
        /* Ensure mobile navbar is hidden on larger screens */
        .mobile-navbar {
            display: none !important;
        }
        
        /* Remove bottom padding on larger screens */
        body {
            padding-bottom: 0;
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
    
    /* Chat Wrapper */
    .chat-wrapper {
      position: fixed;
      right: 24px;
      bottom: 24px;
      z-index: 9999;
    }

    /* Chat Button with GIF */
    .chat-launcher {
      position: relative;
      width: 56px;
      height: 56px;
      border-radius: 50%;
      display: grid;
      place-items: center;
      background: linear-gradient(135deg, #800000, #FFD700);
      border: none;
      cursor: pointer;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
      transition: transform 0.15s ease, box-shadow 0.2s ease;
      z-index: 1;
    }

    .chat-launcher:hover {
      transform: translateY(-2px) scale(1.05);
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3);
    }

    .chat-launcher:active {
      transform: scale(0.96);
    }

    .chat-launcher:focus-visible {
      outline: 3px solid #FFD700;
      outline-offset: 3px;
    }

    /* GIF Icon */
    .chat-icon {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      object-fit: cover;
    }

    /* Chat Badge */
    .chat-badge {
      position: absolute;
      top: 2px;
      right: 2px;
      min-width: 18px;
      height: 18px;
      background-color: #FFD700;
      color: #800000;
      font-size: 12px;
      font-weight: bold;
      line-height: 18px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0 4px;
      box-shadow: 0 0 0 2px white;
      z-index: 2;
    }

    /* Pulse Wave */
    .wave {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 100%;
      height: 100%;
      border-radius: 50%;
      transform: translate(-50%, -50%);
      background: radial-gradient(circle, rgba(255, 215, 0, 0.4), rgba(128, 0, 0, 0));
      animation: pulseWave 2s ease-out infinite;
      z-index: -1;
      pointer-events: none;
    }

    .wave::after,
    .wave::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 100%;
      height: 100%;
      border-radius: 50%;
      transform: translate(-50%, -50%);
      background: radial-gradient(circle, rgba(255, 215, 0, 0.4), rgba(128, 0, 0, 0));
      animation: pulseWave 2s ease-out infinite;
      pointer-events: none;
    }

    .wave::before {
      animation-delay: 1s;
    }

    @keyframes pulseWave {
      0% {
        transform: translate(-50%, -50%) scale(1);
        opacity: 0.8;
      }
      100% {
        transform: translate(-50%, -50%) scale(2.5);
        opacity: 0;
      }
    }

       /* Tooltip Styling */
    .tooltip {
      position: absolute;
      bottom: 70px;
      right: 50%;
      transform: translateX(50%);
      background-color: #800000;
      color: #FFD700;
      font-size: 14px;
      font-weight: 500;
      padding: 6px 10px;
      border-radius: 6px;
      white-space: nowrap;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.4s ease, transform 0.4s ease;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
      z-index: 0;
    }

    .tooltip::after {
      content: '';
      position: absolute;
      top: 100%;
      left: 50%;
      transform: translateX(-50%);
      border: 6px solid transparent;
      border-top-color: #800000;
    }

    /* Chat Popup Styles - FIXED */
    .chat-popup {
        position: fixed;
        right: 24px;
        bottom: 90px;
        width: 350px; /* Normal desktop width */
        height: 450px; /* Normal desktop height */
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        z-index: 9998;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        transform: translateY(20px);
        opacity: 0;
        pointer-events: none;
        transition: all 0.3s ease;
    }

    /* When visible - FIXED */
    .chat-popup.visible {
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto;
    }

    /* Close button styles - FIXED */
    .chat-popup .close-btn {
        align-self: flex-end;
        background: transparent;
        border: none;
        font-size: 24px;
        padding: 10px;
        cursor: pointer;
        z-index: 10000;
        position: absolute;
        top: 5px;
        right: 5px;
        color: white;
    }

    /* Mobile adjustment - FIXED */
    @media (max-width: 768px) {
        .chat-popup {
            right: 0;
            bottom: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            border-radius: 0;
        }
        
        .chat-popup .close-btn {
            top: 10px;
            right: 10px;
            color: white;
            background: rgba(0,0,0,0.3);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    }

    .chat-header {
        background: linear-gradient(135deg, var(--univ-maroon), var(--univ-gold));
        color: white;
        padding: 7px 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
    }
    
    .chat-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
    }
    
    .chat-close {
        background: none;
        border: none;
        color: white;
        font-size: 20px;
        cursor: pointer;
        z-index: 10001;
        padding: 5px 10px;
    }
    
    .chat-messages {
        flex: 1;
        padding: 15px;
        overflow-y: auto;
        background: #f8f9fa;
    }
    
    .message {
        margin-bottom: 15px;
        display: flex;
        align-items: flex-start;
    }
    
    .message.bot {
        justify-content: flex-start;
    }
    
    .message.user {
        justify-content: flex-end;
    }
    
    .message-avatar {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        background: transparent;
    }
    
    .message.user .message-avatar {
        background: transparent;
    }
    
    .message-content {
        max-width: 70%;
    }
    
    .message-bubble {
        padding: 10px 15px;
        border-radius: 18px;
        background: white;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        font-size: 14px;
        line-height: 1.4;
    }
    
    .message.bot .message-bubble {
        background: white;
        border-top-left-radius: 4px;
    }
    
    .message.user .message-bubble {
        background: linear-gradient(135deg, var(--univ-maroon), var(--univ-gold));
        color: white;
        border-top-right-radius: 4px;
    }
    
    .message-time {
        font-size: 11px;
        color: var(--univ-white);
        margin-top: 5px;
        text-align: right;
    }
    
    .chat-input-container {
        padding: 15px;
        border-top: 1px solid #eee;
        background: white;
        z-index: 10000;
        position: relative;
    }
    
    .chat-input-form {
        display: flex;
    }
    
    .chat-input {
        flex: 1;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 20px;
        outline: none;
        font-size: 14px;
        z-index: 10000;
    }
    
    .chat-input:focus {
        border-color: var(--univ-maroon);
    }
    
    .chat-send {
        margin-left: 10px;
        background: #008000;
        color: white;
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 10000;
    }
    
    .chat-send:hover {
        background: linear-gradient(135deg, var(--univ-maroon), var(--univ-gold));
    }
    
    .welcome-message {
        text-align: center;
        padding: 20px;
        color: var(--chat-muted);
        font-size: 14px;
    }
    
    /* Mobile-specific chat widget positioning - FIXED */
    @media (max-width: 768px) {
        .chat-wrapper {
            bottom: 100px;
            right: 16px;
            transform: scale(0.8);
            transform-origin: bottom right;
        }
        
        .chat-launcher {
            width: 52px;
            height: 52px;
        }
        
        .chat-icon {
            width: 38px;
            height: 38px;
        }
        
        .tooltip {
            display: none;
        }
        
        .chat-popup {
            right: 0;
            bottom: 0;
            width: 100%;
            height: 100%;
            border-radius: 0;
        }
    }
    
    /* Login button with animation */
    .login-btn-container {
        display: flex;
        align-items: center;
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
            
            <button class="mobile-menu-btn" onclick="window.location.href='{{ route('help') }}'">
                <img src="https://cdn-icons-gif.flaticon.com/15578/15578675.gif" alt="Menu" style="height: 30px;">
            </button>
            
            <ul class="nav-links">
                @guest
                <li>
                    <div class="login-btn-container">
                        <form action="{{ route('login') }}" method="GET">
                            <button type="submit" class="login-btn">
                                LOGIN
                                <div class="login-tooltip">Access your DTS-ZPPSU account</div>
                            </button>
                        </form>
                        <a href="{{ route('help') }}">
                            <img src="https://cdn-icons-gif.flaticon.com/15578/15578675.gif" alt="Login animation" style="height: 40px; margin-left: 10px;">
                        </a>
                    </div>
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
                <a href="{{ route('help') }}">
                    <img src="https://cdn-icons-gif.flaticon.com/15578/15578675.gif" alt="Login animation" style="height: 40px; margin-left: 10px;">
                </a>
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
                                <button type="submit"><i class="fas fa-search"></i> Track</button>
                                <input type="text" id="trackingID" placeholder="Enter Document ID" required>
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
                        Streamlining document management for Zamboanga Peninsula Polytechnic State University with cutting-edge technology and user-friendly
                       <span id="viewModelText">interfaces. </span>
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
                        <li tabindex="0">Certificate of Enrollment</li>
                        <li tabindex="0">Diploma Copy</li>
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

    <!-- Mobile Navigation Bar (only visible on small screens) -->
    <div class="mobile-navbar">
        <div class="mobile-highlight"></div>

        <a href="/" class="mobile-nav-item active" data-pos="0">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="#" class="mobile-nav-item" data-pos="1">
            <i class="fas fa-search"></i>
            <span>Track</span>
        </a>

        <a href="{{ route('dashboard') }}" class="mobile-dashboard-item">
            <img src="https://cdn-icons-png.flaticon.com/512/10165/10165606.png" alt="Dashboard Icon">
        </a>

        <a href="{{ route('learn') }}" class="mobile-nav-item" data-pos="2">
            <i class="fas fa-book"></i>
            <span>Learn</span>
        </a>
        @guest
        <a href="{{ route('login') }}" class="mobile-nav-item" data-pos="3">
            <i class="fas fa-sign-in-alt"></i>
            <span>Login</span>
        </a>
        @else
        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();" class="mobile-nav-item" data-pos="3">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
        <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
        @endguest
    </div>

    <!-- Chat Widget -->
    <div class="chat-wrapper">
        <!-- Pulse Effect -->
        <div class="wave"></div>

        <!-- Chat Button with GIF -->
        <button class="chat-launcher" id="chatLauncher" aria-controls="chatWindow" aria-expanded="false" aria-label="Open chat">
            <img src="https://cdn-icons-gif.flaticon.com/12134/12134116.gif" alt="Chat Icon" class="chat-icon" />
            <span class="chat-badge" id="chatBadge" style="display: none;">1</span>
        </button>

        <!-- Tooltip -->
        <div class="tooltip">We're Here</div>
    </div>
    
    <!-- Chat Popup - FIXED -->
    <div class="chat-popup" id="chatPopup">
        <div class="chat-header">
            <div style="display: flex; align-items: center;">
                <img src="{{ asset('/assets/img/hd-logo.png') }}" alt="DTS Logo" style="height: 40px; margin-right: 10px;">
                <h3 style="color: white; margin: 0;">DTS Assistant</h3>
            </div>
            <button class="chat-close" id="chatClose">❌</button>
        </div>
        <div class="chat-messages" id="chatMessages">
            <div class="welcome-message">
                <h6><p>Hello! I'm your DTS assistant. How can I help you today?</p></h6>
            </div>
        </div>
        <div class="chat-input-container">
            <form class="chat-input-form" id="chatForm">
                <input type="text" class="chat-input" id="chatInput" placeholder="Type your message..." autocomplete="off">
                <button type="submit" class="chat-send">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>

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
            "DTS-2025-001": {
                status: "Approved",
                document: "Transcript of Records",
                date: "2025-10-15",
                estimated: "Ready for pickup",
                icon: "success",
                color: "#28a745"
            },
            "DTS-2025-002": {
                status: "Processing",
                document: "Certificate of Enrollment",
                date: "2025-10-18",
                estimated: "3 business days",
                icon: "info",
                color: "#17a2b8"
            },
            "DTS-2025-003": {
                status: "Pending",
                document: "Diploma Copy",
                date: "2025-10-20",
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

        // Mobile navbar functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mobileNavItems = document.querySelectorAll('.mobile-nav-item');
            const mobileHighlight = document.querySelector('.mobile-highlight');
            let activeTab = 0;
            updateMobileHighlight();

            mobileNavItems.forEach(item => {
                item.addEventListener('click', e => {
                    e.preventDefault();
                    mobileNavItems.forEach(i => i.classList.remove('active'));
                    item.classList.add('active');
                    activeTab = parseInt(item.dataset.pos);
                    updateMobileHighlight();
                    
                    // Handle navigation based on the clicked item
                    switch(activeTab) {
                        case 0: // Home
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                            break;
                        case 1: // Track
                            document.getElementById('trackingID').focus();
                            break;
                        case 2: // Learn
                            window.location.href = '{{ route('learn') }}';
                            break;
                        case 3: // Login/Logout
                            // Handled by the link itself
                            break;
                    }
                });
            });

            function updateMobileHighlight() {
                const width = 100 / mobileNavItems.length;
                mobileHighlight.style.width = `${width}%`;
                mobileHighlight.style.left = `${activeTab * width}%`;
            }
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
        
        // Chat Widget Functionality - FIXED
        document.addEventListener('DOMContentLoaded', function() {
            const chatLauncher = document.getElementById('chatLauncher');
            const chatPopup = document.getElementById('chatPopup');
            const chatClose = document.getElementById('chatClose');
            const chatMessages = document.getElementById('chatMessages');
            const chatForm = document.getElementById('chatForm');
            const chatInput = document.getElementById('chatInput');
            const chatBadge = document.getElementById('chatBadge');
            const tooltip = document.querySelector('.tooltip');
            const wave = document.querySelector('.wave');
            
            // Variables to control tooltip behavior
            let tooltipInterval;
            let isChatOpen = false;
            let isMobileView = window.innerWidth <= 768;
            
            // Function to show tooltip
            function showTooltip() {
                if (!isChatOpen && !isMobileView) {
                    tooltip.style.opacity = '1';
                    tooltip.style.transform = 'translateX(50%) translateY(-4px)';
                    
                    // Hide after 3 seconds
                    setTimeout(() => {
                        tooltip.style.opacity = '0';
                        tooltip.style.transform = 'translateX(50%)';
                    }, 3000);
                }
            }
            
            // Start the interval for showing tooltip every 5 seconds
            function startTooltipInterval() {
                tooltipInterval = setInterval(showTooltip, 5000);
                // Show immediately on page load
                setTimeout(showTooltip, 1000);
            }
            
            // Check for mobile view on resize
            window.addEventListener('resize', function() {
                isMobileView = window.innerWidth <= 768;
                if (isMobileView && isChatOpen) {
                    // Hide the chat launcher when chat is open on mobile
                    chatLauncher.style.display = 'none';
                } else {
                    chatLauncher.style.display = 'grid';
                }
            });
            
            // Toggle chat popup - FIXED
            chatLauncher.addEventListener('click', function() {
                isChatOpen = !isChatOpen;
                chatPopup.classList.toggle('visible');
                
                if (isMobileView) {
                    // On mobile, hide the launcher when chat is open
                    chatLauncher.style.display = isChatOpen ? 'none' : 'grid';
                }
                
                if (chatPopup.classList.contains('visible')) {
                    chatBadge.style.display = 'none';
                    // Clear interval when chat is open
                    clearInterval(tooltipInterval);
                    // Focus on input when chat opens
                    setTimeout(() => {
                        chatInput.focus();
                    }, 100);
                } else {
                    // Restart interval when chat is closed
                    startTooltipInterval();
                }
            });
            
            // Close chat popup - FIXED
            chatClose.addEventListener('click', function() {
                isChatOpen = false;
                chatPopup.classList.remove('visible');
                // Show the launcher again on mobile when closing chat
                if (isMobileView) {
                    chatLauncher.style.display = 'grid';
                }
                // Restart interval when chat is closed
                startTooltipInterval();
            });
            
            // Start the initial tooltip interval
            startTooltipInterval();
            
            // Handle form submission - FIXED
            chatForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const message = chatInput.value.trim();
                if (message) {
                    addMessage('user', message);
                    chatInput.value = '';
                    
                    // Simulate bot response after a short delay
                    setTimeout(() => {
                        const response = getBotResponse(message);
                        addMessage('bot', response);
                    }, 500);
                }
            });
            
            // Add a new message to the chat
            function addMessage(sender, text) {
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${sender}`;
                
                const avatarDiv = document.createElement('div');
                avatarDiv.className = 'message-avatar';
                
                // Create image element for avatar
                const avatarImg = document.createElement('img');
                avatarImg.style.width = '30px';
                avatarImg.style.height = '30px';
                
                if (sender === 'user') {
                    avatarImg.src = 'https://cdn-icons-png.flaticon.com/128/15777/15777404.png';
                    avatarImg.alt = 'User Avatar';
                } else {
                    avatarImg.src = 'https://cdn-icons-png.flaticon.com/128/6008/6008363.png';
                    avatarImg.alt = 'Bot Avatar';
                }
                
                avatarDiv.appendChild(avatarImg);
                
                const contentDiv = document.createElement('div');
                contentDiv.className = 'message-content';
                
                const bubbleDiv = document.createElement('div');
                bubbleDiv.className = 'message-bubble';
                bubbleDiv.textContent = text;
                
                const timeSpan = document.createElement('div');
                timeSpan.className = 'message-time';
                timeSpan.textContent = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                
                bubbleDiv.appendChild(timeSpan);
                contentDiv.appendChild(bubbleDiv);
                
                if (sender === 'user') {
                    messageDiv.appendChild(contentDiv);
                    messageDiv.appendChild(avatarDiv);
                } else {
                    messageDiv.appendChild(avatarDiv);
                    messageDiv.appendChild(contentDiv);
                    
                    // Show badge if chat is closed
                    if (!chatPopup.classList.contains('visible')) {
                        chatBadge.style.display = 'flex';
                    }
                }
                
                // Remove welcome message if it exists
                const welcomeMsg = chatMessages.querySelector('.welcome-message');
                if (welcomeMsg && chatMessages.children.length > 1) {
                    welcomeMsg.remove();
                }
                
                chatMessages.appendChild(messageDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
            
            // Generate bot response based on user input
            function getBotResponse(text) {
                const lowerText = text.toLowerCase();
                
                if (lowerText.includes('track') || lowerText.includes('document')) {
                    return 'To track a document, go to the tracking section on our website or click the "Track" button in the mobile navigation. You\'ll need to enter your document ID.';
                } else if (lowerText.includes('login') || lowerText.includes('account')) {
                    return 'You can login to your DTS-ZPPSU account by clicking the "Login" button in the top right corner of the page.';
                } else if (lowerText.includes('help') || lowerText.includes('support')) {
                    return 'For immediate assistance, please contact our support team at support@zppsu.edu.ph or call +63 912 345 6789.';
                } else if (lowerText.includes('feature') || lowerText.includes('what can')) {
                    return 'DTS-ZPPSU offers real-time tracking, automated notifications, advanced analytics, secure access, mobile compatibility, and version control. Check our Features section for details.';
                } else if (lowerText.includes('hello') || lowerText.includes('hi')) {
                    return 'Hello! How can I help you with the DTS-ZPPSU system today?';
                } else {
                    return 'I\'m sorry, I didn\'t understand that. Could you rephrase your question? For common topics, try asking about document tracking, login help, or system features.';
                }
            }
            
            // Sample initial messages
            setTimeout(() => {
                if (chatMessages.querySelector('.welcome-message')) {
                    addMessage('bot', 'I can help you with document tracking, system features, and more. What would you like to know?');
                }
            }, 1500);
            
            // Close chat when clicking outside - FIXED
            document.addEventListener('click', function(e) {
                if (isChatOpen && 
                    !chatPopup.contains(e.target) && 
                    !chatLauncher.contains(e.target) &&
                    !e.target.closest('.chat-popup')) {
                    isChatOpen = false;
                    chatPopup.classList.remove('visible');
                    if (isMobileView) {
                        chatLauncher.style.display = 'grid';
                    }
                    startTooltipInterval();
                }
            });
        });
    </script>
    <script src="{{ asset('/sw.js') }}"></script>
    <script>
        if (!navigator.serviceWorker.controller) {
            navigator.serviceWorker.register("/sw.js").then(function (reg) {
                console.log("Service worker has been registered for scope: " + reg.scope);
            });
        }
    </script>
</body>
</html>