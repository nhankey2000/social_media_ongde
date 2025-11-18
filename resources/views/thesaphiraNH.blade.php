<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>NHÀ HÀNG HỒ BƠI - SAPHIRE VIP CARD</title>
    <link rel="icon" href="images/favicon.ico" type="image/x-icon">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(-45deg, #1e1e1e, #2d1b0a, #3f2b16, #5c3f1f, #8B6914, #b8860b);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: #ffffff;
            line-height: 1.6;
            position: relative;
            overflow-x: hidden;
        }
        @keyframes gradientShift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        body::before {
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background-image: radial-gradient(circle at 20% 80%, rgba(255,255,255,0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(255,255,255,0.08) 0%, transparent 50%),
            radial-gradient(circle at 40% 40%, rgba(255,255,255,0.06) 0%, transparent 50%);
            animation: floatParticles 12s ease-in-out infinite; pointer-events: none;
        }
        @keyframes floatParticles { 0%, 100% { transform: translateY(0) rotate(0deg); opacity: 0.7; } 50% { transform: translateY(-20px) rotate(120deg); opacity: 1; } }
        body::after {
            content: ''; position: absolute; top: 50%; left: 50%; width: 200px; height: 200px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.03) 0%, transparent 70%);
            border-radius: 50%; transform: translate(-50%, -50%); animation: pulse 4s ease-in-out infinite; pointer-events: none;
        }
        @keyframes pulse { 0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.5; } 50% { transform: translate(-50%, -50%) scale(2); opacity: 0.2; } }
        .container { max-width: 800px; width: 100%; text-align: center; animation: fadeInUp 0.8s ease-out; position: relative; z-index: 10; }
        .container p { color: white; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
        .logo-section { margin-bottom: 24px; animation: fadeIn 1s ease-out 0.2s both; }
        .logo { width: 200px; height: 120px; margin: 0 auto 24px; border-radius: 24px; background: #ffffff; display: flex; align-items: center; justify-content: center; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1); transition: transform 0.3s ease; }
        .logo:hover { transform: translateY(-4px); }
        .logo img { width: 160px; height: 80px; border-radius: 16px; }
        .main-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 16px;
            letter-spacing: -0.02em;
            background: linear-gradient(45deg, #0f3460, #4169e1, #1e90ff, #00bfff, #0f3460, #4169e1);
            background-size: 400% 400%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: fadeIn 1s ease-out 0.4s both, gradientText 8s ease infinite;
            text-shadow: 0 4px 15px rgba(65, 105, 225, 0.4);
        }
        @keyframes gradientText { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .subtitle { font-size: 1.25rem; color: white; margin-bottom: 24px; font-weight: 400; animation: fadeIn 1s ease-out 0.5s both; }
        .description { font-size: 1.1rem; color: white; margin-bottom: 48px; font-weight: 300; animation: fadeIn 1s ease-out 0.6s both; max-width: 600px; margin-left: auto; margin-right: auto; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3); }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .social-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px; margin-bottom: 48px; }
        .social-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border: none; border-radius: 16px; padding: 32px 24px; text-decoration: none; color: inherit; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); position: relative; overflow: hidden; animation: fadeInUp 0.8s ease-out both; cursor: pointer; }
        .social-card:nth-child(1) { animation-delay: 0.6s; }
        .social-card:nth-child(2) { animation-delay: 0.8s; }
        .social-card:nth-child(3) { animation-delay: 1s; }
        .social-card:nth-child(4) { animation-delay: 1.2s; }
        .social-card:nth-child(5) { animation-delay: 1.4s; }
        .social-card:nth-child(6) { animation-delay: 1.6s; }
        .social-card::before { content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 2px; background: linear-gradient(90deg, transparent, #1a1a1a, #2d3748, transparent); transition: left 0.8s ease; }
        .social-card:hover::before { left: 100%; }
        .social-card:hover { transform: translateY(-12px) scale(1.02); box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15); background: rgba(255, 255, 255, 0.98); }
        .social-icon { width: 48px; height: 48px; margin: 0 auto 16px; border-radius: 12px; display: flex; align-items: center; justify-content: center; transition: all 0.4s ease; background: transparent; }
        .social-card:hover .social-icon { transform: scale(1.2) rotateY(360deg); filter: drop-shadow(0 8px 16px rgba(0, 0, 0, 0.2)); }
        .social-icon::before { content: ''; position: absolute; top: -4px; left: -4px; right: -4px; bottom: -4px; border-radius: 16px; background: linear-gradient(45deg, #ff6b35, #f7931e, #ffd700, #32cd32, #ff8c00, #ff6b35); background-size: 300% 300%; animation: rainbowRotate 3s linear infinite; opacity: 0; transition: opacity 0.4s ease; z-index: -1; }
        .social-card:hover .social-icon::before { opacity: 0.7; }
        @keyframes rainbowRotate { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .social-icon img { width: 48px; height: 48px; border-radius: 12px; }
        .social-title { font-size: 1.125rem; font-weight: 600; color: #1f2937; margin-bottom: 8px; }
        .social-description { font-size: 0.875rem; color: #6b7280; font-weight: 400; }
        .vip-card { background: linear-gradient(135deg, rgba(15, 52, 96, 0.92), rgba(65, 105, 225, 0.92)); color: #ffffff; border: 1px solid rgba(65, 105, 225, 0.5); }
        .vip-card .social-title, .vip-card .social-description { color: #ffffff !important; font-weight: 400 !important; -webkit-text-fill-color: #ffffff !important; text-shadow: 0 2px 8px rgba(0,0,0,0.5) !important; }
        .vip-card:hover { background: linear-gradient(135deg, rgba(15, 52, 96, 0.98), rgba(65, 105, 225, 0.98)) !important; box-shadow: 0 20px 40px rgba(65, 105, 225, 0.4) !important; border: 1px solid rgba(65, 105, 225, 0.8) !important; }
        .map-card { background: linear-gradient(135deg, rgba(34, 139, 34, 0.95), rgba(50, 205, 50, 0.95)); color: white; }
        .map-card .social-title, .map-card .social-description { color: white; }
        .map-card:hover { background: linear-gradient(135deg, rgba(34, 139, 34, 0.98), rgba(50, 205, 50, 0.98)) !important; }
        .menu-card { background: linear-gradient(135deg, rgba(255, 107, 53, 0.95), rgba(247, 147, 30, 0.95)); color: white; }
        .menu-card .social-title, .menu-card .social-description { color: white; }
        .menu-card:hover { background: linear-gradient(135deg, rgba(255, 107, 53, 0.98), rgba(247, 147, 30, 0.98)) !important; }
        .tiktok { background: rgba(255, 255, 255, 0.95); }
        .tiktok:hover { background: rgba(255, 255, 255, 0.98) !important; }
        .facebook { background: rgba(255, 255, 255, 0.95); }
        .facebook:hover { background: rgba(255, 255, 255, 0.98) !important; }
        .zalo { background: rgba(255, 255, 255, 0.95); }
        .zalo:hover { background: rgba(255, 255, 255, 0.98) !important; }

        /* FLIPBOOK MODAL STYLES */
        .menu-modal, .zoom-modal, #vipModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .menu-modal.show, .zoom-modal.show, #vipModal.show { display: flex; opacity: 1; }

        .flipbook-container {
            position: relative;
            width: 90%;
            max-width: 1400px;
            height: 85vh;
            perspective: 2500px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .book-wrapper {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .book {
            position: relative;
            width: 85%;
            max-width: 1000px;
            height: 90%;
            transform-style: preserve-3d;
        }

        .page {
            position: absolute;
            width: 50%;
            height: 100%;
            top: 0;
            transform-style: preserve-3d;
            transition: transform 0.9s cubic-bezier(0.645, 0.045, 0.355, 1);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.6);
        }

        .page-left {
            left: 0;
            transform-origin: right center;
        }

        .page-right {
            right: 0;
            transform-origin: left center;
        }

        .page-content {
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(to right, #f8f5e8 0%, #fff9f0 50%, #f8f5e8 100%);
            backface-visibility: hidden;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: inset 0 0 50px rgba(139, 105, 20, 0.08);
        }

        .page-front { transform: rotateY(0deg); }
        .page-back { transform: rotateY(180deg); }

        .page-content img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 30px;
        }

        .page.flipped { transform: rotateY(-180deg); z-index: 10; }
        .page-left.flipped { transform: rotateY(180deg); }

        /* Book spine */
        .book::before {
            content: '';
            position: absolute;
            left: 50%;
            top: 0;
            width: 8px;
            height: 100%;
            background: linear-gradient(to right,
            rgba(0,0,0,0.4) 0%,
            rgba(0,0,0,0.15) 50%,
            rgba(0,0,0,0.4) 100%);
            transform: translateX(-50%);
            z-index: 100;
            border-radius: 3px;
            box-shadow: 0 0 15px rgba(0,0,0,0.3);
        }

        .book-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 65px;
            height: 65px;
            background: rgba(255, 215, 0, 0.95);
            border: 3px solid rgba(255, 215, 0, 0.3);
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: #1a1a1a;
            transition: all 0.3s ease;
            z-index: 100;
            box-shadow: 0 8px 25px rgba(255, 215, 0, 0.5);
            font-weight: bold;
        }

        .book-nav:hover:not(:disabled) {
            background: rgba(255, 215, 0, 1);
            transform: translateY(-50%) scale(1.15);
            box-shadow: 0 12px 35px rgba(255, 215, 0, 0.7);
        }

        .book-nav:active:not(:disabled) {
            transform: translateY(-50%) scale(1.05);
        }

        .book-nav.prev { left: -90px; }
        .book-nav.next { right: -90px; }

        .book-nav:disabled {
            opacity: 0.25;
            cursor: not-allowed;
            background: rgba(100, 100, 100, 0.5);
        }

        .page-counter {
            position: absolute;
            bottom: -60px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 215, 0, 0.95);
            color: #1a1a1a;
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 18px;
            box-shadow: 0 6px 25px rgba(255, 215, 0, 0.4);
            border: 2px solid rgba(255, 215, 0, 0.3);
        }

        .close-button {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 45px;
            color: #ffd700;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 1000;
            background: rgba(0, 0, 0, 0.6);
            width: 55px;
            height: 55px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(255, 215, 0, 0.3);
        }

        .close-button:hover {
            transform: rotate(90deg) scale(1.1);
            color: #fff;
            background: rgba(255, 107, 53, 0.9);
            border-color: #ff6b35;
        }

        .book-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: #ffd700;
            z-index: 10;
        }

        .spinner {
            width: 70px;
            height: 70px;
            border: 7px solid #333;
            border-top: 7px solid #ffd700;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 25px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .zoom-modal-content { max-width: 90%; max-height: 90%; position: relative; text-align: center; }
        .zoom-modal img { max-width: 100%; max-height: 100%; border-radius: 8px; animation: zoomIn 0.3s ease-out; }
        @keyframes zoomIn { from { transform: scale(0.5); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        .footer { margin-top: 48px; padding-top: 32px; border-top: 1px solid rgba(255, 255, 255, 0.2); animation: fadeIn 1s ease-out 1.6s both; }
        .footer-text { font-size: 0.875rem; color: white; margin-bottom: 8px; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3); }
        .contact-info {
            background: #ff8c00;
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 24px;
            margin-top: 32px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: fadeIn 1s ease-out 1.8s both;
            color: white;
            text-align: center;
            font-weight: 500;
        }
        .contact-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .contact-item {
            display: flex;
            align-items: flex-start;
            justify-content: center;
            margin-bottom: 16px;
            font-size: 1rem;
            line-height: 1.6;
            text-align: left;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        .contact-icon {
            font-weight: 700;
            margin-right: 12px;
            min-width: 70px;
            color: #fff8e1;
            text-align: left;
        }
        .contact-item span:not(.contact-icon) {
            flex: 1;
            color: white;
            word-break: break-word;
        }

        #vip-content-box p, #vip-content-box strong, #vip-content-box em { color: #b71c1c !important; }

        @media (max-width: 768px) {
            body { justify-content: flex-start; padding: 40px 16px 20px; }
            .main-title { font-size: 2.5rem; }
            .subtitle { font-size: 1.1rem; }
            .description { font-size: 1rem; }
            .social-grid { grid-template-columns: repeat(2, 1fr); gap: 16px; }
            .social-card { padding: 24px 16px; }
            .social-icon { width: 40px; height: 40px; }
            .social-icon img { width: 40px; height: 40px; }
            .social-title { font-size: 1rem; }
            .social-description { font-size: 0.8rem; }

            /* MOBILE: 1 trang full màn hình */
            .flipbook-container {
                width: 100%;
                height: 100vh;
                padding: 0;
            }
            .book-wrapper {
                width: 100%;
                height: 100%;
            }
            .book {
                width: 100%;
                height: 100%;
                max-width: none;
            }
            .book::before { display: none; } /* Ẩn gáy sách trên mobile */

            .page {
                width: 100% !important; /* Full width 1 trang */
                left: 0 !important;
                right: auto !important;
                transform-origin: left center;
            }

            .page-content {
                border-radius: 0;
            }

            .page-content img {
                padding: 20px;
                object-fit: contain;
            }

            /* Navigation buttons dạng overlay */
            .book-nav {
                width: 60px;
                height: 60px;
                font-size: 28px;
                background: rgba(255, 215, 0, 0.9);
                backdrop-filter: blur(10px);
            }
            .book-nav.prev {
                left: 10px;
            }
            .book-nav.next {
                right: 10px;
            }

            .page-counter {
                font-size: 16px;
                padding: 10px 25px;
                bottom: 20px;
                background: rgba(255, 215, 0, 0.95);
                backdrop-filter: blur(10px);
            }

            .close-button {
                width: 50px;
                height: 50px;
                font-size: 40px;
                top: 15px;
                right: 15px;
                background: rgba(0, 0, 0, 0.7);
                backdrop-filter: blur(10px);
            }

            /* Contact info mobile */
            .contact-info { padding: 20px; }
            .contact-title { font-size: 1.3rem; }
            .contact-item { font-size: 0.9rem; margin-bottom: 12px; }
            .contact-icon { min-width: 60px; font-size: 0.9rem; }
        }

        @media (max-width: 480px) {
            .main-title { font-size: 2rem; }
            .subtitle { font-size: 1rem; }
            .description { font-size: 0.95rem; padding: 0 10px; }
            .social-grid { grid-template-columns: 1fr; gap: 12px; }
            .logo { width: 160px; height: 100px; }
            .logo img { width: 130px; height: 65px; }

            /* Ultra mobile: 1 trang full */
            .flipbook-container {
                width: 100vw;
                height: 100vh;
                max-width: 100vw;
            }

            .page-content img {
                padding: 15px;
            }

            .book-nav {
                width: 50px;
                height: 50px;
                font-size: 24px;
            }
            .book-nav.prev { left: 8px; }
            .book-nav.next { right: 8px; }

            .page-counter {
                font-size: 14px;
                padding: 8px 20px;
                bottom: 15px;
            }

            .close-button {
                width: 45px;
                height: 45px;
                font-size: 35px;
                top: 10px;
                right: 10px;
            }

            /* VIP Modal mobile */
            #vipModal > div {
                max-width: 95% !important;
                padding: 15px !important;
            }
            #vip-title { font-size: 1.3rem !important; }
            #vip-content-box { padding: 12px !important; font-size: 0.9rem !important; }

            /* Contact info ultra mobile */
            .contact-info { padding: 16px; }
            .contact-title { font-size: 1.2rem; margin-bottom: 15px; }
            .contact-item {
                font-size: 0.85rem;
                margin-bottom: 10px;
                flex-direction: column;
                align-items: flex-start;
            }
            .contact-icon {
                margin-bottom: 5px;
                min-width: auto;
            }
        }

        @media (max-width: 360px) {
            .main-title { font-size: 1.8rem; }

            .book-nav {
                width: 45px;
                height: 45px;
                font-size: 22px;
            }
            .book-nav.prev { left: 5px; }
            .book-nav.next { right: 5px; }

            .page-counter {
                font-size: 13px;
                padding: 7px 18px;
                bottom: 12px;
            }

            .close-button {
                width: 40px;
                height: 40px;
                font-size: 30px;
                top: 8px;
                right: 8px;
            }

        }
    </style>
</head>
<body>
<div class="container">
    <div class="logo-section">
        <div class="logo">
            <img src="images/logo2.jpg" alt="Logo Bánh Xèo Cô Tư" loading="lazy">
        </div>
    </div>

    <h1 class="main-title">SAPHIRE VIP CARD</h1>
    <p class="subtitle">NHÀ HÀNG HỒ BƠI HỒNG PHÁT - HẢI SẢN TƯƠI SỐNG & MÓN ĂN ĐỒNG QUÊ</p>
    <p class="description">
        Thưởng thức hải sản tươi rói chế biến tại chỗ cùng các món đồng quê đậm vị miền Tây. Không gian hồ bơi thoáng mát, thích hợp tổ chức tiệc tất niên, sinh nhật, họp mặt và liên hoan gia đình.
    </p>

    <div class="social-grid">
        <div class="social-card vip-card" onclick="showVipPrivileges()">
            <div class="social-icon">
                <img src="images/saphira.png" alt="Đặc Quyền VIP" loading="lazy">
            </div>
            <div class="social-title">Đặc Quyền VIP SAPHIRE</div>
            <div class="social-description">Ưu đãi độc quyền cho thành viên SAPHIRE</div>
        </div>

        <a href="#" onclick="showMenu(); return false;" class="social-card menu-card">
            <div class="social-icon">
                <img src="images/menu.png" alt="Thực đơn" loading="lazy">
            </div>
            <div class="social-title">Thực đơn</div>
            <div class="social-description">Xem thực đơn dạng sách lật trang 3D</div>
        </a>

        <a href="https://maps.app.goo.gl/kNoDNUMmPqGVXKkT8" target="_blank" rel="noopener" class="social-card map-card">
            <div class="social-icon">
                <img src="images/ggmap.png" alt="Google Maps" loading="lazy">
            </div>
            <div class="social-title">Địa điểm</div>
            <div class="social-description">Tìm đường và xem bản đồ đến quán</div>
        </a>

        <a href="https://www.tiktok.com/@nhahanghoboihongphat.ct" target="_blank" rel="noopener" class="social-card tiktok">
            <div class="social-icon">
                <img src="images/tiktok.png" alt="TikTok" loading="lazy">
            </div>
            <div class="social-title">TikTok</div>
            <div class="social-description">Xem video làm bánh xèo và đánh giá khách hàng</div>
        </a>

        <a href="https://www.facebook.com/nhahanghoppho" target="_blank" rel="noopener" class="social-card facebook">
            <div class="social-icon">
                <img src="images/facebook1.png" alt="Facebook" loading="lazy">
            </div>
            <div class="social-title">Facebook</div>
            <div class="social-description">Theo dõi tin tức và khuyến mãi mới nhất</div>
        </a>

        <a href="https://zalo.me/0901273222" target="_blank" rel="noopener" class="social-card zalo">
            <div class="social-icon">
                <img src="images/zalo1.png" alt="Zalo" loading="lazy">
            </div>
            <div class="social-title">Zalo</div>
            <div class="social-description">Liên hệ và Tư vấn</div>
        </a>
    </div>

    <!-- VIP MODAL -->
    <!-- VIP MODAL SAPHIRA -->
    <!-- VIP MODAL – SAPHIRA DÙNG CHUNG KHUNG VỚI GOLD -->
    <div id="vipModal" class="menu-modal">
        <div style="max-width: 500px; background: white; border-radius: 16px; padding: 20px; position: relative; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);">
            <span class="close-button" onclick="hideVip()">×</span>
            <div style="margin-bottom: 20px; text-align:center;">
                <img id="vip-image" src="images/vipgold.png" alt="SAPHIRA VIP" style="width: 80px; height: 80px; border-radius: 50%; border: 3px solid #ffd700; box-shadow: 0 0 20px rgba(255,215,0,0.5);">
            </div>
            <h2 id="vip-title" style="margin: 16px 0; color: #d32f2f; font-weight: 800; font-size: 1.5rem; text-align:center;">
                ĐẶC QUYỀN THẺ SAPHIRE VIP
            </h2>
            <div id="vip-content-box" style="background: #fff8e1; border-left: 4px solid #ffb300; padding: 16px; margin: 20px 0; border-radius: 0 8px 8px 0; text-align: left; font-size: 1rem; line-height: 1.8;"></div>
            <div style="background: #f3e5f5; border-left: 4px solid #8e24aa; padding: 16px; margin: 20px 0; border-radius: 0 8px 8px 0; text-align: left; font-size: 1rem; line-height: 1.8;">
                <p style="margin: 0; color: #4a148c;">
                    <strong>Thời hạn sử dụng thẻ:</strong><br>
                    <span id="vip-expiry-date" style="font-weight: 700; color: #d32f2f; font-size: 1.1em;">Đang tải...</span>
                </p>
            </div>
            <p style="margin-top: 24px; font-size: 0.9rem; color: #666; font-style: italic; text-align:center;">
                * Ưu đãi không áp dụng đồng thời với chương trình khác<br>
                * Quý khách vui lòng xuất trình thẻ VIP khi thanh toán
            </p>
        </div>
    </div>

    <!-- FLIPBOOK MENU MODAL -->
    <div id="menuModal" class="menu-modal">
        <span class="close-button" onclick="hideMenu()">×</span>

        <div class="flipbook-container">
            <button class="book-nav prev" id="prevBtn" onclick="prevPage()">‹</button>

            <div class="book-wrapper">
                <div class="book" id="book">
                    <div class="book-loading" id="bookLoading">
                        <div class="spinner"></div>
                        <p style="font-size:1.4rem; margin:0; font-weight: 600;">Đang mở sách thực đơn...</p>
                    </div>
                </div>

                <div class="page-counter" id="pageCounter">
                    Trang <span id="currentPage">0</span> / <span id="totalPages">0</span>
                </div>
            </div>

            <button class="book-nav next" id="nextBtn" onclick="nextPage()">›</button>
        </div>
    </div>

    <!-- ZOOM MODAL -->
    <div id="zoomModal" class="zoom-modal">
        <div class="zoom-modal-content">
            <span class="close-button" onclick="hideZoom()">×</span>
            <img id="zoomedImage" src="" alt="Zoomed Image">
        </div>
    </div>

    <div class="contact-info">
        <h3 class="contact-title">Thông tin liên hệ</h3>
        <div class="contact-item">
            <span class="contact-icon">Địa chỉ:</span>
            <span>Số 10B,đường Trần Hoàng Na, Khu dân cư Hồng Phát, P. An Bình, Cần Thơ</span>
        </div>
        <div class="contact-item">
            <span class="contact-icon">Hotline:</span>
            <span>0901 273 222</span>
        </div>
        <div class="contact-item">
            <span class="contact-icon">Giờ mở cửa:</span>
            <span>10:00 – 23:00 (hàng ngày)</span>
        </div>
    </div>
    <div class="footer">
        <p class="footer-text">© 2025 Nhà Hàng Hồ Bơi Hồng Phát. Tất cả quyền được bảo lưu.</p>
        <p class="footer-text">Thuộc Hệ Sinh Thái Ông Đề - Làng Du Lịch Sinh Thái Ông Đề</p>
    </div>
</div>

<script>
    // Ripple Effect
    document.querySelectorAll('.social-card').forEach(card => {
        card.addEventListener('click', function(e) {
            const ripple = document.createElement('div');
            ripple.style.cssText = `position: absolute; border-radius: 50%; background: rgba(255, 255, 255, 0.6); transform: scale(0); animation: ripple 0.6s linear; pointer-events: none;`;
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            this.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });
    });

    const style = document.createElement('style');
    style.textContent = `@keyframes ripple { to { transform: scale(4); opacity: 0; } }`;
    document.head.appendChild(style);

    // FLIPBOOK VARIABLES
    let currentPageIndex = 0;
    let menuImages = [];
    let pages = [];

    // SHOW FLIPBOOK MENU
    function showMenu() {
        const modal = document.getElementById('menuModal');
        const book = document.getElementById('book');
        const loading = document.getElementById('bookLoading');

        if (!modal || !book) return;

        modal.classList.add('show');
        document.body.style.overflow = 'hidden';

        if (loading) loading.style.display = 'block';

        const API_URL = 'https://social.ongde.vn/api/menu-nha-hang';

        fetch(API_URL)
            .then(r => r.json())
            .then(result => {
                if (!result.success || !result.data || result.data.length === 0) {
                    throw new Error('Không có dữ liệu');
                }

                menuImages = result.data;
                createFlipbook();

                if (loading) loading.style.display = 'none';
            })
            .catch(err => {
                console.error(err);
                if (loading) {
                    loading.innerHTML = '<p style="color:#ff6b35; font-size:1.4rem;">Không tải được thực đơn. Vui lòng thử lại!</p>';
                }
            });
    }

    function createFlipbook() {
        const book = document.getElementById('book');
        if (!book) return;

        book.innerHTML = '';
        pages = [];
        currentPageIndex = 0;

        // TRANG BÌA ĐẦU
        const coverPage = document.createElement('div');
        coverPage.className = 'page page-right';
        coverPage.style.zIndex = menuImages.length + 2;

        const coverFront = document.createElement('div');
        coverFront.className = 'page-content page-front';
        coverFront.innerHTML = `
            <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; background: linear-gradient(135deg, #ffd700 0%, #ffb800 100%); padding:40px; text-align:center;">
                <img src="images/logo2.jpg" alt="Logo" style="width:150px; height:100px; border-radius:20px; margin-bottom:30px; box-shadow:0 10px 30px rgba(0,0,0,0.3);">
                <h1 style="color:#1a1a1a; font-size:2.5rem; font-weight:800; margin-bottom:20px; text-shadow:2px 2px 4px rgba(0,0,0,0.1);">THỰC ĐƠN</h1>
                <h2 style="color:#2d1b0a; font-size:1.5rem; font-weight:600; margin-bottom:15px;">NHÀ HÀNG HỒ BƠI HỒNG PHÁT</h2>
                <p style="color:#3f2b16; font-size:1.1rem; line-height:1.6; max-width:80%;">Hải Sản Tươi Sống & Món Ăn Đồng Quê</p>
                <p style="color:#5c3f1f; font-size:0.95rem; margin-top:20px; font-style:italic;">📞 Hotline: 0901 273 222</p>
            </div>
        `;

        const coverBack = document.createElement('div');
        coverBack.className = 'page-content page-back';
        coverBack.innerHTML = `
            <div style="display:flex; align-items:center; justify-content:center; height:100%; background:#f8f5e8; padding:40px; text-align:center;">
                <p style="color:#8B6914; font-size:1.3rem; font-weight:600; line-height:1.8;">
                 NHÀ HÀNG HỒ BƠI HỒNG PHÁT<br><span style="color:#d32f2f; font-size:1.6rem; font-weight:800;">CHÚC QUÝ KHÁCH NGON MIỆNG</span>

                </p>
            </div>
        `;

        coverPage.appendChild(coverFront);
        coverPage.appendChild(coverBack);
        book.appendChild(coverPage);
        pages.push(coverPage);

        // CÁC TRANG THỰC ĐƠN
        for (let i = 0; i < menuImages.length; i++) {
            const pageDiv = document.createElement('div');
            pageDiv.className = 'page page-right';
            pageDiv.style.zIndex = menuImages.length - i + 1;

            const front = document.createElement('div');
            front.className = 'page-content page-front';
            front.innerHTML = `<img src="${menuImages[i].url}" alt="Menu ${i+1}" loading="lazy" style="cursor:pointer;" onclick="zoomImage('${menuImages[i].url}')">`;

            const back = document.createElement('div');
            back.className = 'page-content page-back';
            back.style.background = '#f8f5e8';

            pageDiv.appendChild(front);
            pageDiv.appendChild(back);
            book.appendChild(pageDiv);
            pages.push(pageDiv);
        }

        // TRANG BÌA CUỐI
        const backCoverPage = document.createElement('div');
        backCoverPage.className = 'page page-right';
        backCoverPage.style.zIndex = 0;

        const backCoverFront = document.createElement('div');
        backCoverFront.className = 'page-content page-front';
        backCoverFront.innerHTML = `
            <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; background: linear-gradient(135deg, #ff8c00 0%, #ff6b35 100%); padding:40px; text-align:center; color:white;">
                <h2 style="font-size:2rem; font-weight:800; margin-bottom:25px; text-shadow:2px 2px 4px rgba(0,0,0,0.3);">CẢM ƠN QUÝ KHÁCH</h2>
                <div style="background:rgba(255,255,255,0.2); padding:25px; border-radius:15px; backdrop-filter:blur(10px); margin-bottom:20px;">
                    <p style="font-size:1.1rem; margin-bottom:12px; font-weight:600;">📍 Địa chỉ:</p>
                    <p style="font-size:0.95rem; margin-bottom:20px; line-height:1.6;">Số 10B, đường Trần Hoàng Na<br>Khu dân cư Hồng Phát, Cần Thơ</p>
                    <p style="font-size:1.1rem; margin-bottom:12px; font-weight:600;">📞 Hotline:</p>
                    <p style="font-size:1.3rem; font-weight:800; margin-bottom:20px;">0901 273 222</p>
                    <p style="font-size:1.1rem; margin-bottom:12px; font-weight:600;">🕐 Giờ mở cửa:</p>
                    <p style="font-size:0.95rem;">10:00 – 23:00 (Hàng ngày)</p>
                </div>
                <p style="font-size:0.9rem; font-style:italic; opacity:0.9;">Hẹn gặp lại quý khách!</p>
            </div>
        `;

        const backCoverBack = document.createElement('div');
        backCoverBack.className = 'page-content page-back';
        backCoverBack.innerHTML = `
            <div style="display:flex; align-items:center; justify-content:center; height:100%; background: linear-gradient(135deg, #8B6914 0%, #b8860b 100%); padding:40px;">
                <img src="images/logo2.jpg" alt="Logo" style="width:200px; height:130px; border-radius:25px; box-shadow:0 15px 40px rgba(0,0,0,0.5);">
            </div>
        `;

        backCoverPage.appendChild(backCoverFront);
        backCoverPage.appendChild(backCoverBack);
        book.appendChild(backCoverPage);
        pages.push(backCoverPage);

        updatePageCounter();
        updateNavButtons();
    }

    function nextPage() {
        // Nếu đang ở trang cuối, quay về đầu
        if (currentPageIndex >= pages.length) {
            resetToFirstPage();
            return;
        }

        pages[currentPageIndex].classList.add('flipped');
        currentPageIndex++;

        updatePageCounter();
        updateNavButtons();
    }

    function prevPage() {
        // Nếu đang ở trang đầu, nhảy tới cuối
        if (currentPageIndex <= 0) {
            jumpToLastPage();
            return;
        }

        currentPageIndex--;
        pages[currentPageIndex].classList.remove('flipped');

        updatePageCounter();
        updateNavButtons();
    }

    // Hàm reset về trang đầu với animation
    function resetToFirstPage() {
        // Lật ngược tất cả trang về ban đầu
        for (let i = pages.length - 1; i >= 0; i--) {
            setTimeout(() => {
                pages[i].classList.remove('flipped');
            }, (pages.length - 1 - i) * 100); // Animation cascade
        }

        currentPageIndex = 0;

        setTimeout(() => {
            updatePageCounter();
            updateNavButtons();
        }, pages.length * 100);
    }

    // Hàm nhảy tới trang cuối với animation
    function jumpToLastPage() {
        // Lật tất cả trang
        for (let i = 0; i < pages.length; i++) {
            setTimeout(() => {
                pages[i].classList.add('flipped');
            }, i * 100); // Animation cascade
        }

        currentPageIndex = pages.length;

        setTimeout(() => {
            updatePageCounter();
            updateNavButtons();
        }, pages.length * 100);
    }

    function updatePageCounter() {
        const currentEl = document.getElementById('currentPage');
        const totalEl = document.getElementById('totalPages');
        if (currentEl) currentEl.textContent = currentPageIndex + 1;
        if (totalEl) totalEl.textContent = pages.length;
    }

    function updateNavButtons() {
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');

        // Không disable nút nào cả - cho phép loop vô tận
        if (prevBtn) prevBtn.disabled = false;
        if (nextBtn) nextBtn.disabled = false;

        // Thay đổi icon khi ở đầu/cuối để user biết sẽ loop
        if (prevBtn) {
            if (currentPageIndex === 0) {
                prevBtn.innerHTML = '⟲'; // Icon quay về cuối
                prevBtn.style.fontSize = '26px';
            } else {
                prevBtn.innerHTML = '‹';
                prevBtn.style.fontSize = '28px';
            }
        }

        if (nextBtn) {
            if (currentPageIndex >= pages.length) {
                nextBtn.innerHTML = '⟲'; // Icon quay về đầu
                nextBtn.style.fontSize = '26px';
            } else {
                nextBtn.innerHTML = '›';
                nextBtn.style.fontSize = '28px';
            }
        }
    }

    function hideMenu() {
        const modal = document.getElementById('menuModal');
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
            currentPageIndex = 0;
            pages.forEach(p => p.classList.remove('flipped'));
        }
    }

    function zoomImage(src) {
        const zm = document.getElementById('zoomModal');
        const zi = document.getElementById('zoomedImage');
        if (zm && zi) {
            zi.src = src;
            zm.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }

    function hideZoom() {
        const zm = document.getElementById('zoomModal');
        if (zm) {
            zm.classList.remove('show');
            document.body.style.overflow = 'auto';
        }
    }

    function showVipPrivileges() {
        const modal = document.getElementById('vipModal');
        if (!modal) return;

        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        document.getElementById('vip-image').src = 'images/saphira.png';   // đổi tên ảnh ở đây
        const contentBox = document.getElementById('vip-content-box');
        const expirySpan = document.getElementById('vip-expiry-date');

        contentBox.innerHTML = '<p style="margin:0; color:#b71c1c;"><strong>Đang tải ưu đãi SAPHIRE...</strong></p>';
        expirySpan.textContent = 'Đang tải...';

        fetch(`${window.location.origin}/api/vip-card/info`)
            .then(res => {
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                return res.json();
            })
            .then(result => {
                if (!result.success || !result.data || !Array.isArray(result.data)) {
                    throw new Error('Dữ liệu không hợp lệ');
                }

                // ƯU TIÊN LẤY THẺ SAPPHIRE (SAPHIRA VIP)
                const saphiraCard = result.data.find(card =>
                    card.type.toUpperCase() === 'SAPPHIRE'
                );

                // Nếu không có thì lấy DIAMOND → GOLD (dự phòng)
                const fallbackCard = saphiraCard || result.data.find(c => c.type === 'DIAMOND') || result.data.find(c => c.type === 'GOLD');
                if (!fallbackCard) throw new Error('Không có thẻ VIP');

                const card = fallbackCard;

                // Cố định hiển thị SAPHIRA nếu là SAPPHIRE
                const isSaphira = card.type.toUpperCase() === 'SAPPHIRE' || card.type.toUpperCase() === 'SAPHIRA';
                document.getElementById('vip-title').textContent = isSaphira ? 'ĐẶC QUYỀN THẺ SAPHIRE VIP' : 'ĐẶC QUYỀN THẺ ' + card.type + ' VIP';

                // Nội dung ưu đãi – giữ nguyên kiểu GOLD
                if (contentBox && card.content) {
                    const temp = document.createElement('div');
                    temp.innerHTML = card.content;

                    temp.querySelectorAll('p, strong, em, br').forEach(el => {
                        if (el.tagName !== 'BR') {
                            el.style.cssText = 'color: #b71c1c !important;';
                        }
                    });
                    temp.querySelectorAll('strong').forEach(s => {
                        if (s.textContent.includes('Giảm') || s.textContent.includes('%')) {
                            s.style.cssText = 'font-weight:900 !important; font-size:1.15em !important; color:#d32f2f !important;';
                        }
                    });

                    contentBox.innerHTML = temp.innerHTML;
                }

                // Ngày hết hạn
                expirySpan.innerHTML = `Đến hết ngày <u style="color:#d32f2f;">${card.expiry_date || 'Vĩnh viễn'}</u>`;
            })
            .catch(err => {
                console.error('Lỗi VIP:', err);
                contentBox.innerHTML = '<p style="margin:0; color:#d32f2f;"><strong>THẺ SAPHIRA CHƯA KÍCH HOẠT</strong></p>';
                expirySpan.textContent = '—';
            });
    }




    function hideVip() {
        const modal = document.getElementById('vipModal');
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
        }
    }

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        const menuModal = document.getElementById('menuModal');
        if (menuModal && menuModal.classList.contains('show')) {
            if (e.key === 'ArrowRight') nextPage();
            if (e.key === 'ArrowLeft') prevPage();
            if (e.key === 'Escape') hideMenu();
        } else if (e.key === 'Escape') {
            hideZoom();
            hideVip();
        }
    });

    // Click outside to close
    ['menuModal', 'zoomModal', 'vipModal'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('click', (e) => {
                if (e.target === el) {
                    if (id === 'menuModal') hideMenu();
                    else if (id === 'zoomModal') hideZoom();
                    else hideVip();
                }
            });
        }
    });
</script>
</body>
</html>