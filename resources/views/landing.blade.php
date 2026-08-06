<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DTS-ZPPSU | University Document Tracking System</title>

    <!-- PWA -->
    <meta name="theme-color" content="#6777ef" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="apple-touch-icon" href="{{ asset('/assets/img/hd-logo.png') }}" />
    <link rel="icon" href="{{ asset('/assets/img/hd-logo.png') }}" type="image/x-icon" />
    <link rel="manifest" href="{{ asset('/manifest.json') }}" />

    <!-- ===== FIX: prevent caching after login to ensure chat widget loads fresh ===== -->
    @auth
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    @endauth

    <!-- Fonts and Icons -->
    <link rel="shortcut icon" href="{{ asset('/assets/img/hd-logo.png') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" />

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
        /* ── Root Variables ── */
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
            --chat-accent: var(--univ-maroon);
            --chat-bg: #ffffff;
            --chat-text: #0f172a;
            --chat-muted: #64748b;
            --chat-border: rgba(15, 23, 42, 0.12);
            --radius: 16px;
            --z: 2147483000;
        }

        /* ── Reset & Base ── */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', 'Georgia', 'Times New Roman', serif;
        }

        body {
            /* Lightened transparent geometric pattern over maroon-gold gradient */
            --s: 200px;
            --line-color: rgba(255, 255, 255, 0.06);
            --gold-a: rgba(220, 157, 55, 0.1);
            --gold-b: rgba(254, 212, 80, 0.08);
            --teal: rgba(18, 92, 101, 0.04);
            --rust: rgba(188, 74, 51, 0.04);

            --_g: var(--gold-a) 25%, var(--gold-b) 0 50%, transparent 0;
            --_l1: var(--line-color) 0 1px, transparent 0 calc(25% - 1px), var(--line-color) 0 25%;
            --_l2: var(--line-color) 0 1px, transparent 0 calc(50% - 1px), var(--line-color) 0 50%;

            background:
                repeating-linear-gradient(45deg,  var(--_l1)),
                repeating-linear-gradient(-45deg, var(--_l1)),
                repeating-linear-gradient(0deg,   var(--_l2)),
                repeating-linear-gradient(90deg,  var(--_l2)),
                conic-gradient(from 135deg at 25% 75%, var(--_g)),
                conic-gradient(from 225deg at 25% 25%, var(--_g)),
                conic-gradient(from 45deg  at 75% 75%, var(--_g)),
                conic-gradient(from -45deg at 75% 25%, var(--_g)),
                repeating-conic-gradient(var(--teal) 0 45deg, var(--rust) 0 90deg),
                linear-gradient(54deg, var(--univ-maroon) 75%, var(--univ-gold) 100%);

            background-size:
                var(--s) var(--s),
                var(--s) var(--s),
                var(--s) var(--s),
                var(--s) var(--s),
                var(--s) var(--s),
                var(--s) var(--s),
                var(--s) var(--s),
                var(--s) var(--s),
                var(--s) var(--s),
                auto;

            color: var(--univ-dark);
            padding-right: 0 !important;
        }

        body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown) {
            overflow-y: auto !important;
        }

        /* ── Navbar ── */
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
            padding: 10px 30px;
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

        /* ── CUSTOM PRIVACY MODAL (LANDSCAPE) ── */
        .privacy-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(4px);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            transition: opacity 0.3s ease;
        }

        .privacy-modal-overlay.hidden {
            opacity: 0;
            pointer-events: none;
        }

        .privacy-modal {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.97), rgba(248, 244, 233, 0.99));
            border-radius: 28px;
            box-shadow: 0 28px 80px rgba(0, 0, 0, 0.18);
            padding: 1.5rem 2rem 1.2rem;
            width: 1000px;
            max-width: 96vw;
            min-height: 480px;
            aspect-ratio: 18 / 9;
            max-height: 92vh;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
            transform: translateY(-20px);
        }

        .privacy-modal::before {
            content: '';
            position: absolute;
            top: -28px;
            left: calc(50% - 28px);
            width: 0;
            height: 0;
            border-left: 28px solid transparent;
            border-right: 28px solid transparent;
            border-bottom: 28px solid #800020;
        }

        .privacy-modal .modal-body {
            display: flex;
            gap: 2rem;
            flex: 1;
            min-height: 0;
            overflow: hidden;
        }

        .privacy-modal .modal-image {
            flex: 0 0 220px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .privacy-modal .modal-image img {
            width: 100%;
            max-width: 200px;
            height: auto;
            object-fit: contain;
            border-radius: 16px;
        }

        .privacy-modal .modal-text {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            padding-right: 4px;
            gap: 0;
        }

        .privacy-modal .modal-text .text-content {
            overflow-y: auto;
            padding-right: 4px;
            margin-bottom: 10px;
        }

        .privacy-modal .modal-text .text-content h2 {
            margin: 0 0 0.35rem 0;
            font-size: 1.6rem;
            color: #3d1f1f;
        }

        .privacy-modal .modal-text .text-content .subtitle {
            margin: 0 0 0.75rem 0;
            font-size: 0.95rem;
            color: #525252;
        }

        .privacy-modal .modal-text .text-content p {
            margin-bottom: 0.6rem;
            line-height: 1.7;
            font-size: 0.92rem;
            color: #1f2937;
        }

        .privacy-modal .modal-text .consent-row {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-top: 0;
            padding-top: 6px;
            border-top: 1px solid rgba(128, 0, 32, 0.10);
            flex-wrap: wrap;
        }

        .privacy-modal .modal-text .consent-row input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #800020;
            flex-shrink: 0;
            cursor: pointer;
        }

        .privacy-modal .modal-text .consent-row label {
            font-size: 0.92rem;
            color: #1f2937;
            cursor: pointer;
            flex: 1;
        }

        .privacy-modal .modal-text .consent-row .btn-accept {
            background: var(--univ-maroon);
            color: white;
            border: none;
            padding: 8px 30px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            opacity: 0.6;
            pointer-events: none;
            flex-shrink: 0;
            margin-left: auto;
            margin-top: 5px;
        }

        .privacy-modal .modal-text .consent-row .btn-accept.enabled {
            opacity: 1;
            pointer-events: auto;
        }

        .privacy-modal .modal-text .consent-row .btn-accept.enabled:hover {
            background: #28a745;
            color: #ffffff;
            border-color: #28a745;
        }

        .privacy-modal .modal-actions {
            display: none;
        }

        @media (max-width: 820px) {
            .privacy-modal {
                aspect-ratio: auto;
                width: 96vw !important;
                min-height: 400px;
                max-height: 94vh;
                padding: 1.2rem 1.5rem 1rem;
            }
            .privacy-modal .modal-body {
                gap: 1rem;
                flex-wrap: wrap;
            }
            .privacy-modal .modal-image {
                flex: 0 0 120px;
            }
            .privacy-modal .modal-image img {
                max-width: 120px;
            }
            .privacy-modal .modal-text .text-content h2 {
                font-size: 1.3rem;
            }
            .privacy-modal .modal-text .text-content p {
                font-size: 0.85rem;
                line-height: 1.5;
            }
            .privacy-modal .modal-text .consent-row {
                padding-top: 4px;
                gap: 0.5rem;
            }
            .privacy-modal .modal-text .consent-row label {
                font-size: 0.85rem;
            }
            .privacy-modal .modal-text .consent-row .btn-accept {
                padding: 6px 18px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .privacy-modal {
                min-height: 340px;
                padding: 1rem;
                border-radius: 20px;
            }
            .privacy-modal .modal-body {
                gap: 0.6rem;
            }
            .privacy-modal .modal-image {
                flex: 0 0 80px;
            }
            .privacy-modal .modal-image img {
                max-width: 80px;
            }
            .privacy-modal .modal-text .text-content h2 {
                font-size: 1.1rem;
                margin-bottom: 0.2rem;
            }
            .privacy-modal .modal-text .text-content p {
                font-size: 0.78rem;
                line-height: 1.4;
                margin-bottom: 0.35rem;
            }
            .privacy-modal .modal-text .consent-row {
                padding-top: 4px;
                gap: 0.4rem;
                flex-wrap: wrap;
            }
            .privacy-modal .modal-text .consent-row label {
                font-size: 0.78rem;
                flex: 1 0 100%;
            }
            .privacy-modal .modal-text .consent-row .btn-accept {
                padding: 6px 16px;
                font-size: 0.85rem;
                margin-left: 0;
                width: 100%;
            }
        }

        /* ── other styles (unchanged) ── */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--univ-dark);
            cursor: pointer;
            padding: 5px;
        }

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
            box-shadow: 4px 8px 19px -3px rgba(0, 0, 0, 0.27);
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
            box-shadow: 4px 8px 19px -3px rgba(0, 0, 0, 0.27);
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

        .welcome-user {
            font-weight: 600;
            color: var(--univ-maroon);
            margin-right: 15px;
        }

        .nav-button {
            background-color: var(--univ-maroon);
            color: white !important;
            padding: 10px 16px;
            border: 2px solid var(--univ-maroon);
            border-radius: 6px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            line-height: 1.2;
            height: 44px;
            min-height: 44px;
            gap: 8px;
            white-space: nowrap;
            box-sizing: border-box;
        }

        .nav-button i {
            font-size: 1rem;
        }

        .nav-button:hover {
            background-color: white !important;
            color: var(--univ-maroon) !important;
            box-shadow: 0 4px 12px rgba(128, 0, 32, 0.2);
        }

        /* ── LOGOUT BUTTON SPECIFIC OVERRIDE ── */
        #logout-form .nav-button {
            background-color: white;
            color: var(--univ-maroon) !important;
            border: 2px solid var(--univ-maroon);
        }

        #logout-form .nav-button:hover {
            background-color: var(--univ-maroon) !important;
            color: white !important;
            box-shadow: 0 4px 12px rgba(128, 0, 32, 0.2);
        }

        #logout-form {
            display: inline-flex;
            align-items: center;
            margin: 0;
            padding: 0;
            line-height: 0;
        }

        #logout-form .nav-button {
            height: 44px;
            min-height: 44px;
            line-height: 1.2;
        }

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

        /* ── Container, Sections, Hero, etc. ── */
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

        h1,
        h2,
        h3,
        h4 {
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
        .text-gradient {
            background: linear-gradient(to right, var(--univ-maroon), var(--univ-gold));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
        }
        .mb-4 {
            margin-bottom: 2rem;
        }

        .hero {
            padding-top: 90px;
            background: linear-gradient(135deg, rgba(248, 233, 233, 0.9) 25%, rgba(248, 244, 233, 0.95) 100%),
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

        .cta {
            color: white;
            text-align: center;
            padding: 10px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding-bottom: 25px;
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

        /* Mobile Nav */
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
            position: relative;
            z-index: 1001;
            -webkit-tap-highlight-color: transparent;
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
            0% {
                box-shadow: 0 0 0 0 rgba(212, 175, 55, 0.5);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(212, 175, 55, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(212, 175, 55, 0);
            }
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
            pointer-events: none;
        }

        @media (max-width: 768px) {
            .mobile-navbar {
                display: flex;
            }
            body {
                padding-bottom: 70px;
            }
            .mobile-menu-btn {
                display: block;
            }
            .nav-links {
                display: none;
            }
            .hero {
                padding-top: 50px;
            }
            .hero-content {
                flex-direction: column;
                text-align: center;
            }
            .hero-text {
                padding-right: 0;
                text-align: center;
                margin-top: -20px;
            }
            .hero-image {
                margin-top: 20px;
            }
            .cta-buttons {
                justify-content: center;
                width: 100%;
            }
            .cta-buttons .btn-secondary,
            .cta-buttons #trackForm button {
                width: 100%;
                padding: 0.8rem;
                text-align: center;
                justify-content: center;
                display: block;
            }
            .steps {
                flex-direction: column;
                align-items: center;
                gap: 30px;
            }
            .steps::before {
                display: none;
            }
            .step {
                flex: none;
                width: 100%;
                max-width: 350px;
                text-align: center;
                padding: 0;
            }
            .mobile-nav-item {
                padding: 12px 4px;
                min-height: 60px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }
            .mobile-nav-item i {
                font-size: 1.3rem;
                margin-bottom: 4px;
            }
            .mobile-nav-item span {
                font-size: 0.7rem;
            }
            .mobile-navbar a[href="{{ route('login') }}"] {
                background: transparent !important;
                border: none !important;
                color: var(--univ-cream) !important;
            }
            #trackForm {
                flex-direction: column;
            }
            #trackForm button {
                justify-content: center;
                padding: 0.8rem;
                width: 100%;
            }
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
            .login-btn {
                padding: 12px 20px;
                font-size: 15px;
                margin: 10px 0;
                width: 100%;
            }
        }

        @media (min-width: 769px) {
            .mobile-navbar {
                display: none !important;
            }
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

        /* SweetAlert overrides */
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

        /* 3D Viewer */
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

        /* Chat Widget */
        .chat-wrapper {
            position: fixed;
            z-index: 9999;
        }

        .chat-wrapper.is-chat-open {
            display: none !important;
        }

        /* Desktop Wrapper (hello.png on the right side) */
        .chat-wrapper-desktop {
            right: 24px;
            left: auto;
            bottom: -4px;
            display: block;
        }

        .chat-wrapper-desktop .chat-launcher-hello {
            background: transparent !important;
            box-shadow: none !important;
            border: none !important;
            border-radius: 0 !important;
            width: auto !important;
            height: auto !important;
            padding: 0 !important;
            cursor: pointer;
            transition: none !important;
            position: relative;
            display: inline-block;
            transform: none !important;
            filter: none !important;
            animation: none !important;
        }

        /* Absolutely NO hover effect or animation on hello.png */
        .chat-wrapper-desktop .chat-launcher-hello:hover,
        .chat-wrapper-desktop .chat-launcher-hello:active,
        .chat-wrapper-desktop .chat-launcher-hello:focus,
        .chat-wrapper-desktop .chat-launcher-hello:focus-visible {
            transform: none !important;
            box-shadow: none !important;
            filter: none !important;
            outline: none !important;
            animation: none !important;
        }

        .chat-wrapper-desktop .chat-icon-hello {
            width: 185px;
            max-width: 220px;
            height: auto;
            border-radius: 0;
            object-fit: contain;
            display: block;
            transition: none !important;
            transform: none !important;
            animation: none !important;
        }

        /* Mobile Wrapper (smile.gif) - Hidden on Desktop */
        .chat-wrapper-mobile {
            display: none !important;
            right: 24px;
            left: auto;
            bottom: 24px;
        }

        .chat-launcher-smile {
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

        .chat-launcher-smile:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3);
        }
        .chat-launcher-smile:active {
            transform: scale(0.96);
        }
        .chat-launcher-smile:focus-visible {
            outline: 3px solid #FFD700;
            outline-offset: 3px;
        }

        .chat-icon-smile {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            object-fit: cover;
        }

        .chat-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #ef4444;
            color: white;
            font-size: 11px;
            font-weight: 700;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.25);
            z-index: 10;
        }

        .chat-wrapper-desktop .chat-badge {
            top: 4px;
            right: 12px;
        }

        .chat-wrapper-mobile .chat-badge {
            top: -6px;
            right: -6px;
        }

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

        .chat-popup {
            position: fixed;
            right: 24px;
            left: auto;
            bottom: 10px;
            width: 350px;
            height: 450px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            z-index: 10000;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transform: translateY(20px);
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s ease;
        }

        .chat-popup.visible {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

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

        @media (max-width: 768px) {
            .chat-wrapper-desktop {
                display: none !important;
            }
            .chat-wrapper-mobile {
                display: block !important;
                position: fixed;
                right: 16px;
                left: auto;
                bottom: 85px;
                transform: scale(0.85);
                transform-origin: bottom right;
            }
            .chat-popup {
                left: 0;
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
                background: rgba(0, 0, 0, 0.3);
                border-radius: 50%;
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
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
            background: linear-gradient(135deg, #2563EB, #31f517ff);
        }

        .welcome-message {
            text-align: center;
            padding: 20px;
            color: var(--chat-muted);
            font-size: 14px;
        }

        .login-btn-container {
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>

    @guest
    <!-- ===== CUSTOM PRIVACY MODAL ===== -->
    <div class="privacy-modal-overlay hidden" id="privacyModal">
        <div class="privacy-modal">
            <div class="modal-body">
                <div class="modal-image">
                    <img src="{{ asset('/assets/img/sec.png') }}" alt="Security Notice" />
                </div>
                <div class="modal-text">
                    <div class="text-content">
                        <h2>Privacy Notice</h2>
                        <p class="subtitle">ZPPSU Document Tracking System Consent</p>
                        <p>I have read and understood the <strong>Zamboanga Peninsula Polytechnic State University (ZPPSU) Document Tracking System (DTS-ZPPSU) Privacy Notice</strong> and voluntarily consent to the collection, processing, storage, and use of my personal information for document tracking, processing, monitoring, and other legitimate university related purposes.</p>
                        <p>I understand that my personal data will be handled in accordance with the <strong>Data Privacy Act of 2012 (Republic Act No. 10173)</strong> and will be protected through appropriate security measures.</p>
                        <p>I also acknowledge my rights as a data subject, including the right to be informed, access, correct, object to, or withdraw the processing of my personal data, as well as the right to claim damages and exercise data portability, subject to applicable laws and regulations.</p>
                        <p>By using the ZPPSU Document Tracking System, I confirm that I have read, understood, and voluntarily agree to the processing of my personal information for the purposes stated above.</p>
                    </div>
                    <div class="consent-row">
                        <input id="privacyConsentCheckbox" type="checkbox" />
                        <label for="privacyConsentCheckbox">I have read and agree to the privacy notice.</label>
                        <button class="btn-accept" id="acceptPrivacyBtn">I Accept</button>
                    </div>
                </div>
            </div>
            <div class="modal-actions" style="display:none;"></div>
        </div>
    </div>
    @endguest

    <!-- ===== HEADER ===== -->
    <header>
        <nav>
            <div class="nav-left">
                <img src="{{ asset('/assets/img/hd-logo.png') }}" alt="Logo" class="nav-logo" />
                <div class="nav-title">
                    <span>DTS-ZPPSU</span>
                    <span>Document Tracking System</span>
                </div>
            </div>

            <button class="mobile-menu-btn" onclick="window.location.href='{{ route('help') }}'">
                <img src="{{ asset('/assets/img/user-guide.png') }}" alt="Menu" style="height:30px;" />
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
                            <img src="{{ asset('/assets/img/user-guide.png') }}" alt="Login animation" style="height:40px; margin-left:10px;" />
                        </a>
                    </div>
                </li>
                @else
                <li class="welcome-user" style="display:flex; align-items:center; gap:8px;">
                    @if(Auth::user()->avatar_url)
                    <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" style="width:32px; height:32px; border-radius:50%; object-fit:cover; border:2px solid var(--univ-gold); box-shadow:0 2px 6px rgba(0,0,0,0.2);" />
                    @endif
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
                    <img src="{{ asset('/assets/img/user-guide.png') }}" alt="Help" style="height:40px; margin-left:10px;" />
                </a>
                @endguest
            </ul>
        </nav>
    </header>

    <!-- ===== HERO ===== -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Streamline Your Documents Workflow with <span class="text-gradient">DTS-ZPPSU</span></h1>
                    <p>The advanced document tracking system designed to revolutionize how ZPPSU manages, tracks, and processes official documents. Save time, reduce errors, and enhance productivity.</p>
                    <div class="tracker">
                        <form id="trackForm">
                            <div class="cta-buttons">
                                <a href="{{ route('learn') }}" class="btn btn-secondary">Learn More</a>
                                <button type="submit"><i class="fas fa-search"></i> Track</button>
                                <input type="text" id="trackingID" placeholder="Enter Document ID" required />
                            </div>
                        </form>
                        <div id="trackResult"></div>
                    </div>
                </div>
                <div class="hero-image">
                    <img src="{{ asset('/assets/img/field.png') }}" alt="Logout" class="logout-img" />
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FEATURES ===== -->
    <section class="features">
        <div class="container">
            <div class="text-center mb-4">
                <h2>Powerful Features for <span class="text-primary">Efficient Document Management</span></h2>
                <p>DTS-ZPPSU offers a comprehensive suite of tools designed to meet all your document tracking needs</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="{{ asset('/assets/img/realtime.png') }}" alt="Real-time Tracking" style="width:50px; height:50px;" />
                    </div>
                    <h3>Real-time Tracking</h3>
                    <p>Monitor document status in real-time with our intuitive dashboard. Know exactly where your documents are at any moment.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="{{ asset('/assets/img/bel.png') }}" alt="Automated Notifications" style="width:50px; height:50px;" />
                    </div>
                    <h3>Automated Notifications</h3>
                    <p>Receive instant alerts for document actions, approvals, and deadlines. Never miss an important update again.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="{{ asset('/assets/img/graph.png') }}" alt="Advanced Analytics" style="width:50px; height:50px;" />
                    </div>
                    <h3>Advanced Analytics</h3>
                    <p>Gain valuable insights with comprehensive reporting tools. Identify bottlenecks and optimize your workflow.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="{{ asset('/assets/img/shield.png') }}" alt="Secure Access" style="width:50px; height:50px;" />
                    </div>
                    <h3>Secure Access</h3>
                    <p>Role-based permissions ensure sensitive documents are only accessible to authorized personnel.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="{{ asset('/assets/img/phone.png') }}" alt="Mobile Friendly" style="width:50px; height:50px;" />
                    </div>
                    <h3>Mobile Friendly</h3>
                    <p>Access the system from any device, anywhere. Our responsive design works perfectly on all screen sizes.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="{{ asset('/assets/img/relation.png') }}" alt="Version Control" style="width:50px; height:50px;" />
                    </div>
                    <h3>Version Control</h3>
                    <p>Maintain complete document history with our robust version control system. Track changes and revert when needed.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== HOW IT WORKS ===== -->
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

    <!-- ===== CTA ===== -->
    <section class="cta">
        <div class="container">
            <div class="text-center">
                <h2>Ready to Transform Your Document Management?</h2>
                <p>Join dozens of departments at ZPPSU who have streamlined their workflows with our advanced tracking system</p>
            </div>
        </div>
    </section>

    <!-- ===== FOOTER ===== -->
    @include('footer')

    <!-- ===== MOBILE NAVBAR ===== -->
    <div class="mobile-navbar">
        <div class="mobile-highlight"></div>
        <a href="/" class="mobile-nav-item active" data-pos="0">
            <i class="fas fa-home"></i><span>Home</span>
        </a>
        <a href="#" class="mobile-nav-item" data-pos="1">
            <i class="fas fa-search"></i><span>Track</span>
        </a>
        <a href="{{ route('dashboard') }}" class="mobile-dashboard-item">
            <img src="{{ asset('assets/img/gear.png') }}" alt="Dashboard Icon" />
        </a>
        <a href="{{ route('learn') }}" class="mobile-nav-item" data-pos="2">
            <i class="fas fa-book"></i><span>Learn</span>
        </a>
        @guest
        <a href="{{ route('login') }}" class="mobile-nav-item" data-pos="3">
            <i class="fas fa-sign-in-alt"></i><span>Login</span>
        </a>
        @else
        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();" class="mobile-nav-item" data-pos="3">
            <i class="fas fa-sign-out-alt"></i><span>Logout</span>
        </a>
        <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" style="display:none;">
            @csrf
        </form>
        @endguest
    </div>

    <!-- ===== DESKTOP CHAT LAUNCHER (hello.png) ===== -->
    <div class="chat-wrapper chat-wrapper-desktop">
        <button type="button" class="chat-launcher chat-launcher-hello" aria-controls="chatPopup" aria-expanded="false" aria-label="Open chat">
            <img src="{{ asset('/assets/img/hello.png') }}" alt="Hello Assistant" class="chat-icon-hello" />
            <span class="chat-badge" style="display: flex;">1</span>
        </button>
    </div>

    <!-- ===== MOBILE CHAT LAUNCHER (smile.gif) ===== -->
    <div class="chat-wrapper chat-wrapper-mobile">
        <div class="wave"></div>
        <button type="button" class="chat-launcher chat-launcher-smile" id="chatLauncher" aria-controls="chatPopup" aria-expanded="false" aria-label="Open chat">
            <img src="{{ asset('/assets/img/smile.gif') }}" alt="Chat Icon" class="chat-icon-smile" />
            <span class="chat-badge" style="display: flex;">1</span>
        </button>
        <div class="tooltip">We're Here</div>
    </div>

    <!-- ===== CHAT POPUP ===== -->
    <div class="chat-popup" id="chatPopup" aria-hidden="true">
        <div class="chat-header">
            <div style="display:flex; align-items:center;">
                <img src="{{ asset('/assets/img/chat_header2.png') }}" alt="DTS Assistant" style="height:40px;" />
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
                <input type="text" class="chat-input" id="chatInput" placeholder="Type your message..." autocomplete="off" />
                <button type="submit" class="chat-send"><i class="fas fa-paper-plane"></i></button>
            </form>
        </div>
    </div>

    <!-- ===== SCRIPTS ===== -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Pass user login state and avatar to frontend -->
    <script>
        window.isUserLoggedIn = {{ Auth::check() ? 'true' : 'false' }};
        @auth
            window.userProfileAvatar = @json(Auth::user()->avatar_url ?? '');
        @else
            window.userProfileAvatar = '';
        @endauth
    </script>

    <script>
        // ── AOS ──
        if (window.AOS) {
            AOS.init({ duration: 1000, once: true });
        }

        // ── PRIVACY MODAL (shows only on first visit for unauthenticated users, persisted in localStorage) ──
        (function() {
            const modal = document.getElementById('privacyModal');
            if (!modal) return; // Hidden for logged in users (guest guard)

            const checkbox = document.getElementById('privacyConsentCheckbox');
            const acceptBtn = document.getElementById('acceptPrivacyBtn');

            // If already accepted in localStorage, keep hidden
            if (localStorage.getItem('privacyAccepted') === 'true') {
                modal.classList.add('hidden');
                return;
            }

            // Otherwise show the modal on first visit
            modal.classList.remove('hidden');

            if (checkbox && acceptBtn) {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        acceptBtn.classList.add('enabled');
                    } else {
                        acceptBtn.classList.remove('enabled');
                    }
                });

                acceptBtn.addEventListener('click', function() {
                    if (this.classList.contains('enabled')) {
                        localStorage.setItem('privacyAccepted', 'true');
                        modal.classList.add('hidden');
                    }
                });
            }
        })();

        // ── Mobile menu toggle ──
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        const mobileDropdown = document.getElementById('mobile-dropdown');
        if (mobileMenuBtn && mobileDropdown) {
            mobileMenuBtn.addEventListener('click', () => {
                mobileDropdown.classList.toggle('active');
            });
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.mobile-menu-btn') && !e.target.closest('.mobile-dropdown')) {
                    mobileDropdown.classList.remove('active');
                }
            });
        }

        // ── Header shadow on scroll ──
        window.addEventListener('scroll', () => {
            const header = document.querySelector('header');
            if (header) {
                if (window.scrollY > 10) {
                    header.style.boxShadow =
                        '0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06)';
                } else {
                    header.style.boxShadow = 'none';
                }
            }
        });

        // ── Document Tracking ──
        const trackForm = document.getElementById('trackForm');
        const trackingID = document.getElementById('trackingID');
        if (trackForm && trackingID) {
            const sampleData = {
                "DTS-2025-001": { status: "Approved", document: "Transcript of Records", date: "2025-10-15",
                    estimated: "Ready for pickup", icon: "success", color: "#28a745" },
                "DTS-2025-002": { status: "Processing", document: "Certificate of Enrollment", date: "2025-10-18",
                    estimated: "3 business days", icon: "info", color: "#17a2b8" },
                "DTS-2025-003": { status: "Pending", document: "Diploma Copy", date: "2025-10-20",
                    estimated: "Under review", icon: "warning", color: "#ffc107" }
            };

            trackForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const id = trackingID.value.trim();

                @guest
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

                Swal.fire({
                    title: 'Searching...',
                    html: 'Please wait while we search for your document.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); },
                    scrollbarPadding: false,
                    width: '400px'
                });

                setTimeout(() => {
                    if (sampleData[id]) {
                        const data = sampleData[id];
                        const htmlContent = `
                                <div style="text-align:left;">
                                    <div style="display:flex; align-items:center; margin-bottom:15px;">
                                        <i class="fas fa-file-alt" style="font-size:28px; color:${data.color}; margin-right:10px;"></i>
                                        <strong style="font-size:16px;">Document:</strong> ${data.document}
                                    </div>
                                    <div style="display:flex; align-items:center; margin-bottom:15px;">
                                        <i class="fas fa-calendar-check" style="font-size:28px; color:${data.color}; margin-right:10px;"></i>
                                        <strong style="font-size:16px;">Request Date:</strong> ${data.date}
                                    </div>
                                    <div style="display:flex; align-items:center; margin-bottom:15px;">
                                        <i class="fas fa-tasks" style="font-size:28px; color:${data.color}; margin-right:10px;"></i>
                                        <strong style="font-size:16px;">Status:</strong> <span style="color:${data.color}">${data.status}</span>
                                    </div>
                                    <div style="display:flex; align-items:center;">
                                        <i class="fas fa-clock" style="font-size:28px; color:${data.color}; margin-right:10px;"></i>
                                        <strong style="font-size:16px;">Estimated:</strong> ${data.estimated}
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
                            customClass: { popup: 'custom-swal-popup' }
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
        }

        // ── Mobile nav highlight ──
        (function() {
            const mobileNavItems = document.querySelectorAll('.mobile-nav-item');
            const mobileHighlight = document.querySelector('.mobile-highlight');
            if (!mobileNavItems.length || !mobileHighlight) return;
            let activeTab = 0;

            function updateMobileHighlight() {
                const width = 100 / mobileNavItems.length;
                mobileHighlight.style.width = `${width}%`;
                mobileHighlight.style.left = `${activeTab * width}%`;
            }
            updateMobileHighlight();

            mobileNavItems.forEach((item) => {
                item.addEventListener('click', function(e) {
                    if (this.getAttribute('href') === '{{ route('login') }}' ||
                        this.getAttribute('href') === '{{ route('logout') }}') {
                        return;
                    }
                    e.preventDefault();
                    mobileNavItems.forEach(i => i.classList.remove('active'));
                    item.classList.add('active');
                    activeTab = parseInt(item.dataset.pos);
                    updateMobileHighlight();
                    switch (activeTab) {
                        case 0:
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                            break;
                        case 1:
                            document.getElementById('trackingID')?.focus();
                            break;
                        case 2:
                            window.location.href = '{{ route('learn') }}';
                            break;
                        case 3:
                            break;
                    }
                });
            });
        })();

        // ── 3D Viewer ──
        document.getElementById('viewModelText')?.addEventListener('click', async () => {
            await Swal.fire({
                title: '<span style="color:white;">ZPPSU</span>',
                html: '<div id="three-container"></div>',
                width: 600,
                padding: '1em',
                background: 'linear-gradient(135deg, #800000, #ffcc00)',
                showCloseButton: true,
                showConfirmButton: false,
                didOpen: () => { init3DViewer(); }
            });
        });

        async function init3DViewer() {
            const THREE = await import('three');
            const { OrbitControls } = await import('three/addons/controls/OrbitControls.js');

            const container = document.getElementById('three-container');
            if (!container) return;
            const scene = new THREE.Scene();
            scene.background = null;

            const camera = new THREE.PerspectiveCamera(60, container.clientWidth / container.clientHeight, 0.1, 1000);
            camera.position.z = 5;

            const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
            renderer.setSize(container.clientWidth, container.clientHeight);
            renderer.setPixelRatio(window.devicePixelRatio);
            container.appendChild(renderer.domElement);

            const controls = new OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;

            scene.add(new THREE.AmbientLight(0xffffff, 0.8));
            const light = new THREE.DirectionalLight(0xffffff, 1);
            light.position.set(2, 2, 2);
            scene.add(light);

            const numParticles = 25000;
            const geo = new THREE.BufferGeometry();
            const pos = new Float32Array(numParticles * 3);
            const colors = new Float32Array(numParticles * 3);
            for (let i = 0; i < numParticles; i++) {
                const phi = Math.acos(-1 + (2 * i) / numParticles);
                const theta = Math.sqrt(numParticles * Math.PI) * phi;
                const idx = i * 3;
                pos[idx] = Math.sin(phi) * Math.cos(theta) * 1.5;
                pos[idx + 1] = Math.sin(phi) * Math.sin(theta) * 1.5;
                pos[idx + 2] = Math.cos(phi) * 1.5;
                const c = new THREE.Color(0xff5900);
                c.offsetHSL(0, 0, (Math.random() - 0.5) * 0.5);
                colors[idx] = c.r;
                colors[idx + 1] = c.g;
                colors[idx + 2] = c.b;
            }
            geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
            geo.setAttribute('color', new THREE.BufferAttribute(colors, 3));
            const particleMat = new THREE.PointsMaterial({
                size: 0.035,
                vertexColors: true,
                transparent: true,
                opacity: 0.9,
                sizeAttenuation: true,
                blending: THREE.AdditiveBlending
            });
            const particleSystem = new THREE.Points(geo, particleMat);
            scene.add(particleSystem);

            const textureLoader = new THREE.TextureLoader();
            textureLoader.load(
                'https://upload.wikimedia.org/wikipedia/en/8/8e/Zamboanga_Peninsula_Polytechnic_State_University_-_Emblem.png',
                (texture) => {
                    texture.encoding = THREE.sRGBEncoding;
                    texture.anisotropy = 16;
                    const planeGeo = new THREE.PlaneGeometry(2.5, 2.5);
                    const planeMat = new THREE.MeshStandardMaterial({
                        map: texture,
                        side: THREE.DoubleSide,
                        roughness: 0.4,
                        metalness: 0.2,
                        transparent: true,
                        alphaTest: 0.1
                    });
                    const logoPlane = new THREE.Mesh(planeGeo, planeMat);
                    scene.add(logoPlane);
                    function animate() {
                        requestAnimationFrame(animate);
                        particleSystem.rotation.y += 0.002;
                        logoPlane.rotation.y += 0.006;
                        controls.update();
                        renderer.render(scene, camera);
                    }
                    animate();
                }
            );

            window.addEventListener('resize', () => {
                if (!container) return;
                camera.aspect = container.clientWidth / container.clientHeight;
                camera.updateProjectionMatrix();
                renderer.setSize(container.clientWidth, container.clientHeight);
            });
        }

        // ── CHAT WIDGET ──
        function initChatWidget() {
            const chatLaunchers = document.querySelectorAll('.chat-launcher');
            const chatWrappers = document.querySelectorAll('.chat-wrapper');
            const chatPopup = document.getElementById('chatPopup');
            const chatClose = document.getElementById('chatClose');
            const chatMessages = document.getElementById('chatMessages');
            const chatForm = document.getElementById('chatForm');
            const chatInput = document.getElementById('chatInput');
            const chatBadges = document.querySelectorAll('.chat-badge');
            const tooltip = document.querySelector('.chat-wrapper-mobile .tooltip');
            if (!chatLaunchers.length || !chatPopup || !chatMessages || !chatForm || !chatInput) return;

            let isChatOpen = false;
            let isMobileView = window.innerWidth <= 768;
            let tooltipInterval;

            function showTooltip() {
                if (!isChatOpen && isMobileView && tooltip) {
                    tooltip.style.opacity = '1';
                    tooltip.style.transform = 'translateX(50%) translateY(-4px)';
                    setTimeout(() => {
                        tooltip.style.opacity = '0';
                        tooltip.style.transform = 'translateX(50%)';
                    }, 3000);
                }
            }

            function startTooltipInterval() {
                clearInterval(tooltipInterval);
                tooltipInterval = setInterval(showTooltip, 5000);
                setTimeout(showTooltip, 1000);
            }

            function setChatOpen(open) {
                isChatOpen = open;
                chatPopup.classList.toggle('visible', isChatOpen);
                chatPopup.setAttribute('aria-hidden', String(!isChatOpen));

                chatWrappers.forEach(wrapper => {
                    wrapper.classList.toggle('is-chat-open', isChatOpen);
                });

                chatLaunchers.forEach(launcher => {
                    launcher.setAttribute('aria-expanded', String(isChatOpen));
                });

                if (isChatOpen) {
                    chatBadges.forEach(badge => badge.style.display = 'none');
                    clearInterval(tooltipInterval);
                    setTimeout(() => chatInput.focus(), 100);
                } else {
                    startTooltipInterval();
                }
            }

            chatLaunchers.forEach(launcher => {
                launcher.addEventListener('click', function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    setChatOpen(!isChatOpen);
                });
            });

            if (chatClose) {
                chatClose.addEventListener('click', function() {
                    setChatOpen(false);
                });
            }

            window.addEventListener('resize', () => {
                isMobileView = window.innerWidth <= 768;
            });

            startTooltipInterval();

            function addMessage(sender, text) {
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${sender}`;
                const avatarDiv = document.createElement('div');
                avatarDiv.className = 'message-avatar';
                const avatarImg = document.createElement('img');
                avatarImg.style.width = '30px';
                avatarImg.style.height = '30px';
                
                if (sender === 'user') {
                    avatarImg.src = (window.isUserLoggedIn && window.userProfileAvatar) 
                        ? window.userProfileAvatar 
                        : 'https://cdn-icons-png.flaticon.com/512/8209/8209379.png';
                } else {
                    avatarImg.src = 'https://cdn-icons-png.flaticon.com/512/12109/12109541.png';
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
                    if (!chatPopup.classList.contains('visible')) {
                        chatBadges.forEach(b => b.style.display = 'flex');
                    }
                }

                const welcome = chatMessages.querySelector('.welcome-message');
                if (welcome && chatMessages.children.length > 1) welcome.remove();
                chatMessages.appendChild(messageDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            function updateLastMessage(sender, newText) {
                const msgs = chatMessages.getElementsByClassName(sender === 'user' ? 'user' : 'bot');
                const last = msgs[msgs.length - 1];
                if (last) {
                    const bubble = last.querySelector('.message-bubble');
                    if (bubble) {
                        bubble.textContent = newText;
                        const timeSpan = document.createElement('div');
                        timeSpan.className = 'message-time';
                        timeSpan.textContent = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        bubble.appendChild(timeSpan);
                    }
                }
            }

            async function getBotResponse(text) {
                try {
                    const res = await fetch('/chat/send', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ message: text })
                    });
                    const data = await res.json();
                    return data.response || 'Sorry, I wasn’t able to get a response from the server.';
                } catch {
                    return 'There was an issue connecting to the AI server.';
                }
            }

            chatForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const message = chatInput.value.trim();
                if (!message) return;
                addMessage('user', message);
                chatInput.value = '';
                addMessage('bot', 'Typing...');
                const reply = await getBotResponse(message);
                updateLastMessage('bot', reply);
            });

            setTimeout(() => {
                if (chatMessages.querySelector('.welcome-message')) {
                    addMessage('bot',
                        'I can help you with document tracking, system features, and more. What would you like to know?'
                    );
                }
            }, 1500);

            document.addEventListener('click', function(e) {
                if (isChatOpen &&
                    !chatPopup.contains(e.target) &&
                    !e.target.closest('.chat-launcher')) {
                    setChatOpen(false);
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initChatWidget);
        } else {
            initChatWidget();
        }
    </script>

    <script src="{{ asset('/sw.js') }}"></script>
    <script>
        if (!navigator.serviceWorker.controller) {
            navigator.serviceWorker.register("/sw.js").then(function(reg) {
                console.log("Service worker has been registered for scope: " + reg.scope);
            });
        }
    </script>
</body>
</html>