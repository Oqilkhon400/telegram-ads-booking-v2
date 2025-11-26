<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kosonsoy Telegram Reklama | @kosonsoy</title>
    <meta name="description" content="Kosonsoy telegram kanalida reklama berish - eng samarali reklama platformasi">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0088cc;
            --secondary: #00a6db;
            --dark: #1a1a2e;
            --light: #f8f9fa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 100px 0 80px;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,138.7C960,139,1056,117,1152,106.7C1248,96,1344,96,1392,96L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
            opacity: 0.3;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .hero-subtitle {
            font-size: 1.5rem;
            margin-bottom: 30px;
            opacity: 0.95;
        }

        .telegram-badge {
            display: inline-flex;
            align-items: center;
            background: white;
            color: var(--primary);
            padding: 15px 30px;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: all 0.3s;
        }

        .telegram-badge:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
            color: var(--secondary);
        }

        .telegram-badge i {
            font-size: 1.5rem;
            margin-right: 10px;
        }

        /* Stats Section */
        .stats-section {
            background: white;
            padding: 60px 0;
            margin-top: -50px;
            position: relative;
            z-index: 2;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* Features Section */
        .features-section {
            padding: 80px 0;
            background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 100%);
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 50px;
            color: var(--dark);
        }

        .feature-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s;
            height: 100%;
            border: 2px solid transparent;
        }

        .feature-card:hover {
            border-color: var(--primary);
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            color: white;
        }

        .feature-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--dark);
        }

        .feature-desc {
            color: #666;
            line-height: 1.6;
        }

        /* Pricing Section */
        .pricing-section {
            padding: 80px 0;
            background: white;
        }

        .price-card {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s;
            height: 100%;
        }

        .price-card:hover {
            border-color: var(--primary);
            transform: scale(1.05);
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        }

        .price-card.featured {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border: none;
            transform: scale(1.08);
        }

        .price-badge {
            background: rgba(255,255,255,0.2);
            padding: 8px 20px;
            border-radius: 30px;
            display: inline-block;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .price-amount {
            font-size: 3rem;
            font-weight: 800;
            margin: 20px 0;
        }

        .price-period {
            font-size: 1.1rem;
            opacity: 0.8;
            margin-bottom: 30px;
        }

        .price-features {
            list-style: none;
            padding: 0;
            margin: 30px 0;
        }

        .price-features li {
            padding: 12px 0;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }

        .price-card.featured .price-features li {
            border-bottom-color: rgba(255,255,255,0.2);
        }

        .price-features li i {
            color: #4CAF50;
            margin-right: 10px;
        }

        .price-card.featured .price-features li i {
            color: #ffffff;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }

        .cta-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .cta-buttons .btn {
            margin: 10px;
            padding: 15px 40px;
            font-size: 1.1rem;
            border-radius: 50px;
            font-weight: 600;
        }

        .btn-light-custom {
            background: white;
            color: var(--primary);
            border: none;
        }

        .btn-light-custom:hover {
            background: #f0f0f0;
            transform: translateY(-3px);
        }

        .btn-outline-light-custom {
            border: 2px solid white;
            color: white;
            background: transparent;
        }

        .btn-outline-light-custom:hover {
            background: white;
            color: var(--primary);
        }

        /* Footer */
        .footer {
            background: var(--dark);
            color: white;
            padding: 40px 0 20px;
        }

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.3s;
        }

        .footer-links a:hover {
            color: white;
            padding-left: 5px;
        }

        .social-links a {
            color: white;
            font-size: 1.5rem;
            margin: 0 10px;
            transition: all 0.3s;
        }

        .social-links a:hover {
            color: var(--primary);
            transform: translateY(-3px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }
            .hero-subtitle {
                font-size: 1.1rem;
            }
            .stat-number {
                font-size: 2rem;
            }
            .section-title {
                font-size: 1.8rem;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeInUp 0.8s ease-out;
        }
    </style>
</head>
<body>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content animate-fade-in">
                    <h1 class="hero-title">Kosonsoy Telegram Kanalida Reklama</h1>
                    <p class="hero-subtitle">
                        <i class="fas fa-users"></i> 50,000+ faol a'zolar
                        <br>
                        Mahalliy biznesingizni kengaytiring!
                    </p>
                    <a href="https://t.me/kosonsoy" target="_blank" class="telegram-badge">
                        <i class="fab fa-telegram"></i>
                        @kosonsoy kanaliga qo'shiling
                    </a>
                </div>
                <div class="col-lg-6 text-center animate-fade-in">
                    <i class="fab fa-telegram" style="font-size: 15rem; opacity: 0.2;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-number">50K+</div>
                        <div class="stat-label">Faol Obunachilar</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <div class="stat-number">10K+</div>
                        <div class="stat-label">Kunlik Ko'rishlar</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <div class="stat-number">95%</div>
                        <div class="stat-label">Faollik Darajasi</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <div class="stat-number">500+</div>
                        <div class="stat-label">Mamnun Mijozlar</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <h2 class="section-title">Nima Uchun Bizni Tanlaysiz?</h2>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <h3 class="feature-title">Keng Auditoriya</h3>
                        <p class="feature-desc">50,000+ dan ortiq faol obunachilar - Kosonsoy va atrofdagi eng yirik telegram kanal</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="feature-title">Yuqori Konversiya</h3>
                        <p class="feature-desc">95% faollik darajasi - sizning reklamangiz ko'pchilik tomonidan ko'riladi</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <h3 class="feature-title">Arzon Narxlar</h3>
                        <p class="feature-desc">Har qanday budjetga mos paketlar - kichik biznesdan tortib yirik kompaniyalargacha</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3 class="feature-title">Tez Jarayon</h3>
                        <p class="feature-desc">Buyurtmadan keyin 24 soat ichida sizning reklamangiz kanalda chiqadi</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3 class="feature-title">24/7 Qo'llab-quvvatlash</h3>
                        <p class="feature-desc">Har qanday savol yoki muammolar bo'yicha biz har doim yordam berishga tayyormiz</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <h3 class="feature-title">Ishonchli Hamkor</h3>
                        <p class="feature-desc">500+ dan ortiq mamnun mijozlar - biz bilan ishlash oson va ishonchli</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing-section">
        <div class="container">
            <h2 class="section-title">Reklama Paketlari</h2>
            <div class="row g-4 justify-content-center">
                <div class="col-lg-4 col-md-6">
                    <div class="price-card">
                        <h3>Boshlang'ich</h3>
                        <div class="price-amount">150,000</div>
                        <div class="price-period">so'm / 5 ta post</div>
                        <ul class="price-features">
                            <li><i class="fas fa-check"></i> 5 ta reklama post</li>
                            <li><i class="fas fa-check"></i> Oddiy matn va rasm</li>
                            <li><i class="fas fa-check"></i> 1 oy davomida</li>
                            <li><i class="fas fa-check"></i> Telegram qo'llab-quvvatlash</li>
                        </ul>
                        <a href="https://t.me/kosonsoy_admin" target="_blank" class="btn btn-outline-primary btn-lg w-100">Buyurtma berish</a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="price-card featured">
                        <div class="price-badge">⭐ ENG MASHHUR</div>
                        <h3>Biznes</h3>
                        <div class="price-amount">250,000</div>
                        <div class="price-period">so'm / 10 ta post</div>
                        <ul class="price-features">
                            <li><i class="fas fa-check"></i> 10 ta reklama post</li>
                            <li><i class="fas fa-check"></i> Video va rasm</li>
                            <li><i class="fas fa-check"></i> 2 oy davomida</li>
                            <li><i class="fas fa-check"></i> Bepul konsultatsiya</li>
                            <li><i class="fas fa-check"></i> Statistika hisoboti</li>
                        </ul>
                        <a href="https://t.me/kosonsoy_admin" target="_blank" class="btn btn-light btn-lg w-100">Buyurtma berish</a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="price-card">
                        <h3>Premium</h3>
                        <div class="price-amount">400,000</div>
                        <div class="price-period">so'm / 20 ta post</div>
                        <ul class="price-features">
                            <li><i class="fas fa-check"></i> 20 ta reklama post</li>
                            <li><i class="fas fa-check"></i> Video, rasm va animatsiya</li>
                            <li><i class="fas fa-check"></i> 3 oy davomida</li>
                            <li><i class="fas fa-check"></i> Shaxsiy menejer</li>
                            <li><i class="fas fa-check"></i> Batafsil hisobot</li>
                            <li><i class="fas fa-check"></i> Maxsus vaqt tanlov</li>
                        </ul>
                        <a href="https://t.me/kosonsoy_admin" target="_blank" class="btn btn-outline-primary btn-lg w-100">Buyurtma berish</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2 class="cta-title">Biznesingizni Bugun Boshlang!</h2>
            <p class="lead mb-4">Reklamangizni 50,000+ odamga yetkazing</p>
            <div class="cta-buttons">
                <a href="https://t.me/kosonsoy_admin" target="_blank" class="btn btn-light-custom btn-lg">
                    <i class="fab fa-telegram"></i> Buyurtma berish
                </a>
                <a href="frontend/login.php" class="btn btn-outline-light-custom btn-lg">
                    <i class="fas fa-sign-in-alt"></i> Admin Panel
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h4 class="mb-3">Kosonsoy Reklama</h4>
                    <p>Telegram'dagi eng yirik mahalliy kanal. Biznesingizni kengaytiring!</p>
                    <div class="social-links mt-3">
                        <a href="https://t.me/kosonsoy" target="_blank"><i class="fab fa-telegram"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-facebook"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5 class="mb-3">Foydali Havolalar</h5>
                    <ul class="footer-links">
                        <li><a href="#features">Xususiyatlar</a></li>
                        <li><a href="#pricing">Narxlar</a></li>
                        <li><a href="https://t.me/kosonsoy" target="_blank">Kanal</a></li>
                        <li><a href="frontend/login.php">Admin Panel</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5 class="mb-3">Bog'lanish</h5>
                    <ul class="footer-links">
                        <li><i class="fab fa-telegram"></i> <a href="https://t.me/kosonsoy_admin" target="_blank">@kosonsoy_admin</a></li>
                        <li><i class="fas fa-envelope"></i> info@kosonsoyliklar.uz</li>
                        <li><i class="fas fa-map-marker-alt"></i> Kosonsoy, Namangan viloyati</li>
                    </ul>
                </div>
            </div>
            <hr style="border-color: rgba(255,255,255,0.1); margin: 30px 0;">
            <div class="text-center">
                <p class="mb-0">&copy; 2025 Kosonsoy Reklama. Barcha huquqlar himoyalangan.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
