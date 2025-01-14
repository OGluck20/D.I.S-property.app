<style>
    :root {
        --footer-bg: #e8f5e9; /* Very light green background */
    }

    footer {
        background: var(--footer-bg);
        padding: 3rem 0;
        border-top: 1px solid var(--shadow);
        margin-top: auto;
    }

    .footer-container {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
        padding: 0 1rem;
    }

    .footer-section h3 {
        color: var(--primary);
        margin-bottom: 1.5rem;
        font-size: 1.2rem;
        font-weight: 600;
    }

    .footer-section p {
        color: var(--text);
        line-height: 1.6;
        margin-bottom: 1rem;
    }

    .social-links {
        display: flex;
        gap: 1rem;
        margin-top: 1rem;
    }

    .social-links a {
        color: var(--primary);
        font-size: 1.5rem;
        transition: transform 0.3s ease;
    }

    .social-links a:hover {
        transform: translateY(-3px);
    }

    .quick-links {
        list-style: none;
        padding: 0;
    }

    .quick-links li {
        margin-bottom: 0.5rem;
    }

    .quick-links a {
        color: var(--text);
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .quick-links a:hover {
        color: var(--primary);
    }

    .contact-info {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .contact-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text);
    }

    .copyright {
        background: #dcedc8; /* Slightly darker shade for copyright section */
        padding: 1rem 0;
        margin-top: 2rem;
    }

    @media (max-width: 768px) {
        .footer-container {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .social-links {
            justify-content: center;
        }

        .contact-item {
            justify-content: center;
        }
    }
</style>

<footer>
    <div class="footer-container">
        <!-- Company Info -->
        <div class="footer-section">
            <h3>About D.I.S</h3>
            <p>Innovative solutions for Properties, Technology, and Agriculture.</p>
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-linkedin"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul class="quick-links">
                <li><a href="#">Home</a></li>
                <li><a href="#">About Us</a></li>
                <li><a href="#">Services</a></li>
                <li><a href="#">Properties</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </div>

        <!-- Contact Info -->
        <div class="footer-section">
            <h3>Contact Us</h3>
            <div class="contact-info">
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>123 Business Street, City, Country</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <span>+1 234 567 890</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <span>info@dis-group.com</span>
                </div>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
