<style>
    /* Updated Footer Styles */
    .footer {
        width: 100%;
        padding: 40px 0 20px;
        background: linear-gradient(135deg, var(--univ-dark-maroon) 0%, var(--univ-gold) 100%);
        color: var(--univ-cream);
        font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        position: relative;
        overflow: hidden;
    }

    .footer,
    .footer * {
        box-sizing: border-box;
    }

    .footer * {
        font-family: inherit;
    }

    /* Use a footer-specific container so page-level .container rules cannot alter its layout. */
    .footer .footer-container {
        width: 100%;
        max-width: 1800px;
        margin: 0 auto;
        padding: 0 20px;
        position: relative;
        z-index: 2;
    }

    .footer-top {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-start;
        align-items: flex-start;
        gap: 2rem;
        padding-bottom: 20px;
        border-bottom: 1px solid rgba(248, 244, 233, 0.2);
        position: relative;
        z-index: 2;
    }

    .footer-col:first-child {
        flex: 1;
        min-width: 100px;
        position: relative;
        z-index: 2;
    }

    .footer-col {
        flex: 0 1 auto !important;
        min-width: 160px !important;
        text-align: left !important;
        position: relative !important;
        z-index: 2 !important;
    }

    /* ═══════════════════════════════════════════
       GROUP THAT HOLDS QUICK LINKS + SERVICES + CONTACT
       ═══════════════════════════════════════════ */
    .footer-links-group {
        display: flex !important;
        flex: 1 1 auto !important;
        gap: 3rem !important;
        margin-left: 0 !important;
        flex-wrap: wrap !important;
        justify-content: flex-start !important;
    }

    /* Image column remains on the far right */
    .footer-image-col {
        flex: 0 0 auto !important;
        margin-left: auto !important;
        margin-right: 24px !important;
        text-align: right !important;
    }

    .footer-image-col img {
        width: 120px !important;
        height: auto !important;
        border-radius: 8px !important;
        opacity: 0.95 !important;
        cursor: pointer !important;
    }

    .footer-logo-title {
        display: flex !important;
        align-items: center !important;
        margin-bottom: 12px !important;
    }

    .footer-logo-title img {
        width: 70px !important;
        height: 70px !important;
        margin-right: 15px !important;
    }

    .footer-logo-title h4 {
        margin: 0 !important;
        font-size: 22px !important;
        font-weight: 700 !important;
        color: var(--univ-cream) !important;
    }

    .footer-logo-subtitle {
        font-size: 16px !important;
        color: var(--univ-gold) !important;
        margin-bottom: 16px !important;
        font-weight: 500 !important;
    }

    .footer-description {
        font-size: 15px !important;
        color: rgba(248, 244, 233, 0.8) !important;
        max-width: 400px !important;
    }

    .footer-col h3 {
        font-weight: 700 !important;
        font-size: 16px !important;
        margin-bottom: 12px !important;
        color: var(--univ-cream) !important;
    }

    .footer-links,
    .footer-contact {
        list-style: none !important;
        padding: 0 !important;
        margin: 0 !important;
        color: rgba(248, 244, 233, 0.8) !important;
    }

    .footer-links li,
    .footer-contact li {
        margin-bottom: 10px !important;
        cursor: pointer !important;
        transition: color 0.25s ease !important;
    }

    .footer-links li:hover {
        color: var(--univ-maroon) !important;
    }

    .footer-contact li:hover,
    .footer-contact li:hover a,
    .footer-contact li a:hover {
        color: #05E527 !important;
    }

    .footer-contact li a {
        color: inherit !important;
        text-decoration: none !important;
    }

    .footer-contact li {
        font-size: 14px !important;
        white-space: pre-line !important;
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
    }

    .footer-contact li .contact-icon {
        width: 18px !important;
        height: 18px !important;
        display: inline-block !important;
        object-fit: contain !important;
    }

    .footer-contact a.footer-contact-link {
        color: var(--univ-cream) !important;
        text-decoration: none !important;
    }

    .footer-bottom {
        display: flex !important;
        flex-wrap: wrap !important;
        justify-content: space-between !important;
        margin-top: 24px !important;
        font-size: 13px !important;
        color: rgba(248, 244, 233, 0.6) !important;
        width: 100% !important;
    }

    .footer-bottom .copyright {
        flex: 1 1 300px !important;
    }

    .footer-bottom .policy-links {
        display: flex !important;
        gap: 20px !important;
        flex: 1 1 300px !important;
        justify-content: flex-end !important;
        flex-wrap: wrap !important;
        background: transparent !important;
        box-shadow: none !important;
        position: static !important;
        padding: 0 !important;
        top: auto !important;
        z-index: auto !important;
    }

    .footer-bottom .policy-links a {
        color: rgba(248, 244, 233, 0.6) !important;
        text-decoration: none !important;
        font-weight: 500 !important;
        transition: color 0.25s ease !important;
    }

    .footer-bottom .policy-links a:hover {
        color: var(--univ-gold) !important;
    }

    /* Security Image Modal Lightbox Styles */
    .sec-modal-overlay {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: rgba(0, 0, 0, 0.85) !important;
        backdrop-filter: blur(6px) !important;
        -webkit-backdrop-filter: blur(6px) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        z-index: 999999 !important;
        opacity: 0 !important;
        pointer-events: none !important;
        transition: opacity 0.3s ease !important;
    }

    .sec-modal-overlay.active {
        opacity: 1 !important;
        pointer-events: auto !important;
    }

    .sec-modal-content {
        position: relative !important;
        max-width: 90vw !important;
        max-height: 90vh !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        transform: scale(0.8) !important;
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
    }

    .sec-modal-overlay.active .sec-modal-content {
        transform: scale(1) !important;
    }

    .sec-modal-content img {
        max-width: 90vw !important;
        max-height: 85vh !important;
        width: auto !important;
        height: auto !important;
        object-fit: contain !important;
        border-radius: 12px !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7) !important;
        border: 2px solid rgba(255, 255, 255, 0.15) !important;
    }

    .sec-modal-close {
        position: absolute !important;
        top: -45px !important;
        right: 0 !important;
        background: rgba(255, 255, 255, 0.25) !important;
        color: #ffffff !important;
        border: none !important;
        font-size: 26px !important;
        width: 38px !important;
        height: 38px !important;
        border-radius: 50% !important;
        cursor: pointer !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        line-height: 1 !important;
        transition: background 0.2s ease, transform 0.2s ease !important;
    }

    .sec-modal-close:hover {
        background: rgba(255, 255, 255, 0.5) !important;
        transform: scale(1.1) !important;
    }

    @media (max-width: 900px) {
        .footer-top {
            flex-direction: column !important;
        }
        .footer-links-group {
            flex-direction: column !important;
            width: 100% !important;
            gap: 1.5rem !important;
        }
        .footer-image-col {
            margin-left: 0 !important;
            margin-right: 0 !important;
            text-align: center !important;
            width: 100% !important;
        }
        .footer-image-col img {
            margin: 0 auto !important;
        }
        .footer-bottom {
            justify-content: center !important;
            text-align: center !important;
        }
        .footer-bottom .policy-links {
            justify-content: center !important;
            margin-top: 8px !important;
        }
    }
