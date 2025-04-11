<?php
http_response_code(404);

?>

<style>
        :root {
        --primary: #2ecc71;
        --primary-dark: #27ae60;
        --background: #f9fafb;
        --text: #2c3e50;
        --shadow: rgba(0, 0, 0, 0.1);
    }
    .error-page {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 2rem;
    }

    .error-container {
        max-width: 100vw;
        text-align: center;
        padding: 3rem;
        background: white;
        border-radius: 24px;
        box-shadow: 
            0 20px 40px rgba(0, 0, 0, 0.05),
            0 1px 3px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }

    .error-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: linear-gradient(90deg, #2ecc71, #3498db);
    }

    .error-illustration {
        width: 400px;
        margin: 2rem auto;
        animation: float 6s ease-in-out infinite;
        filter: drop-shadow(0 10px 15px rgba(46, 204, 113, 0.2));
    }

    .error-code {
        font-size: 8rem;
        font-weight: 700;
        line-height: 1;
        margin: 0;
        background: linear-gradient(135deg, #2ecc71, #3498db);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.1);
    }

    .error-title {
        font-size: 2rem;
        color: #2c3e50;
        margin: 1.5rem 0;
    }

    .error-message {
        color: #64748b;
        font-size: 1.1rem;
        margin-bottom: 2rem;
        line-height: 1.6;
    }

    .back-home {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: #2ecc71;
        color: white;
        padding: 1rem 2rem;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 4px 15px rgba(46, 204, 113, 0.2);
    }

    .back-home:hover {
        transform: translateY(-2px);
        background: #27ae60;
        color: white;
        box-shadow: 0 6px 20px rgba(46, 204, 113, 0.3);
    }

    @keyframes float {
         0% {
            transform: translateY(0px) rotate(0deg);
        }
        50% {
            transform: translateY(-20px) rotate(2deg);
        }
        100% {
            transform: translateY(0px) rotate(0deg);
        }
    }

    @media (max-width: 768px) {
        .error-code {
            font-size: 6rem;
        }

        .error-title {
            font-size: 1.5rem;
        }

        .error-message {
            font-size: 1rem;
        }

        .error-container {
            padding: 2rem;
        }

        .error-illustration {
            width: 280px;
        }
    }
</style>

<div class="error-page">
    <div class="error-container">
        <h1 class="error-code">404</h1>
        <div class="error-illustration">
            <svg viewBox="0 0 800 600" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="gradient1" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" style="stop-color:#2ecc71"/>
                        <stop offset="100%" style="stop-color:#27ae60"/>
                    </linearGradient>
                    <linearGradient id="gradient2" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" style="stop-color:#3498db"/>
                        <stop offset="100%" style="stop-color:#2980b9"/>
                    </linearGradient>
                </defs>
                
                <!-- Background shapes -->
                <circle cx="400" cy="300" r="250" fill="#f8f9fa" opacity="0.5"/>
                <circle cx="400" cy="300" r="200" fill="#e9ecef" opacity="0.3"/>
                
                <!-- 4's -->
                <g transform="translate(200,150)">
                    <!-- First 4 -->
                    <path d="M50,0 L50,120 L0,120 L0,170 L50,170 L50,200 L100,200 L100,170 L120,170 L120,120 L100,120 L100,0 Z" 
                        fill="url(#gradient1)"/>
                    <path d="M0,120 L120,120 L120,170 L0,170 Z" 
                        fill="url(#gradient1)"/>
                </g>
                
                <!-- 0 -->
                <path d="M370,150 Q320,150 320,200 L320,300 Q320,350 370,350 L430,350 Q480,350 480,300 L480,200 Q480,150 430,150 Z
                        M370,200 L430,200 L430,300 L370,300 Z" 
                    fill="url(#gradient2)"/>
                
                <!-- Second 4 -->
                <g transform="translate(500,150)">
                    <path d="M50,0 L50,120 L0,120 L0,170 L50,170 L50,200 L100,200 L100,170 L120,170 L120,120 L100,120 L100,0 Z" 
                        fill="url(#gradient1)"/>
                    <path d="M0,120 L120,120 L120,170 L0,170 Z" 
                        fill="url(#gradient1)"/>
                </g>
                
                <!-- Decorative elements -->
                <circle cx="200" cy="100" r="20" fill="#2ecc71" opacity="0.2"/>
                <circle cx="600" cy="500" r="30" fill="#3498db" opacity="0.2"/>
                <circle cx="150" cy="450" r="15" fill="#2ecc71" opacity="0.2"/>
                <circle cx="650" cy="150" r="25" fill="#3498db" opacity="0.2"/>
            </svg>
        </div>
        <h2 class="error-title">Oops! Page Not Found</h2>
        <p class="error-message">
            The page you are looking for might have been removed, had its name changed, 
            or is temporarily unavailable.
        </p>
        <a href="index.php" class="back-home">
            <i class="fas fa-home"></i>
            Back to Home
        </a>
    </div>
</div>

<?php ?>