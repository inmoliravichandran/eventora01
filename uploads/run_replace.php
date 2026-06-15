<?php
$file_path = "index.php";
if (!file_exists($file_path)) {
    die("Error: index.php not found in current directory!");
}
$content = file_get_contents($file_path);

$new_style = <<<'EOD'
    <style>
        /* Define global animation settings and custom transition curve */
        :root {
            --transition-bounce: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Home page specific animations & styles */
        .hero-section {
            position: relative;
            overflow: hidden;
            animation: heroReveal 1.2s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        @keyframes heroReveal {
            0% {
                opacity: 0;
                transform: scale(0.97) translateY(10px);
            }
            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .floating-elements {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .floating-element {
            position: absolute;
            font-size: 2rem;
            opacity: 0.15;
            animation: floatAround 20s linear infinite;
        }

        .floating-element:nth-child(1) { top: 10%; left: 10%; animation-delay: 0s; }
        .floating-element:nth-child(2) { top: 20%; right: 15%; animation-delay: 5s; }
        .floating-element:nth-child(3) { bottom: 15%; left: 20%; animation-delay: 10s; }
        .floating-element:nth-child(4) { bottom: 25%; right: 10%; animation-delay: 15s; }

        @keyframes floatAround {
            0% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(20px, -20px) rotate(90deg); }
            50% { transform: translate(0, -40px) rotate(180deg); }
            75% { transform: translate(-20px, -20px) rotate(270deg); }
            100% { transform: translate(0, 0) rotate(360deg); }
        }

        .trust-banner {
            background: var(--glass-bg);
            backdrop-filter: var(--backdrop-blur);
            -webkit-backdrop-filter: var(--backdrop-blur);
            padding: 1.4rem;
            border-radius: 60px;
            margin: 2rem 0 4rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2.5rem;
            flex-wrap: wrap;
            border: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
        }

        .trust-item {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            color: var(--primary-dark);
            font-weight: 700;
            font-size: 0.95rem;
        }

        .trust-item i {
            color: var(--accent-gold);
            font-size: 1.25rem;
        }

        /* Call To Action Banner */
        .cta-banner {
            background: var(--gradient-gold) !important;
            padding: 5rem 2rem;
            border-radius: var(--border-radius);
            text-align: center;
            margin: 4.5rem 0;
            box-shadow: 0 15px 35px rgba(245, 158, 11, 0.25);
            border: 1px solid rgba(251, 191, 36, 0.3);
        }

        .cta-banner h2 {
            font-size: 2.8rem;
            font-weight: 800;
            margin-bottom: 1rem;
            color: var(--primary-dark);
            letter-spacing: -0.03em;
        }

        .cta-banner p {
            font-size: 1.2rem;
            color: rgba(15, 23, 42, 0.85);
            margin-bottom: 2.2rem;
            font-weight: 600;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.7;
        }

        /* Categories showcase grid */
        .category-showcase {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.8rem;
            margin: 3rem 0;
        }

        .category-card {
            background: var(--glass-bg) !important;
            backdrop-filter: var(--backdrop-blur) !important;
            -webkit-backdrop-filter: var(--backdrop-blur) !important;
            padding: 2.5rem 1.5rem;
            border-radius: var(--border-radius);
            text-align: center;
            box-shadow: var(--glass-shadow);
            transition: var(--transition-bounce);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            animation: cardAppear 0.6s cubic-bezier(0.165, 0.84, 0.44, 1) backwards;
            border: 1px solid var(--glass-border) !important;
        }

        .category-card:nth-child(1) { animation-delay: 0.1s; }
        .category-card:nth-child(2) { animation-delay: 0.2s; }
        .category-card:nth-child(3) { animation-delay: 0.3s; }
        .category-card:nth-child(4) { animation-delay: 0.4s; }

        .category-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-gold);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .category-card:hover::before {
            transform: scaleX(1);
        }

        .category-card:hover {
            transform: translateY(-8px) scale(1.02) !important;
            border-color: rgba(251, 191, 36, 0.4) !important;
            box-shadow: 0 15px 30px rgba(15, 23, 42, 0.08) !important;
        }

        .category-icon {
            width: 75px;
            height: 75px;
            background: rgba(251, 191, 36, 0.08);
            border: 1px solid rgba(251, 191, 36, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.2rem;
            transition: var(--transition-bounce);
        }

        .category-card:hover .category-icon {
            transform: rotate(360deg) scale(1.1);
            background: var(--gradient-gold);
        }

        .category-icon i {
            font-size: 2.2rem;
            color: var(--accent-gold);
            transition: var(--transition-bounce);
        }

        .category-card:hover .category-icon i {
            color: var(--primary-dark);
        }

        .category-card h3 {
            font-size: 1.35rem;
            margin-bottom: 0.6rem;
            color: var(--primary-dark);
            font-weight: 700;
        }

        .category-card p {
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .featured-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: var(--gradient-gold);
            color: var(--primary-dark);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(245,158,11,0.25);
        }

        /* Card Grid & Services Card styling overrides */
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2.5rem;
            width: 100%;
            margin-top: 2rem;
        }

        .card-grid .service-card {
            width: 100% !important;
            padding: 0 !important;
            background: var(--bg-card) !important;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            border: 1px solid var(--border-light);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            cursor: pointer;
        }

        .card-grid .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.12);
            border-color: rgba(251, 191, 36, 0.4);
        }

        .card-grid .service-card .card-image {
            position: relative;
            height: 220px;
            overflow: hidden;
        }

        .card-grid .service-card .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.8s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .card-grid .service-card:hover .card-image img {
            transform: scale(1.08);
        }

        .card-grid .service-card .badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--gradient-gold);
            color: var(--primary-dark);
            padding: 0.4rem 1.2rem;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            z-index: 2;
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);
        }

        .card-grid .service-card .card-content {
            padding: 1.8rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .card-grid .service-card .service-icon {
            font-size: 1.8rem;
            color: var(--accent-gold);
            margin-bottom: 1rem;
            background: rgba(251, 191, 36, 0.08);
            border: 1px solid rgba(251, 191, 36, 0.2);
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: var(--shadow-sm);
        }

        .card-grid .service-card:hover .service-icon {
            transform: scale(1.1) rotate(360deg);
            color: var(--primary-dark);
            background: var(--gradient-gold);
        }

        .card-grid .service-card h3 {
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: 0.6rem;
            color: var(--primary-dark);
            line-height: 1.3;
        }

        .card-grid .service-card p {
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1.2rem;
            flex-grow: 1;
        }

        .card-grid .service-card .price {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--accent-amber);
            margin-bottom: 1.2rem;
        }

        .card-grid .service-card .btn {
            margin-top: auto;
            width: 100%;
            text-align: center;
        }

        /* About & Statistics layout */
        .about-grid {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 4rem;
            align-items: center;
        }

        .about-content h2.section-title {
            margin-bottom: 1.5rem;
            display: inline-block;
        }

        .about-content p {
            color: var(--text-muted);
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 2.2rem;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }

        .stat-item {
            background: rgba(255, 255, 255, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.4);
            padding: 1.8rem 1rem;
            border-radius: var(--border-radius-sm);
            text-align: center;
            box-shadow: var(--shadow-sm);
            transition: var(--transition-bounce);
        }

        .stat-item:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.55);
            border-color: var(--accent-gold);
            box-shadow: var(--shadow-md);
        }

        .stat-number {
            font-family: 'Outfit', sans-serif;
            font-size: 2.4rem;
            font-weight: 800;
            color: var(--accent-amber);
            line-height: 1.2;
            margin-bottom: 0.4rem;
        }

        .stat-label {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--primary-dark);
        }

        .about-image img {
            width: 100%;
            border-radius: var(--border-radius);
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow-lg);
            object-fit: cover;
            height: 380px;
            transition: transform 0.5s ease;
        }

        .about-image:hover img {
            transform: scale(1.02);
        }

        /* Video Showcase */
        .video-showcase {
            margin: 4.5rem 0;
            position: relative;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--glass-border);
        }

        .video-showcase video {
            width: 100%;
            height: 450px;
            object-fit: cover;
            display: block;
        }

        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(15,23,42,0.8), rgba(30,41,59,0.8));
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: white;
            text-align: center;
            padding: 2.5rem;
        }

        .video-overlay h2 {
            color: white;
            font-size: 2.8rem;
            margin-bottom: 1rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .video-overlay p {
            color: #cbd5e1;
            font-size: 1.25rem;
            max-width: 600px;
            margin-bottom: 2.2rem;
            line-height: 1.7;
        }

        .play-button {
            width: 85px;
            height: 85px;
            background: var(--gradient-gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            color: var(--primary-dark);
            cursor: pointer;
            animation: pulse 2s ease-in-out infinite;
            transition: var(--transition-bounce);
            box-shadow: 0 4px 15px rgba(245,158,11,0.3);
        }

        .play-button:hover {
            transform: scale(1.15);
            background: white;
            color: var(--primary-dark);
        }

        /* Instagram Feed */
        .instagram-feed {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin: 3rem 0;
        }

        .insta-post {
            position: relative;
            border-radius: var(--border-radius-sm);
            overflow: hidden;
            aspect-ratio: 1;
            cursor: pointer;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--glass-border);
        }

        .insta-post img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .insta-post:hover img {
            transform: scale(1.08);
        }

        .insta-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(251,191,36,0.9), rgba(245,158,11,0.9));
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
            color: var(--primary-dark);
            font-size: 1.3rem;
            font-weight: 700;
            gap: 1.5rem;
        }

        .insta-post:hover .insta-overlay {
            opacity: 1;
        }

        .insta-overlay span {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        /* Newsletter Section */
        .newsletter-section {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.95)), url('https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=1200');
            background-size: cover;
            background-position: center;
            backdrop-filter: var(--backdrop-blur);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 5rem 2rem;
            border-radius: var(--border-radius);
            margin: 4.5rem 0;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .newsletter-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(251,191,36,0.05) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
        }

        .newsletter-section h2 {
            color: white;
            font-size: 2.6rem;
            font-weight: 800;
            margin-bottom: 1rem;
            position: relative;
            z-index: 2;
            letter-spacing: -0.02em;
        }

        .newsletter-section p {
            color: #cbd5e1;
            margin-bottom: 2.2rem;
            font-size: 1.15rem;
            position: relative;
            z-index: 2;
        }

        .newsletter-form {
            display: flex;
            max-width: 550px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            border-radius: 60px;
            overflow: hidden;
        }

        .newsletter-form input {
            flex: 1;
            padding: 1.1rem 1.8rem;
            border: none;
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.92);
            color: var(--primary-dark);
            outline: none;
        }

        .newsletter-form button {
            padding: 1.1rem 2.2rem;
            border: none;
            background: var(--gradient-gold);
            color: var(--primary-dark);
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }

        .newsletter-form button:hover {
            background: white;
            color: var(--primary-dark);
        }

        /* Partner Section */
        .partner-section {
            margin: 4.5rem 0;
            text-align: center;
        }

        .partner-logos {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .partner-logo {
            width: 140px;
            height: 90px;
            background: var(--glass-bg);
            backdrop-filter: var(--backdrop-blur);
            border-radius: var(--border-radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--primary-dark);
            box-shadow: var(--glass-shadow);
            transition: all 0.3s ease;
            border: 1px solid var(--glass-border);
            font-size: 0.95rem;
        }

        .partner-logo:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(15,23,42,0.08);
            border-color: rgba(251, 191, 36, 0.4);
        }

        /* Why Choose Us Grid */
        .why-choose-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin: 3rem 0;
        }

        .why-card {
            background: var(--glass-bg) !important;
            backdrop-filter: var(--backdrop-blur) !important;
            -webkit-backdrop-filter: var(--backdrop-blur) !important;
            padding: 2.8rem 2.2rem;
            border-radius: var(--border-radius);
            text-align: center;
            box-shadow: var(--glass-shadow);
            border: 1px solid var(--glass-border) !important;
            transition: var(--transition-bounce);
            position: relative;
            overflow: hidden;
        }

        .why-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-gold);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .why-card:hover::after {
            transform: scaleX(1);
        }

        .why-card:hover {
            transform: translateY(-8px) !important;
            border-color: rgba(251, 191, 36, 0.4) !important;
            box-shadow: 0 15px 30px rgba(15, 23, 42, 0.08) !important;
        }

        .why-icon {
            width: 80px;
            height: 80px;
            background: rgba(251, 191, 36, 0.08);
            border: 1px solid rgba(251, 191, 36, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            transition: var(--transition-bounce);
        }

        .why-card:hover .why-icon {
            transform: rotate(360deg);
            background: var(--gradient-gold);
        }

        .why-icon i {
            font-size: 2.4rem;
            color: var(--accent-gold);
            transition: var(--transition-bounce);
        }

        .why-card:hover .why-icon i {
            color: var(--primary-dark);
        }

        .why-card h3 {
            font-size: 1.4rem;
            margin-bottom: 0.8rem;
            font-weight: 700;
            color: var(--primary-dark);
        }

        .why-card p {
            color: var(--text-muted);
            line-height: 1.7;
            font-size: 0.95rem;
        }

        /* Responsive breakpoints */
        @media (max-width: 1024px) {
            .category-showcase {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .why-choose-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .instagram-feed {
                grid-template-columns: repeat(2, 1fr);
            }

            .about-grid {
                grid-template-columns: 1fr;
                gap: 3rem;
            }

            .about-image {
                order: -1;
            }

            .about-image img {
                height: 300px;
            }
        }

        @media (max-width: 768px) {
            .category-showcase {
                grid-template-columns: 1fr;
            }
            
            .why-choose-grid {
                grid-template-columns: 1fr;
            }
            
            .instagram-feed {
                grid-template-columns: 1fr;
            }
            
            .trust-banner {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
                border-radius: 30px;
            }
            
            .newsletter-form {
                flex-direction: column;
                gap: 1rem;
                background: none;
                box-shadow: none;
                border-radius: 0;
            }
            
            .newsletter-form input,
            .newsletter-form button {
                border-radius: 60px;
                width: 100%;
                justify-content: center;
            }

            .video-showcase video {
                height: 300px;
            }

            .video-overlay h2 {
                font-size: 2rem;
            }

            .video-overlay p {
                font-size: 1rem;
            }

            .play-button {
                width: 70px;
                height: 70px;
                font-size: 1.8rem;
            }

            .stats-container {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }
    </style>
EOD;

$pattern = '/<style>.*?<\/style>/s';
if (preg_match($pattern, $content)) {
    $content = preg_replace($pattern, $new_style, $content);
    if (file_put_contents($file_path, $content)) {
        echo "Success: CSS block replaced successfully!";
    } else {
        echo "Error: Failed to write to index.php";
    }
} else {
    echo "Error: Could not find <style> block in index.php";
}
?>