</style>

<footer class="footer" aria-label="Site Footer">
    <div class="footer-container">
        <div class="footer-top">
            <!-- Logo & description -->
            <div class="footer-col">
                <div class="footer-logo-title">
                    <img src="{{ asset('assets/img/hd-logo.png') }}" alt="Zamboanga Peninsula Polytechnic State University official seal in red and gold with detailed emblematic design" />
                    <div>
                        <h4>DTS-ZPPSU</h4>
                        <div class="footer-logo-subtitle">Document Tracking System</div>
                    </div>
                </div>
                <p class="footer-description">
                    Streamlining document management for Zamboanga Peninsula Polytechnic State University with cutting edge technology and user friendly
                   <span id="viewModelText">interfaces. </span>
                </p>
            </div>

            <!-- Group container for Quick Links, Services, Contact – they sit side by side cleanly -->
            <div class="footer-links-group">
                <!-- Quick Links -->
                <div class="footer-col" aria-labelledby="quick-links-title">
                    <h3 id="quick-links-title">Quick Links</h3>
                    <ul class="footer-links" role="list">
                        <li tabindex="0">Dashboard</li>
                        <li tabindex="0">Track Document</li>
                        <li tabindex="0">Submit Request</li>
                        <li tabindex="0">Help Center</li>
                    </ul>
                </div>

                <!-- Services -->
                <div class="footer-col" aria-labelledby="services-title">
                    <h3 id="services-title">Services</h3>
                    <ul class="footer-links" role="list">
                        <li tabindex="0">Transcript of Records</li>
                        <li tabindex="0">Certificate of Enrollment</li>
                        <li tabindex="0">Diploma Copy</li>
                        <li tabindex="0">Official Documents</li>
                    </ul>
                </div>

                <!-- Contact -->
                <div class="footer-col" aria-labelledby="contact-title">
                    <h3 id="contact-title">Contact</h3>
                    <ul class="footer-contact" role="list">
                        <li tabindex="0"><img class="contact-icon" src="https://cdn-icons-png.flaticon.com/128/854/854878.png" alt="Address icon" aria-hidden="true"><span>R.T. Lim Boulevard, Baliwasan, Zamboanga City, 7000, Philippines</span></li>
                        <li tabindex="0"><img class="contact-icon" src="https://cdn-icons-png.flaticon.com/128/552/552489.png" alt="Phone icon" aria-hidden="true"><a class="footer-contact-link" href="tel:+63629753137">(062) 975-3137</a></li>
                        <li tabindex="0"><img class="contact-icon" src="https://cdn-icons-png.flaticon.com/128/15047/15047587.png" alt="Email icon" aria-hidden="true"><a class="footer-contact-link" href="mailto:zppsu@zppsu.edu.ph">zppsu@zppsu.edu.ph</a></li>
                        <li tabindex="0"><img class="contact-icon" src="https://cdn-icons-png.flaticon.com/128/3687/3687407.png" alt="Website icon" aria-hidden="true"><a class="footer-contact-link" href="https://www.zppsu.edu.ph" target="_blank" rel="noopener noreferrer">https://www.zppsu.edu.ph</a></li>
                    </ul>
                </div>
            </div>

            <!-- Image on the footer float left or right ikaw na bahala -->
            <div class="footer-image-col">
                <img src="{{ asset('/assets/img/sec.png') }}" alt="Security icon" style="float: left; margin-right: 151px; title="Click to enlarge view">
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

<!-- Lightbox Modal for Security Image -->
<div id="secImageModal" class="sec-modal-overlay" aria-hidden="true" role="dialog">
    <div class="sec-modal-content">
        <button type="button" class="sec-modal-close" aria-label="Close modal">&times;</button>
        <img src="{{ asset('/assets/img/sec.png') }}" alt="Security icon - Enlarged View">
    </div>
</div>

<script>
    (function() {
        function initSecModal() {
            const secImg = document.querySelector('.footer-image-col img');
            const secModal = document.getElementById('secImageModal');
            const secClose = document.querySelector('.sec-modal-close');

            if (secImg && secModal) {
                secImg.addEventListener('click', function(e) {
                    e.preventDefault();
                    secModal.classList.add('active');
                    secModal.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                });

                function closeModal() {
                    secModal.classList.remove('active');
                    secModal.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                }

                if (secClose) {
                    secClose.addEventListener('click', closeModal);
                }

                secModal.addEventListener('click', function(e) {
                    if (e.target === secModal) {
                        closeModal();
                    }
                });

                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && secModal.classList.contains('active')) {
                        closeModal();
                    }
                });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initSecModal);
        } else {
            initSecModal();
        }
    })();
</script>
