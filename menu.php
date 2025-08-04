<?php
require_once 'config/config.php';

// Get categories
$stmt = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
$categories = $stmt->fetchAll();

// Get featured menu items
$stmt = $pdo->query("
    SELECT mi.*, c.name as category_name 
    FROM menu_items mi 
    LEFT JOIN categories c ON mi.category_id = c.id 
    WHERE mi.status = 'available' AND mi.is_featured = 1
    ORDER BY mi.name
");
$featured_items = $stmt->fetchAll();

// Get all menu items by category
$menu_by_category = [];
foreach ($categories as $category) {
    $stmt = $pdo->prepare("
        SELECT * FROM menu_items 
        WHERE category_id = ? AND status = 'available' 
        ORDER BY name
    ");
    $stmt->execute([$category['id']]);
    $menu_by_category[$category['id']] = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Sambatan Coffee & Space</title>
    <meta name="description" content="Lihat menu lengkap Sambatan Coffee & Space - Kopi, makanan, dan minuman terbaik">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="assets/sambatanlogo.png">
    
    <style>
        .menu-hero {
            background: linear-gradient(rgba(28, 64, 62, 0.8), rgba(28, 64, 62, 0.8)), url('assets/background.png');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 120px 0 80px;
        }
        
        .category-filter {
            background: white;
            border-radius: 50px;
            padding: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .filter-btn {
            background: transparent;
            border: none;
            padding: 12px 20px;
            border-radius: 40px;
            transition: all 0.3s ease;
            color: #1C403E;
            font-weight: 500;
        }
        
        .filter-btn.active,
        .filter-btn:hover {
            background: #F0B33C;
            color: white;
            transform: translateY(-2px);
        }
        
        .menu-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .menu-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .menu-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        
        .menu-card-body {
            padding: 1.5rem;
        }
        
        .menu-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #F0B33C;
        }
        
        .order-btn {
            background: linear-gradient(45deg, #1C403E, #2a5a56);
            border: none;
            border-radius: 25px;
            color: white;
            padding: 10px 20px;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .order-btn:hover {
            background: linear-gradient(45deg, #F0B33C, #d19d35);
            transform: translateY(-2px);
            color: white;
        }
        
        .featured-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #F0B33C;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 10px;
        }
        
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'layout/navbar.html'; ?>
    
    <!-- Hero Section -->
    <section class="menu-hero">
        <div class="container text-center">
            <h1 class="display-3 fw-bold mb-4" data-aos="fade-up">Menu Kami</h1>
            <p class="lead" data-aos="fade-up" data-aos-delay="200">
                Nikmati berbagai pilihan kopi premium dan makanan lezat
            </p>
        </div>
    </section>
    
    <!-- Menu Section -->
    <section class="py-5" style="background-color: #f8f9fa;">
        <div class="container">
            <!-- Category Filter -->
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8">
                    <div class="category-filter text-center">
                        <button class="filter-btn active" data-filter="featured">
                            <i class="fas fa-star"></i> Unggulan
                        </button>
                        <button class="filter-btn" data-filter="all">
                            <i class="fas fa-th"></i> Semua
                        </button>
                        <?php foreach ($categories as $category): ?>
                        <button class="filter-btn" data-filter="category-<?= $category['id'] ?>">
                            <?= $category['name'] ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Featured Items -->
            <div id="featured-menu" class="menu-section">
                <h2 class="text-center mb-4" style="color: #1C403E;">Menu Unggulan</h2>
                <div class="row g-4">
                    <?php foreach ($featured_items as $item): ?>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="menu-card position-relative">
                            <div class="featured-badge">
                                <i class="fas fa-star"></i> Unggulan
                            </div>
                            <?php if ($item['image']): ?>
                                <img src="uploads/<?= $item['image'] ?>" alt="<?= $item['name'] ?>" loading="lazy">
                            <?php else: ?>
                                <img src="assets/kopi.png" alt="<?= $item['name'] ?>" loading="lazy">
                            <?php endif; ?>
                            <div class="menu-card-body">
                                <h5 class="card-title" style="color: #1C403E;"><?= $item['name'] ?></h5>
                                <p class="card-text text-muted small"><?= $item['description'] ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="menu-price"><?= formatPrice($item['price']) ?></span>
                                    <span class="badge bg-success"><?= $item['category_name'] ?></span>
                                </div>
                                <button class="order-btn mt-3" onclick="orderItem('<?= $item['name'] ?>', '<?= $item['price'] ?>')">
                                    <i class="fas fa-shopping-cart"></i> Pesan
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- All Menu Items -->
            <div id="all-menu" class="menu-section" style="display: none;">
                <h2 class="text-center mb-4" style="color: #1C403E;">Semua Menu</h2>
                <div class="row g-4">
                    <?php
                    $stmt = $pdo->query("
                        SELECT mi.*, c.name as category_name 
                        FROM menu_items mi 
                        LEFT JOIN categories c ON mi.category_id = c.id 
                        WHERE mi.status = 'available'
                        ORDER BY c.name, mi.name
                    ");
                    $all_items = $stmt->fetchAll();
                    
                    foreach ($all_items as $item): ?>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="menu-card">
                            <?php if ($item['is_featured']): ?>
                            <div class="featured-badge">
                                <i class="fas fa-star"></i> Unggulan
                            </div>
                            <?php endif; ?>
                            <?php if ($item['image']): ?>
                                <img src="uploads/<?= $item['image'] ?>" alt="<?= $item['name'] ?>" loading="lazy">
                            <?php else: ?>
                                <img src="assets/kopi.png" alt="<?= $item['name'] ?>" loading="lazy">
                            <?php endif; ?>
                            <div class="menu-card-body">
                                <h5 class="card-title" style="color: #1C403E;"><?= $item['name'] ?></h5>
                                <p class="card-text text-muted small"><?= $item['description'] ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="menu-price"><?= formatPrice($item['price']) ?></span>
                                    <span class="badge bg-info"><?= $item['category_name'] ?></span>
                                </div>
                                <button class="order-btn mt-3" onclick="orderItem('<?= $item['name'] ?>', '<?= $item['price'] ?>')">
                                    <i class="fas fa-shopping-cart"></i> Pesan
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Category Sections -->
            <?php foreach ($categories as $category): ?>
            <div id="category-<?= $category['id'] ?>" class="menu-section" style="display: none;">
                <h2 class="text-center mb-4" style="color: #1C403E;"><?= $category['name'] ?></h2>
                <?php if (!empty($category['description'])): ?>
                <p class="text-center text-muted mb-4"><?= $category['description'] ?></p>
                <?php endif; ?>
                <div class="row g-4">
                    <?php foreach ($menu_by_category[$category['id']] as $item): ?>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="menu-card">
                            <?php if ($item['is_featured']): ?>
                            <div class="featured-badge">
                                <i class="fas fa-star"></i> Unggulan
                            </div>
                            <?php endif; ?>
                            <?php if ($item['image']): ?>
                                <img src="uploads/<?= $item['image'] ?>" alt="<?= $item['name'] ?>" loading="lazy">
                            <?php else: ?>
                                <img src="assets/kopi.png" alt="<?= $item['name'] ?>" loading="lazy">
                            <?php endif; ?>
                            <div class="menu-card-body">
                                <h5 class="card-title" style="color: #1C403E;"><?= $item['name'] ?></h5>
                                <p class="card-text text-muted small"><?= $item['description'] ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="menu-price"><?= formatPrice($item['price']) ?></span>
                                </div>
                                <button class="order-btn mt-3" onclick="orderItem('<?= $item['name'] ?>', '<?= $item['price'] ?>')">
                                    <i class="fas fa-shopping-cart"></i> Pesan
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    
    <!-- WhatsApp Float Button -->
    <a href="https://wa.me/6281234567890?text=Halo, saya ingin memesan dari menu Sambatan" class="whatsapp-float" target="_blank">
        <i class="fab fa-whatsapp"></i>
    </a>
    
    <!-- Back to Top Button -->
    <button class="back-to-top" onclick="scrollToTop()">
        <i class="fas fa-chevron-up"></i>
    </button>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true
        });
        
        // Filter functionality
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all buttons
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                // Add active class to clicked button
                this.classList.add('active');
                
                // Hide all menu sections
                document.querySelectorAll('.menu-section').forEach(section => {
                    section.style.display = 'none';
                });
                
                // Show selected section
                const filter = this.getAttribute('data-filter');
                const targetSection = document.getElementById(filter + '-menu') || document.getElementById(filter);
                if (targetSection) {
                    targetSection.style.display = 'block';
                    // Trigger AOS refresh
                    AOS.refresh();
                }
            });
        });
        
        // Order function
        function orderItem(name, price) {
            const message = `Halo, saya ingin memesan:\n\n${name}\nHarga: ${formatPrice(price)}\n\nTerima kasih!`;
            const whatsappUrl = `https://wa.me/6281234567890?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        }
        
        function formatPrice(price) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(price);
        }
        
        // Back to top functionality
        window.addEventListener('scroll', function() {
            const backToTop = document.querySelector('.back-to-top');
            if (window.pageYOffset > 300) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }
        });
        
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
        
        // Lazy loading images
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.classList.add('loaded');
                        observer.unobserve(img);
                    }
                });
            });
            
            document.querySelectorAll('img[loading="lazy"]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    </script>
</body>
</html>
