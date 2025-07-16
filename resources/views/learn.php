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
            --primary: #0056b3;
            --secondary: #003366;
            --accent: #00a8e8;
            --light: #f8f9fa;
            --dark: #343a40;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            color: var(--dark);
            line-height: 1.6;
        }

        /* ===== Header ===== */
        header {
            background: linear-gradient(to right, var(--secondary), var(--primary));
            color: white;
            padding: 2rem 0;
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
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            position: relative;
            animation: fadeInDown 1s ease;
        }

        header p {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            animation: fadeInUp 1s ease 0.3s both;
        }

        /* ===== Main Content ===== */
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .feature-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
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
            font-size: 1.3rem;
        }

        .feature-card p {
            color: #555;
        }

        /* ===== Tracking Section ===== */
        .tracker {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
            animation: fadeIn 1s ease 0.6s both;
        }

        .tracker h2 {
            color: var(--secondary);
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
        }

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
            transition: var(--transition);
        }

        #trackingID:focus {
            border-color: var(--accent);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 168, 232, 0.2);
        }

        #trackForm button {
            background: linear-gradient(to right, var(--primary), var(--accent));
            color: white;
            border: none;
            padding: 0 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        #trackForm button:hover {
            background: linear-gradient(to right, var(--secondary), var(--primary));
            transform: translateY(-2px);
        }

        #trackResult {
            margin-top: 1.5rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid var(--accent);
            text-align: left;
            display: none;
            animation: fadeIn 0.5s ease;
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
            color: var(--accent);
        }

        /* ===== Footer ===== */
        footer {
            background: var(--dark);
            color: white;
            text-align: center;
            padding: 1.5rem;
            margin-top: 3rem;
        }

        footer a {
            color: var(--accent);
            text-decoration: none;
            transition: var(--transition);
        }

        footer a:hover {
            text-decoration: underline;
        }

        /* ===== Animations ===== */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
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

        /* ===== Responsive ===== */
        @media (max-width: 768px) {
            .features {
                grid-template-columns: 1fr;
            }

            #trackForm {
                flex-direction: column;
            }

            #trackForm button {
                justify-content: center;
                padding: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <h1><i class="fas fa-file-alt"></i> DTS-ZPPSU</h1>
        <p>Zamboanga Peninsula Polytechnic State University</p>
    </header>

    <!-- Main Content -->
    <div class="container">
        <!-- Features Section -->
        <section class="features">
            <div class="feature-card">
                <h3><i class="fas fa-paper-plane"></i> Easy Document Requests</h3>
                <p>Submit and track requests (e.g., academic records) with real-time updates.</p>
            </div>
            <div class="feature-card">
                <h3><i class="fas fa-bolt"></i> Quick Processing</h3>
                <p>Faster turnaround times for document requests.</p>
            </div>
            <div class="feature-card">
                <h3><i class="fas fa-user-friends"></i> User-Friendly Interface</h3>
                <p>Intuitive design for easy navigation.</p>
            </div>
            <div class="feature-card">
                <h3><i class="fas fa-lock"></i> Security & Confidentiality</h3>
                <p>Robust protection for sensitive data.</p>
            </div>
            <div class="feature-card">
                <h3><i class="fas fa-database"></i> Centralized Management</h3>
                <p>Automated workflows and audit trails.</p>
            </div>
        </section>

        <!-- Document Tracker -->
        <section class="tracker">
            <h2><i class="fas fa-search"></i> Track Your Document</h2>
            <form id="trackForm">
                <input type="text" id="trackingID" placeholder="Enter Tracking ID (e.g., DTS-2023-001)" required>
                <button type="submit"><i class="fas fa-search"></i> Track</button>
            </form>
            <div id="trackResult"></div>
        </section>
    </div>

    <!-- Footer -->
    <footer>
        <p>© 2023 DTS-ZPPSU | Contact: <a href="mailto:support@zppsu.edu.ph">support@zppsu.edu.ph</a></p>
    </footer>

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const trackForm = document.getElementById('trackForm');
            const trackingID = document.getElementById('trackingID');
            const trackResult = document.getElementById('trackResult');

            // Sample tracking data (replace with API calls in a real system)
            const sampleData = {
                "DTS-2023-001": {
                    status: "Approved",
                    document: "Transcript of Records",
                    date: "2023-10-15",
                    estimated: "Ready for pickup"
                },
                "DTS-2023-002": {
                    status: "Processing",
                    document: "Certificate of Enrollment",
                    date: "2023-10-18",
                    estimated: "3 business days"
                },
                "DTS-2023-003": {
                    status: "Pending",
                    document: "Diploma Copy",
                    date: "2023-10-20",
                    estimated: "Under review"
                }
            };

            // Form submission
            trackForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const id = trackingID.value.trim();

                if (!id) {
                    showResult("Please enter a valid Tracking ID.", "error");
                    return;
                }

                // Simulate API delay
                trackResult.innerHTML = '<div class="status-item"><i class="fas fa-spinner fa-spin status-icon"></i> Searching...</div>';
                trackResult.style.display = "block";

                setTimeout(() => {
                    if (sampleData[id]) {
                        const data = sampleData[id];
                        let statusColor = "#28a745"; // Green for Approved
                        if (data.status === "Processing") statusColor = "#ffc107"; // Yellow
                        if (data.status === "Pending") statusColor = "#dc3545"; // Red

                        trackResult.innerHTML = `
                            <div class="status-item">
                                <i class="fas fa-file-alt status-icon" style="color: ${statusColor}"></i>
                                <strong>Document:</strong> ${data.document}
                            </div>
                            <div class="status-item">
                                <i class="fas fa-calendar-check status-icon" style="color: ${statusColor}"></i>
                                <strong>Request Date:</strong> ${data.date}
                            </div>
                            <div class="status-item">
                                <i class="fas fa-tasks status-icon" style="color: ${statusColor}"></i>
                                <strong>Status:</strong> <span style="color: ${statusColor}">${data.status}</span>
                            </div>
                            <div class="status-item">
                                <i class="fas fa-clock status-icon" style="color: ${statusColor}"></i>
                                <strong>Estimated:</strong> ${data.estimated}
                            </div>
                        `;
                    } else {
                        showResult("No record found for this Tracking ID. Please verify and try again.", "error");
                    }
                }, 1500);
            });

            // Helper function to show messages
            function showResult(message, type) {
                trackResult.innerHTML = `<div class="status-item"><i class="fas fa-exclamation-circle status-icon" style="color: ${type === 'error' ? '#dc3545' : '#28a745'}"></i> ${message}</div>`;
                trackResult.style.display = "block";
            }
        });
    </script>
</body>
</html>