<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>DTS-ZPPSU | Document Tracking System</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts (optional) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* ============================================================
                   GLOBAL VARIABLES & RESET
                   ============================================================ */
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

        /* ============================================================
                   NAVIGATION (taken from the first code – placed above header)
                   ============================================================ */
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
            flex-wrap: wrap;
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-logo {
            height: 50px;
            margin-right: 0;
        }

        .nav-title {
            display: flex;
            flex-direction: column;
            color: var(--univ-maroon);
            line-height: 1.2;
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
            color: var(--dark);
            cursor: pointer;
            padding: 5px;
        }

        /* ============================================================
                   HEADER (completely preserved – fixed broken icon)
                   ============================================================ */
        header {
            background: linear-gradient(to right, var(--secondary), var(--primary));
            color: white;
            padding: 1.0rem 0;
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

        /* ============================================================
                   MAIN CONTENT – FEATURE CARDS (aligned text)
                   ============================================================ */
        .container {
            max-width: 1100px;
            margin: 2rem auto;
            padding: 0 1.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .features-grid {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .top-row,
        .bottom-row {
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
            justify-content: flex-start;   /* changed from space-between */
            position: relative;
            overflow: hidden;
        }

        .bottom-row .feature-card {
            width: calc(50% - 1.2rem);
            max-width: 400px;
        }

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
            /* margin-top: auto;  REMOVED – text now sits directly under the title */
        }

        /* ============================================================
                   RESPONSIVE BREAKPOINTS
                   ============================================================ */
        @media (max-width: 900px) {
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
                left: 0;
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
                background: #f4d03f;
                margin: 0;
            }
            nav:not(.policy-links) {
                padding: 12px 20px;
                position: relative;
            }

            .top-row,
            .bottom-row {
                flex-direction: column;
                align-items: center;
            }
            .feature-card,
            .bottom-row .feature-card {
                width: 100%;
                max-width: 400px;
            }
            .container {
                margin: 1.5rem auto;
                padding: 0 1rem;
            }
            .feature-card {
                padding: 1.2rem;
                min-height: 140px;
            }

            header h1 {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 480px) {
            .nav-title span:first-child {
                font-size: 1rem;
            }
            .nav-title span:last-child {
                font-size: 0.7rem;
            }
            .nav-logo {
                height: 40px;
            }
            nav:not(.policy-links) {
                padding: 10px 15px;
            }
            .feature-card h3 {
                font-size: 1rem;
            }
            .feature-card p {
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>

    <!-- ============================================================
    NAVIGATION (from first code – placed ABOVE the header)
    ============================================================ -->
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

            <button class="mobile-menu-btn" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <!-- ============================================================
    HEADER (fixed icon and removed stray button)
    ============================================================ -->
    <header>
        <h1></i> DTS-ZPPSU</h1>
        <p>Zamboanga Peninsula Polytechnic State University</p>
    </header>

    <!-- ============================================================
    MAIN CONTENT (cards with aligned text)
    ============================================================ -->
    <div class="container">
        <div class="features-grid">
            <!-- Top 3 Centered Cards -->
            <div class="top-row">
                <div class="feature-card">
                    <h3><i class="fas fa-paper-plane"></i> Easy Document Requests</h3>
                    <p>Submit and track document requests with real‑time status updates for better workflow management.</p>
                </div>
                <div class="feature-card">
                    <h3><i class="fas fa-bolt"></i> Quick Processing</h3>
                    <p>Experience faster turnaround times for all your academic document requirements.</p>
                </div>
                <div class="feature-card">
                    <h3><i class="fas fa-user-friends"></i> User‑Friendly Interface</h3>
                    <p>Simple and intuitive design ensures easy navigation for all users.</p>
                </div>
            </div>

            <!-- Bottom 2 Centered Cards -->
            <div class="bottom-row">
                <div class="feature-card">
                    <h3><i class="fas fa-lock"></i> Secure & Confidential</h3>
                    <p>Your sensitive data is protected with enterprise‑grade security measures.</p>
                </div>
                <div class="feature-card">
                    <h3><i class="fas fa-database"></i> Centralized System</h3>
                    <p>Automated document workflows with complete tracking and audit trails.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer (Laravel Blade include) -->
    @include('footer')

    <!-- ============================================================
    JAVASCRIPT – Mobile menu toggle
    ============================================================ -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuBtn = document.querySelector('.mobile-menu-btn');
            const navLinks = document.querySelector('.nav-links');

            if (menuBtn && navLinks) {
                menuBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    navLinks.classList.toggle('active');
                });

                document.addEventListener('click', function(e) {
                    if (!e.target.closest('nav')) {
                        navLinks.classList.remove('active');
                    }
                });

                navLinks.querySelectorAll('a').forEach(function(link) {
                    link.addEventListener('click', function() {
                        navLinks.classList.remove('active');
                    });
                });
            }
        });
    </script>

</body>
</html>