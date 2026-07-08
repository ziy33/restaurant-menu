<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>卤肉 重庆特色 - 菜单</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=ZCOOL+XiaoWei&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'ZCOOL XiaoWei', serif;
            background: linear-gradient(135deg, #1a0a0a 0%, #2d1515 50%, #1a0a0a 100%);
            min-height: 100vh;
            color: #f0e6d2;
            line-height: 1.6;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, rgba(139, 0, 0, 0.9) 0%, rgba(92, 0, 0, 0.9) 100%),
                        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="%23daa520" stroke-width="0.5" opacity="0.3"/></svg>');
            padding: 80px 20px 60px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(218, 165, 32, 0.1) 0%, transparent 70%);
            animation: shimmer 15s linear infinite;
        }

        @keyframes shimmer {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-size: clamp(32px, 6vw, 56px);
            color: #daa520;
            text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.7),
                         0 0 30px rgba(218, 165, 32, 0.5);
            margin-bottom: 15px;
            letter-spacing: 8px;
            animation: fadeInDown 1s ease-out;
        }

        .hero p {
            font-size: clamp(14px, 2vw, 18px);
            color: #f0d0a0;
            letter-spacing: 4px;
            animation: fadeInUp 1s ease-out 0.3s both;
        }

        .hero .subtitle-id {
            font-size: clamp(11px, 1.5vw, 14px);
            color: #c9b896;
            letter-spacing: 2px;
            font-style: italic;
            margin-top: 5px;
            animation: fadeInUp 1s ease-out 0.5s both;
        }

        .decorative-line {
            width: 200px;
            height: 3px;
            background: linear-gradient(90deg, transparent, #daa520, transparent);
            margin: 25px auto;
            animation: scaleX 1s ease-out 0.6s both;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

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

        @keyframes scaleX {
            from { transform: scaleX(0); }
            to { transform: scaleX(1); }
        }

        /* Main Menu */
        .menu {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .category-section {
            margin-bottom: 60px;
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease-out;
        }

        .category-section.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .category-header {
            text-align: center;
            margin-bottom: 35px;
            position: relative;
        }

        .category-header h2 {
            font-size: clamp(22px, 3vw, 32px);
            color: #daa520;
            display: inline-block;
            padding: 0 30px;
            position: relative;
            background: linear-gradient(135deg, #1a0a0a 0%, #2d1515 100%);
            border: 2px solid #daa520;
            border-radius: 50px;
            box-shadow: 0 4px 20px rgba(218, 165, 32, 0.3);
        }

        .category-header h2::before,
        .category-header h2::after {
            content: '◆';
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: #daa520;
            font-size: 12px;
        }

        .category-header h2::before {
            left: 15px;
        }

        .category-header h2::after {
            right: 15px;
        }

        .dish-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }

        @media (max-width: 1024px) {
            .dish-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .dish-grid {
                grid-template-columns: 1fr;
            }
        }

        .dish-card {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.02) 100%);
            border: 1px solid rgba(218, 165, 32, 0.3);
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.4s ease;
            backdrop-filter: blur(10px);
        }

        .dish-card:hover {
            transform: translateY(-8px) scale(1.02);
            border-color: rgba(218, 165, 32, 0.6);
            box-shadow: 
                0 15px 40px rgba(0, 0, 0, 0.4),
                0 0 30px rgba(218, 165, 32, 0.2);
        }

        .dish-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: linear-gradient(145deg, #2a1515 0%, #1a0a0a 100%);
            position: relative;
            overflow: hidden;
        }

        .dish-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .dish-card:hover .dish-image img {
            transform: scale(1.1);
        }

        .dish-placeholder {
            width: 100%;
            height: 200px;
            background: linear-gradient(145deg, #2a1515 0%, #1a0a0a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 14px;
            position: relative;
            overflow: hidden;
        }

        .dish-placeholder::before {
            content: '';
            position: absolute;
            width: 80px;
            height: 80px;
            border: 2px solid rgba(218, 165, 32, 0.2);
            border-radius: 50%;
        }

        .dish-placeholder::after {
            content: '';
            position: absolute;
            width: 50px;
            height: 50px;
            border: 2px solid rgba(218, 165, 32, 0.3);
            border-radius: 50%;
        }

        .dish-info {
            padding: 20px;
            text-align: center;
        }

        .dish-name {
            font-size: clamp(16px, 2vw, 20px);
            color: #f0e6d2;
            margin-bottom: 4px;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .dish-name-id {
            font-size: clamp(12px, 1.5vw, 14px);
            color: #c9b896;
            margin-bottom: 10px;
            font-weight: normal;
            letter-spacing: 1px;
            font-style: italic;
        }

        .category-name-id {
            display: block;
            font-size: clamp(12px, 1.5vw, 16px);
            color: #c9b896;
            font-weight: normal;
            letter-spacing: 1px;
            margin-top: 4px;
            font-style: italic;
        }

        .dish-price {
            font-size: clamp(18px, 2.5vw, 24px);
            color: #daa520;
            font-weight: bold;
            text-shadow: 0 0 10px rgba(218, 165, 32, 0.5);
        }

        /* Loading State */
        .loading {
            text-align: center;
            padding: 60px 20px;
            color: #daa520;
            font-size: 18px;
        }

        .loading::after {
            content: '';
            display: inline-block;
            width: 24px;
            height: 24px;
            border: 3px solid rgba(218, 165, 32, 0.3);
            border-top-color: #daa520;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 10px;
            vertical-align: middle;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Error State */
        .error {
            text-align: center;
            padding: 60px 20px;
            color: #ff6666;
            font-size: 16px;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 40px 20px;
            color: #888;
            font-size: 14px;
            border-top: 1px solid rgba(218, 165, 32, 0.2);
            margin-top: 60px;
        }

        .footer a {
            color: #daa520;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        /* Scroll Progress Indicator */
        .scroll-progress {
            position: fixed;
            top: 0;
            left: 0;
            height: 4px;
            background: linear-gradient(90deg, #daa520, #ffd700);
            z-index: 1000;
            transition: width 0.1s ease;
        }
    </style>
</head>
<body>
    <!-- Scroll Progress -->
    <div class="scroll-progress" id="scrollProgress"></div>

    <!-- Hero Section -->
    <header class="hero">
        <div class="hero-content">
            <h1 id="restaurantName">卤肉 重庆特色</h1>
            <div class="decorative-line"></div>
            <p id="restaurantSubtitle">正宗重庆风味 · 传统工艺</p>
            <p class="subtitle-id" id="restaurantSubtitleId">Rasa Autentik Chongqing · Tradisi Turun-Temurun</p>
        </div>
    </header>

    <!-- Main Menu -->
    <main class="menu" id="menuContainer">
        <div class="loading">菜单加载中</div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>© 2026 卤肉 重庆特色 · 版权所有</p>
        <p style="margin-top: 10px;">
            <a href="admin/login.php">管理后台</a>
        </p>
    </footer>

    <script>
        // Menu data
        let menuData = null;

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadMenuData();
            setupScrollProgress();
        });

        // Load menu data - try fetch first, then PHP fallback
        async function loadMenuData() {
            const container = document.getElementById('menuContainer');
            try {
                // Method 1: Fetch from public API
                const response = await fetch('admin/api.php?action=list');
                if (response.ok) {
                    const result = await response.json();
                    if (result.success && result.data) {
                        menuData = result.data;
                        renderMenu();
                        return;
                    }
                }
            } catch (e) {
                // Fetch failed, try PHP embedded fallback
            }

            try {
                // Method 2: Fetch JSON file directly
                const response2 = await fetch('data/menu.json');
                if (response2.ok) {
                    menuData = await response2.json();
                    renderMenu();
                    return;
                }
            } catch (e2) {
                // Both methods failed
            }

            try {
                // Method 3: PHP embedded data
                const menuJson = <?php
                    $jsonFile = __DIR__ . '/data/menu.json';
                    if (file_exists($jsonFile)) {
                        $content = file_get_contents($jsonFile);
                        echo $content ?: '{}';
                    } else {
                        echo '{}';
                    }
                ?>;
                if (menuJson && menuJson.categories) {
                    menuData = menuJson;
                    renderMenu();
                    return;
                }
            } catch (e3) {}

            container.innerHTML = '<div class="error">菜单加载失败，请确保通过 PHP 服务器访问此页面</div>';
        }

        // Render menu
        function renderMenu() {
            if (!menuData || !menuData.categories || !menuData.dishes) {
                document.getElementById('menuContainer').innerHTML = 
                    '<div class="error">菜单数据格式错误</div>';
                return;
            }

            // Update restaurant info
            if (menuData.restaurant) {
                document.getElementById('restaurantName').textContent = menuData.restaurant.name || '卤肉 重庆特色';
                document.getElementById('restaurantSubtitle').textContent = menuData.restaurant.subtitle || '正宗重庆风味 · 传统工艺';
                var subId = document.getElementById('restaurantSubtitleId');
                if (subId && menuData.restaurant.subtitle_id) {
                    subId.textContent = menuData.restaurant.subtitle_id;
                }
            }

            const container = document.getElementById('menuContainer');
            
            // Sort categories by sort value
            const sortedCategories = [...menuData.categories].sort((a, b) => a.sort - b.sort);

            let html = '';
            let hasContent = false;

            sortedCategories.forEach(category => {
                // Get dishes for this category, sorted by sort value
                const categoryDishes = menuData.dishes
                    .filter(dish => dish.category_id === category.id)
                    .sort((a, b) => a.sort - b.sort);

                // Skip empty categories
                if (categoryDishes.length === 0) return;

                hasContent = true;

                html += `
                    <section class="category-section" data-category="${escapeHtml(category.id)}">
                        <div class="category-header">
                            <h2>${escapeHtml(category.name)}${category.name_id ? '<span class="category-name-id">' + escapeHtml(category.name_id) + '</span>' : ''}</h2>
                        </div>
                        <div class="dish-grid">
                            ${categoryDishes.map(dish => renderDishCard(dish)).join('')}
                        </div>
                    </section>
                `;
            });

            if (!hasContent) {
                html = '<div class="error">暂无菜品信息</div>';
            }

            container.innerHTML = html;

            // Setup scroll observer AFTER rendering
            setupScrollObserver();

            // Fallback: make all sections visible after 500ms
            setTimeout(function() {
                document.querySelectorAll('.category-section').forEach(function(section) {
                    if (!section.classList.contains('visible')) {
                        section.classList.add('visible');
                    }
                });
            }, 500);
        }

        // Render single dish card
        function renderDishCard(dish) {
            const imageUrl = dish.image ? 'uploads/' + dish.image : '';
            const priceFormatted = formatPrice(dish.price);

            let imageHtml;
            if (imageUrl) {
                imageHtml = '<div class="dish-image"><img src="' + imageUrl + '" alt="' + escapeHtml(dish.name) + '" onerror="this.parentElement.innerHTML=\'<div class=dish-placeholder>图片加载失败</div>\'"></div>';
            } else {
                imageHtml = '<div class="dish-placeholder">暂无图片</div>';
            }

            return '<article class="dish-card">' +
                imageHtml +
                '<div class="dish-info">' +
                    '<h3 class="dish-name">' + escapeHtml(dish.name) + '</h3>' +
                    (dish.name_id ? '<p class="dish-name-id">' + escapeHtml(dish.name_id) + '</p>' : '') +
                    '<div class="dish-price">' + priceFormatted + '</div>' +
                '</div>' +
            '</article>';
        }

        // Format price as Indonesian Rupiah
        function formatPrice(price) {
            return 'Rp' + Number(price).toLocaleString('id-ID');
        }

        // Escape HTML
        function escapeHtml(text) {
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Setup scroll progress indicator
        function setupScrollProgress() {
            var progressBar = document.getElementById('scrollProgress');
            
            window.addEventListener('scroll', function() {
                var scrollTop = window.scrollY;
                var docHeight = document.documentElement.scrollHeight - window.innerHeight;
                var scrollPercent = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
                progressBar.style.width = scrollPercent + '%';
            });
        }

        // Setup scroll observer for fade-in animations
        function setupScrollObserver() {
            if (!('IntersectionObserver' in window)) {
                // Fallback: show all sections immediately
                document.querySelectorAll('.category-section').forEach(function(section) {
                    section.classList.add('visible');
                });
                return;
            }

            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, {
                threshold: 0.05,
                rootMargin: '0px 0px -20px 0px'
            });

            document.querySelectorAll('.category-section').forEach(function(section) {
                observer.observe(section);
            });
        }
    </script>
</body>
</html>
