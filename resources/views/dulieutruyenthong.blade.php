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
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab, #667eea, #764ba2);
            background-size: 400% 400%;
            animation: gradientShift 8s ease infinite;
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

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
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
            background: linear-gradient(-45deg, #ffffff, #ffdd59, #ffd700, #32cd32, #00ff00, #228b22, #ff8c00, #ffffff);
            background-size: 400% 400%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: fadeIn 1s ease-out 0.3s both, gradientText 6s ease infinite;
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

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-toggle {
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

        .dropdown-toggle:hover {
            color: white;
            transform: translateY(-2px);
        }

        .dropdown-toggle.active {
            background: rgba(255, 255, 255, 0.95);
            color: #1f2937;
            font-weight: 600;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            margin-top: 5px;
            z-index: 1000;
            max-height: 300px;
            overflow-y: auto;
            min-width: 200px;
            transform: translateZ(0);
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-item {
            display: block;
            width: 100%;
            padding: 12px 16px;
            color: #1f2937;
            text-decoration: none;
            background: transparent;
            border: none;
            text-align: left;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.85rem;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }

        .dropdown-item:last-child {
            border-bottom: none;
        }

        .dropdown-item:hover {
            background: rgba(0, 0, 0, 0.1);
            color: #000;
        }

        .dropdown-item.all-items {
            font-weight: 600;
            background: rgba(0, 0, 0, 0.05);
            color: #2563eb;
        }

        .main-title {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 12px;
            letter-spacing: -0.02em;
            background: linear-gradient(-45deg, #ffffff, #ffdd59, #ffd700, #32cd32, #00ff00, #228b22, #ff8c00, #ffffff);
            background-size: 400% 400%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: fadeIn 1s ease-out 0.4s both, gradientText 6s ease infinite;
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

        .content-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            margin-bottom: 32px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            z-index: 20;
        }

        .content-table th, .content-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            font-size: 0.85rem;
            min-width: 80px;
        }

        .content-table th {
            background: rgba(0, 0, 0, 0.05);
            font-weight: 600;
            color: #1f2937;
        }

        .content-table td {
            color: #374151;
            vertical-align: top;
        }

        .content-table tr:hover {
            background: rgba(0, 0, 0, 0.03);
        }

        .media-button {
            padding: 8px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(45deg, #4ecdc4, #45b7d1);
            color: white;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 2px;
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .media-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .media-button.view {
            background: linear-gradient(45deg, #4ecdc4, #45b7d1);
        }

        .media-button.download {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
        }

        .media-button.copy {
            background: linear-gradient(45deg, #10b981, #059669);
        }

        .media-button i {
            font-size: 1rem;
        }

        .media-button.downloading {
            background: linear-gradient(45deg, #9ca3af, #6b7280);
            cursor: not-allowed;
        }

        .media-button.downloading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            border: 2px solid #ffffff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .media-preview {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            object-fit: cover;
        }

        .type-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .type-badge.image {
            background: rgba(34, 197, 94, 0.1);
            color: #16a34a;
        }

        .type-badge.video {
            background: rgba(59, 130, 246, 0.1);
            color: #2563eb;
        }

        .loading {
            text-align: center;
            padding: 32px;
            color: white;
            font-size: 1rem;
        }

        .error {
            text-align: center;
            padding: 32px;
            color: #ff6b6b;
            font-size: 1rem;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 500;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 16px;
            max-width: 90vw;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        .close-button {
            position: absolute;
            top: 12px;
            right: 12px;
            font-size: 1.2rem;
            cursor: pointer;
            color: #1f2937;
        }

        .modal-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .modal-table th, .modal-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            font-size: 0.8rem;
        }

        .modal-table th {
            background: rgba(0, 0, 0, 0.05);
            font-weight: 600;
        }

        .media-modal-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .media-modal-content img {
            max-width: 100%;
            max-height: 60vh;
            border-radius: 8px;
        }

        .media-modal-content video {
            max-width: 100%;
            max-height: 60vh;
            border-radius: 8px;
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

        .download-status {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            z-index: 1000;
            min-width: 200px;
            text-align: center;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
        }

        .download-status.show {
            opacity: 1;
            transform: translateX(0);
        }

        .download-status.success {
            background: rgba(34, 197, 94, 0.9);
        }

        .download-status.error {
            background: rgba(239, 68, 68, 0.9);
        }

        @media (max-width: 768px) {
            .dropdown-menu {
                position: relative;
                top: 0;
                margin-top: 8px;
                box-shadow: none;
                border: 1px solid rgba(0,0,0,0.1);
            }

            .dropdown-toggle {
                min-width: 100px;
                font-size: 0.8rem;
                padding: 8px 12px;
            }

            .content-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            .content-table th, .content-table td {
                padding: 8px;
                font-size: 0.75rem;
                min-width: 80px;
            }

            .media-preview {
                width: 80px;
                height: 80px;
            }

            .media-button {
                width: 32px;
                height: 32px;
                padding: 6px;
            }

            .media-button i {
                font-size: 0.9rem;
            }

            .download-status {
                top: 10px;
                right: 10px;
                left: 10px;
                min-width: auto;
                font-size: 0.9rem;
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

    <h1 class="header-title">🌟 Hệ Thống Quản Lý Nội Dung</h1>

    <div class="menu-section">
        <div class="tab-menu">
            <div class="dropdown">
                <button class="dropdown-toggle active" onclick="toggleDropdown('posts')">
                    Nội Dung Bài Viết <i class="fas fa-chevron-down"></i>
                </button>
                <div class="dropdown-menu" id="posts-dropdown">
                    <button class="dropdown-item all-items" onclick="selectContent('posts', 'all', 'Tất Cả')">Tất Cả Bài Viết</button>
                </div>
            </div>

            <div class="dropdown">
                <button class="dropdown-toggle" onclick="toggleDropdown('images')">
                    Kho Ảnh <i class="fas fa-chevron-down"></i>
                </button>
                <div class="dropdown-menu" id="images-dropdown">
                    <button class="dropdown-item all-items" onclick="selectContent('images', 'all', 'Tất Cả')">🔥 Tất Cả Ảnh</button>
                </div>
            </div>

            <div class="dropdown">
                <button class="dropdown-toggle" onclick="toggleDropdown('videos')">
                    Kho Video <i class="fas fa-chevron-down"></i>
                </button>
                <div class="dropdown-menu" id="videos-dropdown">
                    <button class="dropdown-item all-items" onclick="selectContent('videos', 'all', 'Tất Cả')">🔥 Tất Cả Video</button>
                </div>
            </div>
        </div>
    </div>

    <h2 class="main-title" id="mainTitle">Nội Dung Bài Viết</h2>
    <p class="subtitle" id="subtitle">Quản lý các bài viết và nội dung</p>

    <div id="contentArea">
        <div class="loading">⏳ Đang tải dữ liệu...</div>
    </div>

    <!-- Modal cho chi tiết bài viết -->
    <div class="modal" id="postModal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('postModal')">&times;</span>
            <h2 id="modalTitle"></h2>
            <div id="modalContent"></div>
        </div>
    </div>

    <!-- Modal cho xem media -->
    <div class="modal" id="mediaModal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('mediaModal')">&times;</span>
            <div id="mediaModalContent"></div>
        </div>
    </div>

    <!-- Download status notification -->
    <div class="download-status" id="downloadStatus"></div>

    <div class="footer">
        <p class="footer-text">© 2025 Làng Du Lịch Sinh Thái Ông Đề. Tất cả quyền được bảo lưu.</p>
        <p class="footer-text">Công Ty TNHH Làng Du Lịch Sinh Thái Ông Đề.</p>
        <p class="footer-text">Địa chỉ: Số 168-AB1, Đường Xuân Thuỷ, Khu Dân Cư Hồng Phát, Phường An Bình, Thành Phố Cần Thơ, Việt Nam.</p>
        <p class="footer-text">Mã Số Thuế: 1801218923 | Hotline: 0931 852 113</p>
    </div>
</div>

<script>
    // Global variables
    let categories = [];
    let currentType = 'posts';
    let currentCategory = 'all';
    let currentData = [];

    // API endpoints
    const API_BASE_URL = window.location.origin;

    // Utility functions
    function shuffleArray(array) {
        const shuffled = [...array];
        for (let i = shuffled.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
        }
        return shuffled;
    }

    function showDownloadStatus(message, type = 'info') {
        const statusEl = document.getElementById('downloadStatus');
        if (statusEl) {
            statusEl.textContent = message;
            statusEl.className = `download-status show ${type}`;

            setTimeout(() => {
                statusEl.classList.remove('show');
            }, 3000);
        }
    }

    // Safe element access
    function safeGetElement(id) {
        const element = document.getElementById(id);
        if (!element) {
            console.warn(`Element with ID '${id}' not found`);
        }
        return element;
    }

    // Load categories
    async function loadCategories() {
        try {
            const response = await fetch(`${API_BASE_URL}/api/categories`);
            const data = await response.json();

            if (data.success && data.data) {
                categories = data.data;
                populateDropdowns();
            }
        } catch (error) {
            console.log('Categories API not available:', error);
        }
    }

    // Populate dropdowns with categories
    function populateDropdowns() {
        const dropdowns = ['posts', 'images', 'videos'];

        dropdowns.forEach(type => {
            const dropdown = safeGetElement(`${type}-dropdown`);
            if (!dropdown) return;

            // Remove old items (except "All")
            const oldItems = dropdown.querySelectorAll('.dropdown-item:not(.all-items)');
            oldItems.forEach(item => item.remove());

            // Add categories
            categories.forEach(category => {
                const item = document.createElement('button');
                item.className = 'dropdown-item';
                const categoryName = category.ten_danh_muc || category.name || 'Unnamed Category';
                item.textContent = `📂 ${categoryName}`;
                item.onclick = () => selectContent(type, category.id, categoryName);
                dropdown.appendChild(item);
            });
        });
    }

    // Toggle dropdown
    function toggleDropdown(type) {
        // Close all other dropdowns
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            if (menu.id !== `${type}-dropdown`) {
                menu.classList.remove('show');
            }
        });

        // Toggle current dropdown
        const dropdown = safeGetElement(`${type}-dropdown`);
        if (dropdown) {
            dropdown.classList.toggle('show');
        }

        // Update active state
        document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
            toggle.classList.remove('active');
        });

        if (event && event.target) {
            event.target.classList.add('active');
        }
    }

    // Select content with category
    async function selectContent(type, categoryId = 'all', categoryName = 'Tất Cả') {
        currentType = type;
        currentCategory = categoryId;

        // Close dropdowns
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.remove('show');
        });

        // Update titles
        const typeNames = {
            posts: 'Nội Dung Bài Viết',
            images: 'Kho Ảnh',
            videos: 'Kho Video'
        };

        const mainTitle = safeGetElement('mainTitle');
        const subtitle = safeGetElement('subtitle');

        if (mainTitle) {
            mainTitle.textContent = `${typeNames[type]} - ${categoryName}`;
        }

        if (subtitle) {
            subtitle.textContent = `Quản lý ${type === 'posts' ? 'bài viết' : type === 'images' ? 'ảnh' : 'video'} thuộc danh mục: ${categoryName}`;
        }

        // Load data
        await loadData(type, categoryId);
    }

    // Load data with category filter
    async function loadData(type, categoryId = 'all') {
        const contentArea = safeGetElement('contentArea');
        if (!contentArea) return;

        try {
            contentArea.innerHTML = '<div class="loading">🎲 Đang tải dữ liệu ngẫu nhiên...</div>';

            let url;
            if (categoryId === 'all') {
                if (type === 'posts') {
                    url = `${API_BASE_URL}/api/data-posts`;
                } else {
                    url = `${API_BASE_URL}/api/images-data?type=${type === 'images' ? 'image' : 'video'}`;
                }
            } else {
                url = `${API_BASE_URL}/api/categories/${categoryId}/${type}`;
            }

            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (!data.success || !data.data || data.data.length === 0) {
                contentArea.innerHTML = '<div class="error">📭 Không có dữ liệu để hiển thị</div>';
                currentData = [];
                return;
            }

            currentData = data.data;
            renderTable(currentData, type);

        } catch (error) {
            console.error('Error loading data:', error);
            contentArea.innerHTML = `<div class="error">❌ Lỗi tải dữ liệu: ${error.message}</div>`;
        }
    }

    // Render table
    function renderTable(items, type) {
        const contentArea = safeGetElement('contentArea');
        if (!contentArea) return;

        const shuffledItems = shuffleArray(items);
        let columns, rows;

        if (type === 'posts') {
            columns = ['Tiêu Đề', 'Loại', 'Nội Dung', 'Hành Động'];
            rows = shuffledItems.map(item => `
                <tr>
                    <td><strong>${item.title && item.title.length > 13 ? item.title.substring(0, 13) + '...' : (item.title || 'Không có tiêu đề')}</strong></td>
                    <td><span class="type-badge ${item.type}">${item.type === 'image' ? 'Ảnh' : 'Video'}</span></td>
                    <td>${item.content ? item.content.substring(0, 100) + '...' : 'Không có nội dung'}</td>
                    <td>
                        <button class="media-button copy" onclick="copyContent('${encodeURIComponent(item.content || '')}')"><i class="fas fa-clipboard"></i></button>
                        <button class="media-button view" onclick="viewPost(${item.id}, '${encodeURIComponent(item.title || '')}', '${encodeURIComponent(item.content || '')}', '${item.type}', '${formatDate(item.created_at)}')"><i class="fas fa-eye"></i></button>
                    </td>
                </tr>
            `).join('');
        } else {
            columns = ['Preview', 'Loại', 'Ngày Tạo', 'Hành Động'];
            rows = shuffledItems.map(item => `
                <tr>
                    <td>
                        ${item.url ?
                (item.type === 'image' ?
                        `<img src="${item.url}" alt="Preview" class="media-preview" onerror="this.style.display='none'">` :
                        `<div class="video-preview-container">
                            <video class="media-preview" muted preload="metadata" onclick="this.play()" onloadeddata="this.style.display='block'">
                                <source src="${item.url}" type="video/mp4">
                                <source src="${item.url}" type="video/webm">
                                <source src="${item.url}" type="video/mov">
                                Video không được hỗ trợ
                            </video>
                            <div class="video-play-overlay" onclick="playVideo(this)">
                                <i class="fas fa-play-circle"></i>
                            </div>
                        </div>`
                ) :
                'Không có file'
            }
                    </td>
                    <td><span class="type-badge ${item.type}">${item.type === 'image' ? 'Ảnh' : 'Video'}</span></td>
                    <td>${formatDate(item.created_at)}</td>
                    <td>
                        ${item.url ? `
                            <button class="media-button view" onclick="viewMedia('${item.url}', '${item.type}')"><i class="fas fa-eye"></i></button>
                            <button class="media-button download" onclick="downloadMedia('${item.url}', this)"><i class="fas fa-download"></i></button>
                        ` : 'Không có tệp'}
                    </td>
                </tr>
            `).join('');
        }

        contentArea.innerHTML = `
            <table class="content-table">
                <thead>
                    <tr>${columns.map(col => `<th>${col}</th>`).join('')}</tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        `;
    }

    // Format date
    function formatDate(dateString) {
        if (!dateString) return 'Không có';
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'});
    }

    // Copy content
    function copyContent(content) {
        const decodedContent = decodeURIComponent(content);

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(decodedContent).then(() => {
                showDownloadStatus('📋 Đã sao chép nội dung!', 'success');
            }).catch(() => {
                // Fallback for older browsers
                fallbackCopyTextToClipboard(decodedContent);
            });
        } else {
            fallbackCopyTextToClipboard(decodedContent);
        }
    }

    // Fallback copy function
    function fallbackCopyTextToClipboard(text) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            const successful = document.execCommand('copy');
            if (successful) {
                showDownloadStatus('📋 Đã sao chép nội dung!', 'success');
            } else {
                showDownloadStatus('❌ Không thể sao chép', 'error');
            }
        } catch (err) {
            showDownloadStatus('❌ Lỗi khi sao chép', 'error');
        }

        document.body.removeChild(textArea);
    }

    // View post details
    function viewPost(postId, title, content, type, created_at) {
        const modal = safeGetElement('postModal');
        const modalTitle = safeGetElement('modalTitle');
        const modalContent = safeGetElement('modalContent');

        if (!modal || !modalTitle || !modalContent) return;

        modalTitle.textContent = decodeURIComponent(title);
        modalContent.innerHTML = `
            <table class="modal-table">
                <tr>
                    <th>ID</th>
                    <td>${postId}</td>
                </tr>
                <tr>
                    <th>Tiêu Đề</th>
                    <td>${decodeURIComponent(title)}</td>
                </tr>
                <tr>
                    <th>Loại</th>
                    <td><span class="type-badge ${type}">${type === 'image' ? 'Ảnh' : 'Video'}</span></td>
                </tr>
                <tr>
                    <th>Nội Dung</th>
                    <td>${decodeURIComponent(content) || 'Không có nội dung'}</td>
                </tr>
                <tr>
                    <th>Ngày Tạo</th>
                    <td>${created_at}</td>
                </tr>
            </table>
            <div style="margin-top: 15px;">
                <button class="media-button copy" onclick="copyContent('${content}')"><i class="fas fa-clipboard"></i> Sao chép</button>
            </div>
        `;
        modal.style.display = 'flex';
    }

    // View media
    function viewMedia(url, type) {
        const modal = safeGetElement('mediaModal');
        const content = safeGetElement('mediaModalContent');

        if (!modal || !content) return;

        if (type === 'image') {
            content.innerHTML = `
                <div class="media-modal-content">
                    <img src="${url}" alt="Image Preview">
                    <div>
                        <button class="media-button download" onclick="downloadMedia('${url}', this)"><i class="fas fa-download"></i> Tải về</button>
                    </div>
                </div>
            `;
        } else {
            content.innerHTML = `
                <div class="media-modal-content">
                    <video controls autoplay>
                        <source src="${url}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    <div>
                        <button class="media-button download" onclick="downloadMedia('${url}', this)"><i class="fas fa-download"></i> Tải về</button>
                    </div>
                </div>
            `;
        }
        modal.style.display = 'flex';
    }

    // Download media - improved for mobile and sandbox environment
    async function downloadMedia(url, buttonElement = null) {
        if (buttonElement) {
            buttonElement.classList.add('downloading');
            buttonElement.innerHTML = '';
        }

        // Check if mobile
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

        try {
            if (isMobile) {
                // Mobile: Show instructions modal
                showMobileDownloadModal(url);
                showDownloadStatus('📱 Xem hướng dẫn tải xuống cho mobile', 'info');
            } else {
                // Desktop: Try direct download with fallback
                showDownloadStatus('⏬ Đang tải xuống...', 'info');

                try {
                    // Try fetch first
                    const response = await fetch(url, {
                        mode: 'cors',
                        credentials: 'omit'
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const blob = await response.blob();
                    const downloadUrl = window.URL.createObjectURL(blob);

                    const link = document.createElement('a');
                    link.href = downloadUrl;

                    // Create filename from URL
                    const fileName = url.split('/').pop() || 'download';
                    link.download = fileName;

                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    // Cleanup
                    setTimeout(() => window.URL.revokeObjectURL(downloadUrl), 100);
                    showDownloadStatus('✅ Tải xuống thành công!', 'success');

                } catch (fetchError) {
                    // Fallback to opening in new tab
                    throw fetchError;
                }
            }

        } catch (error) {
            console.error('Download error:', error);

            // Ultimate fallback: open in new tab
            showDownloadStatus('🔗 Đang mở trong tab mới...', 'info');
            try {
                const link = document.createElement('a');
                link.href = url;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';

                // Add download attribute
                const fileName = url.split('/').pop() || 'download';
                link.download = fileName;

                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                if (isMobile) {
                    showDownloadStatus('📱 Đã mở file. Nhấn giữ ảnh/video và chọn "Lưu" để tải xuống!', 'success');
                } else {
                    showDownloadStatus('🔗 Đã mở file trong tab mới', 'success');
                }
            } catch (fallbackError) {
                showDownloadStatus('❌ Không thể tải xuống file', 'error');
            }
        } finally {
            if (buttonElement) {
                buttonElement.classList.remove('downloading');
                buttonElement.innerHTML = '<i class="fas fa-download"></i>';
            }
        }
    }

    // Mobile download instructions modal
    function showMobileDownloadModal(url) {
        const modalHtml = `
            <div class="modal" id="mobileDownloadModal" style="display: flex;">
                <div class="modal-content">
                    <span class="close-button" onclick="closeModal('mobileDownloadModal')">&times;</span>
                    <h2>📱 Hướng dẫn tải xuống trên Mobile</h2>
                    <div style="text-align: left; padding: 20px; line-height: 1.8;">
                        <p><strong>Bước 1:</strong> Nhấn nút "Mở File" bên dưới</p>
                        <p><strong>Bước 2:</strong> File sẽ mở trong tab mới</p>
                        <p><strong>Bước 3:</strong> Nhấn giữ vào ảnh/video</p>
                        <p><strong>Bước 4:</strong> Chọn "Lưu hình ảnh" hoặc "Tải xuống"</p>
                        <hr style="margin: 15px 0;">
                        <div style="text-align: center;">
                            <button class="media-button" style="width: auto; padding: 12px 24px; margin: 10px;" onclick="openFileInNewTab('${url}')">
                                <i class="fas fa-external-link-alt"></i> Mở File
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove old modal if exists
        const oldModal = document.getElementById('mobileDownloadModal');
        if (oldModal) oldModal.remove();

        // Add new modal
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }

    // Open file in new tab
    function openFileInNewTab(url) {
        const link = document.createElement('a');
        link.href = url;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        closeModal('mobileDownloadModal');
        showDownloadStatus('📱 Đã mở file! Nhấn giữ để lưu xuống thiết bị', 'success');
    }

    // Close modal
    function closeModal(modalId) {
        const modal = safeGetElement(modalId);
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // Event listeners
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });

    // Initialize
    document.addEventListener('DOMContentLoaded', async function() {
        // Check required elements
        const requiredElements = ['mainTitle', 'subtitle', 'contentArea'];
        const missingElements = requiredElements.filter(id => !safeGetElement(id));

        if (missingElements.length > 0) {
            console.error('Missing required elements:', missingElements);
            return;
        }

        // Load categories if available
        await loadCategories();

        // Load default content
        await selectContent('posts', 'all', 'Tất Cả');
    });
</script>
</body>
</html>