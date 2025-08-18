<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Hệ Sinh Thái Ông Đề</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #ff8c42;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 16px;
            color: #1a1a1a;
            line-height: 1.6;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image:
                    radial-gradient(circle at 20% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                    radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.08) 0%, transparent 50%),
                    radial-gradient(circle at 40% 40%, rgba(255, 255, 255, 0.06) 0%, transparent 50%),
                    radial-gradient(circle at 60% 80%, rgba(255, 255, 255, 0.04) 0%, transparent 50%),
                    radial-gradient(circle at 90% 60%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
            animation: floatParticles 12s ease-in-out infinite;
            pointer-events: none;
            z-index: 0;
        }

        @keyframes floatParticles {
            0%, 100% { transform: translateY(0px) rotate(0deg) scale(1); opacity: 0.7; }
            33% { transform: translateY(-20px) rotate(120deg) scale(1.1); opacity: 1; }
            66% { transform: translateY(10px) rotate(240deg) scale(0.9); opacity: 0.8; }
        }

        .container {
            max-width: 1200px;
            width: 100%;
            text-align: center;
            animation: fadeInUp 0.8s ease-out;
            position: relative;
            z-index: 10;
        }

        .header-title {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 24px;
            letter-spacing: -0.02em;
            background: linear-gradient(-45deg, #ffd700, #4682b4, #8b4513, #ffffff, #87ceeb, #a0522d, #ffcc00, #1e90ff, #deb887, #ffffff, #ffd700);
            background-size: 400% 400%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: fadeIn 1s ease-out 0.3s both, gradientText 4s ease infinite;
            text-align: center;
        }

        @keyframes gradientText {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .menu-section {
            margin-bottom: 32px;
            animation: fadeIn 1s ease-out 0.4s both;
            position: relative;
            z-index: 30;
        }

        .tab-menu {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 8px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 8px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 30;
        }

        .menu-button {
            padding: 10px 16px;
            border-radius: 12px;
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            white-space: nowrap;
            min-width: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .menu-button:hover {
            color: white;
            transform: translateY(-2px);
        }

        .menu-button.active {
            background: rgba(255, 255, 255, 0.95);
            color: #1f2937;
            font-weight: 600;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .main-title {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 12px;
            letter-spacing: -0.02em;
            background: linear-gradient(-45deg, #ffd700, #4682b4, #8b4513, #ffffff, #87ceeb, #a0522d, #ffcc00, #1e90ff, #deb887, #ffffff, #ffd700);
            background-size: 400% 400%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: fadeIn 1s ease-out 0.4s both, gradientText 4s ease infinite;
        }

        .subtitle {
            font-size: 1.1rem;
            color: white;
            margin-bottom: 32px;
            font-weight: 400;
            animation: fadeIn 1s ease-out 0.5s both;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            z-index: 20;
        }

        .image-item {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .image-item:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }

        .image-item img {
            width: 100%;
            height: auto;
            min-height: 200px;
            max-height: 400px;
            object-fit: contain;
            object-position: center;
            border-radius: 12px 12px 0 0;
            transition: transform 0.3s ease;
            background: #f8f9fa;
            cursor: pointer;
        }

        .image-item:hover img {
            transform: scale(1.05);
        }

        .image-item img:active {
            transform: scale(0.98);
        }

        .image-name {
            font-size: 0.95rem;
            color: #1f2937;
            text-align: center;
            padding: 12px 8px;
            font-weight: 500;
            line-height: 1.4;
            background: white;
            border-radius: 0 0 12px 12px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 500;
            align-items: center;
            justify-content: center;
            padding: 16px;
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            background: transparent;
            border-radius: 12px;
            padding: 0;
            max-width: 95vw;
            width: 100%;
            max-height: 95vh;
            overflow: hidden;
            position: relative;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .close-button {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 1.8rem;
            cursor: pointer;
            color: white;
            background: rgba(0, 0, 0, 0.7);
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            transition: all 0.3s ease;
            font-weight: bold;
        }

        .close-button:hover {
            background: rgba(255, 0, 0, 0.8);
            transform: scale(1.1);
        }

        .close-button:active {
            transform: scale(0.95);
        }

        .media-modal-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            min-height: 200px;
        }

        .media-modal-content img {
            max-width: 100%;
            max-height: 90vh;
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .nav-button {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.7);
            border: none;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-button:hover {
            background: rgba(0, 102, 255, 0.9);
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 4px 15px rgba(0, 102, 255, 0.4);
        }

        .nav-button:active {
            transform: translateY(-50%) scale(0.95);
        }

        .nav-prev {
            left: 20px;
        }

        .nav-next {
            right: 20px;
        }

        .image-counter {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            z-index: 20;
        }

        /* Pagination styles */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-top: 30px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .pagination-btn {
            width: 50px;
            height: 50px;
            border: none;
            border-radius: 50%;
            background: linear-gradient(45deg, #4ecdc4, #45b7d1);
            color: white;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(69, 183, 209, 0.3);
        }

        .pagination-btn:hover:not(.disabled) {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 6px 20px rgba(69, 183, 209, 0.4);
        }

        .pagination-btn:active:not(.disabled) {
            transform: translateY(0) scale(1.02);
        }

        .pagination-btn.disabled {
            background: #ccc;
            cursor: not-allowed;
            opacity: 0.6;
            box-shadow: none;
        }

        .page-info {
            text-align: center;
            min-width: 120px;
        }

        .page-text {
            display: block;
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }

        .items-info {
            font-size: 0.9rem;
            color: #666;
        }

        .total-items {
            text-align: center;
            margin-top: 20px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .total-items .items-info {
            font-size: 1rem;
            color: #333;
            font-weight: 500;
        }

        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }

        .footer {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeIn 1s ease-out 1.6s both;
            z-index: 10;
        }

        .footer-text {
            font-size: 0.8rem;
            color: white;
            margin-bottom: 4px;
        }

        .loading, .error {
            padding: 40px;
            text-align: center;
            font-size: 1.1rem;
            color: #666;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            margin: 20px;
        }

        @media (max-width: 768px) {
            .menu-button {
                min-width: 100px;
                font-size: 0.8rem;
                padding: 8px 12px;
            }

            .image-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 16px;
                padding: 16px;
            }

            .image-item img {
                min-height: 150px;
                max-height: 300px;
            }

            .media-modal-content img {
                max-height: 80vh;
            }

            .nav-button {
                width: 45px;
                height: 45px;
                font-size: 1rem;
            }

            .nav-prev {
                left: 15px;
            }

            .nav-next {
                right: 15px;
            }

            .image-counter {
                bottom: 15px;
                font-size: 0.8rem;
                padding: 6px 12px;
            }

            .pagination {
                gap: 15px;
                margin-top: 20px;
                padding: 15px;
            }

            .pagination-btn {
                width: 45px;
                height: 45px;
                font-size: 1rem;
            }

            .page-text {
                font-size: 1rem;
            }

            .items-info {
                font-size: 0.8rem;
            }
        }

        @media (max-width: 480px) {
            .image-grid {
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
                gap: 12px;
            }

            .image-item img {
                min-height: 120px;
                max-height: 250px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="logo-section">
        <div class="logo" style="width: 160px; height: 96px; margin: 0 auto 20px; border-radius: 20px; background: #ffffff; display: flex; align-items: center; justify-content: center; box-shadow: 0 16px 32px rgba(0, 0, 0, 0.1);">
            <img src="images/logo.png" alt="Logo Ông Đề" style="width: 128px; height: 64px; border-radius: 12px;" onerror="this.style.display='none'">
        </div>
    </div>

    <h1 class="header-title">MENU ÔNG ĐỀ</h1>

    <div class="menu-section">
        <div class="tab-menu">
            <button class="menu-button active" onclick="selectContent('khai-vi')">Khai Vị</button>
            <button class="menu-button" onclick="selectContent('mon-chinh')">Món Chính</button>
            <button class="menu-button" onclick="selectContent('mon-kem')">Món Kèm</button>
            <button class="menu-button" onclick="selectContent('lau')">Lẩu</button>
            <button class="menu-button" onclick="selectContent('trang-mieng')">Tráng Miệng</button>
            <button class="menu-button" onclick="selectContent('thuc-uong')">Thức Uống</button>
            <button class="menu-button" onclick="selectContent('combo-set')">Combo/Set</button>
            <button class="menu-button" onclick="selectContent('mon-dac-biet')">Món Đặc Biệt</button>
        </div>
    </div>

    <h2 class="main-title" id="mainTitle">Khai Vị</h2>
    <p class="subtitle" id="subtitle">Các món khai vị đặc sắc</p>

    <div id="contentArea">
        <div class="loading">⏳ Đang tải dữ liệu...</div>
    </div>

    <!-- Modal chào mừng -->
    <div class="modal" id="welcomeModal" style="display: flex;">
        <div class="modal-content" onclick="event.stopPropagation()" style="background: white; max-width: 500px; padding: 30px; text-align: center;">
            <span class="close-button" onclick="closeModal('welcomeModal')" style="color: #333; background: rgba(0,0,0,0.1);">&times;</span>
            <div class="welcome-content">
                <div style="margin-bottom: 20px;">
                    <img src="images/logo.png" alt="Logo Ông Đề" style="width: 80px; height: 40px; border-radius: 8px;" onerror="this.style.display='none'">
                </div>
                <h2 style="color: #2d5016; margin-bottom: 20px; font-size: 1.4rem; font-weight: 700;">Chào mừng quý khách!</h2>
                <p style="color: #333; line-height: 1.6; font-size: 1rem; margin-bottom: 25px;">
                    Chào mừng quý khách đến với <strong>Làng Du Lịch Ông Đề</strong> – nơi đậm đà hồn quê miền Tây.
                    Kính chúc quý khách thưởng thức món ngon tròn vị, trải nghiệm trò chơi dân gian thật vui và lưu lại nhiều kỷ niệm đáng nhớ!
                </p>
                <button onclick="closeModal('welcomeModal')" style="
                    background: linear-gradient(45deg, #4ecdc4, #45b7d1);
                    color: white;
                    border: none;
                    padding: 12px 30px;
                    border-radius: 25px;
                    font-size: 1rem;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    box-shadow: 0 4px 15px rgba(69, 183, 209, 0.3);
                " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(69, 183, 209, 0.4)'"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(69, 183, 209, 0.3)'">
                    Khám phá menu ngay!
                </button>
            </div>
        </div>
    </div>

    <!-- Modal cho xem ảnh -->
    <div class="modal" id="mediaModal" onclick="closeModalOnOutside(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <span class="close-button" onclick="closeModal('mediaModal')">&times;</span>
            <div id="mediaModalContent"></div>
        </div>
    </div>

    <div class="footer">
        <p class="footer-text">© 2025 Làng Du Lịch Sinh Thái Ông Đề. Tất cả quyền được bảo lưu.</p>
        <p class="footer-text">Công Ty TNHH Làng Du Lịch Sinh Thái Ông Đề.</p>
        <p class="footer-text">Địa chỉ: Số 168-AB1, Đường Xuân Thuỷ, Khu Dân Cư Hồng Phát, Phường An Bình, Thành Phố Cần Thơ, Việt Nam.</p>
        <p class="footer-text">Mã Số Thuế: 1801218923 | Hotline: 0931 852 113</p>
    </div>
</div>

<script>
    // Global variables
    let currentType = 'khai-vi';
    let currentData = [];
    let currentImageIndex = 0;
    let currentPage = 1;
    let itemsPerPage = 4;

    // API endpoints
    const API_BASE_URL = window.location.origin;

    // Utility functions
    function shuffleArray(array) {
        const shuffled = [...array];
        for (let i = shuffled.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [shuffled[i], shuffled[j]] = [shuffled[i], shuffled[j]];
        }
        return shuffled;
    }

    // Safe element access
    function safeGetElement(id) {
        const element = document.getElementById(id);
        if (!element) {
            console.warn(`Element with ID '${id}' not found`);
        }
        return element;
    }

    // Select content
    async function selectContent(type) {
        currentType = type;

        // Update active state
        document.querySelectorAll('.menu-button').forEach(button => {
            button.classList.remove('active');
        });

        // Set active cho button được click
        const clickedButton = document.querySelector(`.menu-button[onclick="selectContent('${type}')"]`);
        if (clickedButton) {
            clickedButton.classList.add('active');
        }

        // Update titles
        const typeNames = {
            'khai-vi': 'Khai Vị',
            'mon-chinh': 'Món Chính',
            'mon-kem': 'Món Kèm',
            'lau': 'Lẩu',
            'trang-mieng': 'Tráng Miệng',
            'thuc-uong': 'Thức Uống',
            'combo-set': 'Combo/Set',
            'mon-dac-biet': 'Món Đặc Biệt'
        };

        const mainTitle = safeGetElement('mainTitle');
        const subtitle = safeGetElement('subtitle');

        if (mainTitle) {
            mainTitle.textContent = typeNames[type];
        }

        if (subtitle) {
            subtitle.textContent = `Các món ${typeNames[type].toLowerCase()} đặc sắc`;
        }

        // Load data
        await loadData(type);
    }

    // Load data
    async function loadData(type) {
        const contentArea = safeGetElement('contentArea');
        if (!contentArea) return;

        try {
            contentArea.innerHTML = '<div class="loading">🎲 Đang tải dữ liệu...</div>';

            // Reset về trang 1 khi load danh mục mới
            currentPage = 1;

            // Gọi API /api/images-menu-ongde với tham số category
            const response = await fetch(`${API_BASE_URL}/api/images-menu-ongde?category=${type}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (!data.success || !data.data || data.data.length === 0) {
                contentArea.innerHTML = '<div class="error">📭 Không có dữ liệu để hiển thị</div>';
                currentData = [];
                return;
            }

            currentData = shuffleArray(data.data);
            renderImages(currentData);

        } catch (error) {
            console.error('Error loading data:', error);
            contentArea.innerHTML = `<div class="error">❌ Lỗi tải dữ liệu: ${error.message}</div>`;
        }
    }

    // Render images với phân trang
    function renderImages(items) {
        const contentArea = safeGetElement('contentArea');
        if (!contentArea) return;

        if (!items || items.length === 0) {
            contentArea.innerHTML = '<div class="error">📭 Không có ảnh để hiển thị</div>';
            return;
        }

        const totalPages = Math.ceil(items.length / itemsPerPage);
        const startIndex = (currentPage - 1) * itemsPerPage;
        const currentItems = items.slice(startIndex, startIndex + itemsPerPage);

        // Tạo HTML cho từng ảnh
        let imagesHTML = '';
        for (let i = 0; i < currentItems.length; i++) {
            const item = currentItems[i];
            const actualIndex = startIndex + i;
            const name = item.name || '';
            const showName = name && name !== 'Unnamed Dish';

            imagesHTML += '<div class="image-item">';
            imagesHTML += `<img src="${item.url}" alt="${name}" onerror="this.style.display='none'" onclick="viewMedia('${item.url}', ${actualIndex})">`;
            if (showName) {
                imagesHTML += `<p class="image-name">${name}</p>`;
            }
            imagesHTML += '</div>';
        }

        // Tạo phân trang
        let paginationHTML = '';
        if (totalPages > 1) {
            paginationHTML = '<div class="pagination">';

            // Nút Previous
            if (currentPage > 1) {
                paginationHTML += `<button class="pagination-btn" onclick="changePage(${currentPage - 1})">`;
                paginationHTML += '<i class="fas fa-chevron-left"></i></button>';
            } else {
                paginationHTML += '<button class="pagination-btn disabled" disabled>';
                paginationHTML += '<i class="fas fa-chevron-left"></i></button>';
            }

            // Thông tin trang
            paginationHTML += '<div class="page-info">';
            paginationHTML += `<span class="page-text">Trang ${currentPage} / ${totalPages}</span>`;
            paginationHTML += `<span class="items-info">(${items.length} ảnh)</span>`;
            paginationHTML += '</div>';

            // Nút Next
            if (currentPage < totalPages) {
                paginationHTML += `<button class="pagination-btn" onclick="changePage(${currentPage + 1})">`;
                paginationHTML += '<i class="fas fa-chevron-right"></i></button>';
            } else {
                paginationHTML += '<button class="pagination-btn disabled" disabled>';
                paginationHTML += '<i class="fas fa-chevron-right"></i></button>';
            }

            paginationHTML += '</div>';
        } else {
            paginationHTML = `<div class="total-items"><span class="items-info">Tổng cộng: ${items.length} ảnh</span></div>`;
        }

        // Cập nhật nội dung
        contentArea.innerHTML = '<div class="image-grid">' + imagesHTML + '</div>' + paginationHTML;
    }

    // Chuyển trang
    function changePage(newPage) {
        const totalPages = Math.ceil(currentData.length / itemsPerPage);

        if (newPage < 1 || newPage > totalPages) return;

        currentPage = newPage;
        renderImages(currentData);

        // Scroll to top smooth
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    // View media với navigation
    function viewMedia(url, index = 0) {
        const modal = safeGetElement('mediaModal');
        const content = safeGetElement('mediaModalContent');

        if (!modal || !content) return;

        currentImageIndex = index;

        // Thêm loading state
        content.innerHTML = `
            <div class="media-modal-content">
                <div style="display: flex; align-items: center; justify-content: center; height: 200px; color: #666;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 2rem; margin-right: 10px;"></i>
                    Đang tải ảnh...
                </div>
            </div>
        `;

        modal.style.display = 'flex';

        // Tạo ảnh mới và load
        const img = new Image();
        img.onload = function() {
            content.innerHTML = `
                <div class="media-modal-content">
                    ${currentData.length > 1 ? `
                        <button class="nav-button nav-prev" onclick="navigateImage(-1)" title="Ảnh trước">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    ` : ''}

                    <img src="${url}" alt="Image Preview" style="animation: fadeIn 0.3s ease;">

                    ${currentData.length > 1 ? `
                        <button class="nav-button nav-next" onclick="navigateImage(1)" title="Ảnh sau">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    ` : ''}

                    ${currentData.length > 1 ? `
                        <div class="image-counter">
                            ${currentImageIndex + 1} / ${currentData.length}
                        </div>
                    ` : ''}
                </div>
            `;
        };
        img.onerror = function() {
            content.innerHTML = `
                <div class="media-modal-content">
                    <div style="text-align: center; color: #666; padding: 40px;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 20px; color: #ff6b6b;"></i>
                        <p>Không thể tải ảnh</p>
                    </div>
                </div>
            `;
        };
        img.src = url;
    }

    // Navigate giữa các ảnh
    function navigateImage(direction) {
        if (currentData.length <= 1) return;

        currentImageIndex += direction;

        // Loop around
        if (currentImageIndex >= currentData.length) {
            currentImageIndex = 0;
        } else if (currentImageIndex < 0) {
            currentImageIndex = currentData.length - 1;
        }

        const newUrl = currentData[currentImageIndex].url;
        viewMedia(newUrl, currentImageIndex);
    }

    // Close modal
    function closeModal(modalId) {
        const modal = safeGetElement(modalId);
        if (modal) {
            modal.style.animation = 'fadeOut 0.3s ease';
            setTimeout(() => {
                modal.style.display = 'none';
                modal.style.animation = '';
            }, 300);
        }
    }

    // Close modal khi click outside
    function closeModalOnOutside(event) {
        if (event.target === event.currentTarget) {
            closeModal('mediaModal');
        }
    }

    // Thêm keyboard support để đóng modal bằng ESC và navigation
    document.addEventListener('keydown', function(event) {
        const mediaModal = safeGetElement('mediaModal');
        const welcomeModal = safeGetElement('welcomeModal');

        if (event.key === 'Escape') {
            if (mediaModal && mediaModal.style.display === 'flex') {
                closeModal('mediaModal');
            } else if (welcomeModal && welcomeModal.style.display === 'flex') {
                closeModal('welcomeModal');
            }
        }

        // Navigation với arrow keys khi modal ảnh đang mở
        if (mediaModal && mediaModal.style.display === 'flex') {
            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                navigateImage(-1);
            } else if (event.key === 'ArrowRight') {
                event.preventDefault();
                navigateImage(1);
            }
        }
    });

    // Initialize
    document.addEventListener('DOMContentLoaded', async function() {
        const requiredElements = ['mainTitle', 'subtitle', 'contentArea'];
        const missingElements = requiredElements.filter(id => !safeGetElement(id));

        if (missingElements.length > 0) {
            console.error('Missing required elements:', missingElements);
            return;
        }

        // Hiển thị modal chào mừng
        const welcomeModal = safeGetElement('welcomeModal');
        if (welcomeModal) {
            welcomeModal.style.display = 'flex';
        }

        // Set active button for Khai Vị
        document.querySelectorAll('.menu-button').forEach(button => {
            button.classList.remove('active');
        });
        document.querySelector('.menu-button[onclick="selectContent(\'khai-vi\')"]')?.classList.add('active');

        // Load default content - Khai Vị
        await loadData('khai-vi');
    });
</script>
</body>
</html>