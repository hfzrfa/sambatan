<?php
/**
 * Custom Error Page Handler
 * Handles HTTP errors with custom pages
 */

// Get error code from URL parameter
$error_code = isset($_GET['error']) ? intval($_GET['error']) : 404;

// Define error messages and titles
$errors = [
    400 => [
        'title' => 'Bad Request',
        'message' => 'Permintaan tidak valid. Mohon periksa kembali data yang Anda kirim.',
        'icon' => 'fas fa-exclamation-triangle'
    ],
    401 => [
        'title' => 'Unauthorized',
        'message' => 'Anda tidak memiliki akses untuk melihat halaman ini. Silakan login terlebih dahulu.',
        'icon' => 'fas fa-lock'
    ],
    403 => [
        'title' => 'Forbidden',
        'message' => 'Akses ke halaman ini ditolak. Anda tidak memiliki izin untuk mengakses sumber daya ini.',
        'icon' => 'fas fa-ban'
    ],
    404 => [
        'title' => 'Halaman Tidak Ditemukan',
        'message' => 'Maaf, halaman yang Anda cari tidak ditemukan. Mungkin halaman telah dipindahkan atau dihapus.',
        'icon' => 'fas fa-search'
    ],
    500 => [
        'title' => 'Internal Server Error',
        'message' => 'Terjadi kesalahan pada server. Tim kami sedang memperbaiki masalah ini.',
        'icon' => 'fas fa-tools'
    ],
    502 => [
        'title' => 'Bad Gateway',
        'message' => 'Server tidak dapat menghubungi server lain yang diperlukan.',
        'icon' => 'fas fa-server'
    ],
    503 => [
        'title' => 'Service Unavailable',
        'message' => 'Layanan sedang tidak tersedia. Silakan coba lagi dalam beberapa saat.',
        'icon' => 'fas fa-clock'
    ]
];

// Get error info or default to 404
$error_info = $errors[$error_code] ?? $errors[404];

// Set appropriate HTTP status code
http_response_code($error_code);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $error_code; ?> - <?php echo $error_info['title']; ?> | Sambatan Coffee & Space</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/styles.css">
    
    <style>
        .error-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #8B4513 0%, #D2691E 50%, #F4A460 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .error-page::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('/assets/coffee.png') no-repeat center center;
            background-size: cover;
            opacity: 0.1;
            z-index: 1;
        }
        
        .error-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 90%;
            position: relative;
            z-index: 2;
            border: 1px solid rgba(139, 69, 19, 0.2);
        }
        
        .error-code {
            font-size: 6rem;
            font-weight: 900;
            color: #8B4513;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            line-height: 1;
        }
        
        .error-icon {
            font-size: 4rem;
            color: #D2691E;
            margin-bottom: 1.5rem;
            animation: pulse 2s infinite;
        }
        
        .error-title {
            font-size: 2rem;
            font-weight: 700;
            color: #8B4513;
            margin-bottom: 1rem;
        }
        
        .error-message {
            font-size: 1.1rem;
            color: #6c757d;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-coffee {
            background: linear-gradient(45deg, #8B4513, #D2691E);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(139, 69, 19, 0.3);
        }
        
        .btn-coffee:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 69, 19, 0.4);
            color: white;
        }
        
        .btn-outline-coffee {
            border: 2px solid #8B4513;
            color: #8B4513;
            background: transparent;
            padding: 10px 28px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-outline-coffee:hover {
            background: #8B4513;
            color: white;
            transform: translateY(-2px);
        }
        
        .coffee-beans {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 2rem;
            color: rgba(139, 69, 19, 0.3);
            animation: rotate 20s linear infinite;
        }
        
        .coffee-cup {
            position: absolute;
            bottom: 20px;
            left: 20px;
            font-size: 2rem;
            color: rgba(139, 69, 19, 0.3);
            animation: bounce 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        @keyframes bounce {
            0%, 20%, 53%, 80%, 100% { transform: translateY(0); }
            40%, 43% { transform: translateY(-10px); }
            70% { transform: translateY(-5px); }
        }
        
        .search-container {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(139, 69, 19, 0.2);
        }
        
        .search-box {
            max-width: 400px;
            margin: 0 auto;
        }
        
        .search-input {
            border: 2px solid #8B4513;
            border-radius: 25px;
            padding: 10px 20px;
            font-size: 1rem;
        }
        
        .search-input:focus {
            border-color: #D2691E;
            box-shadow: 0 0 0 0.2rem rgba(139, 69, 19, 0.25);
        }
        
        @media (max-width: 768px) {
            .error-container {
                padding: 2rem 1.5rem;
                margin: 1rem;
            }
            
            .error-code {
                font-size: 4rem;
            }
            
            .error-title {
                font-size: 1.5rem;
            }
            
            .error-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-coffee,
            .btn-outline-coffee {
                width: 100%;
                max-width: 250px;
            }
        }
    </style>
</head>
<body>
    <div class="error-page">
        <!-- Decorative elements -->
        <div class="coffee-beans">
            <i class="fas fa-seedling"></i>
        </div>
        <div class="coffee-cup">
            <i class="fas fa-coffee"></i>
        </div>
        
        <div class="error-container">
            <div class="error-code"><?php echo $error_code; ?></div>
            <div class="error-icon">
                <i class="<?php echo $error_info['icon']; ?>"></i>
            </div>
            <h1 class="error-title"><?php echo $error_info['title']; ?></h1>
            <p class="error-message"><?php echo $error_info['message']; ?></p>
            
            <div class="error-actions">
                <a href="/" class="btn-coffee">
                    <i class="fas fa-home me-2"></i>
                    Kembali ke Beranda
                </a>
                
                <?php if ($error_code == 404): ?>
                <a href="/menu" class="btn-outline-coffee">
                    <i class="fas fa-utensils me-2"></i>
                    Lihat Menu
                </a>
                <?php elseif ($error_code == 401): ?>
                <a href="/admin/login" class="btn-outline-coffee">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Login
                </a>
                <?php else: ?>
                <a href="javascript:history.back()" class="btn-outline-coffee">
                    <i class="fas fa-arrow-left me-2"></i>
                    Kembali
                </a>
                <?php endif; ?>
            </div>
            
            <?php if ($error_code == 404): ?>
            <div class="search-container">
                <h5 class="mb-3">Cari apa yang Anda butuhkan:</h5>
                <div class="search-box">
                    <div class="input-group">
                        <input type="text" class="form-control search-input" id="searchInput" 
                               placeholder="Cari menu, promo, atau halaman...">
                        <button class="btn btn-coffee" type="button" onclick="performSearch()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                
                <div class="mt-3">
                    <small class="text-muted">Halaman populer:</small><br>
                    <div class="mt-2">
                        <a href="/menu" class="badge bg-light text-dark me-2 mb-2 text-decoration-none">Menu Kopi</a>
                        <a href="/order" class="badge bg-light text-dark me-2 mb-2 text-decoration-none">Pesan Online</a>
                        <a href="/#tentang" class="badge bg-light text-dark me-2 mb-2 text-decoration-none">Tentang Kami</a>
                        <a href="/#contact" class="badge bg-light text-dark me-2 mb-2 text-decoration-none">Kontak</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($error_code >= 500): ?>
            <div class="mt-4 p-3 bg-light rounded">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Jika masalah ini terus berlanjut, silakan hubungi tim support kami di 
                    <strong>support@sambatan.com</strong> atau WhatsApp 
                    <strong>+62 812-3456-7890</strong>
                </small>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Search functionality
        function performSearch() {
            const query = document.getElementById('searchInput').value.trim();
            if (query) {
                // Simple search logic - redirect to main page with search parameter
                window.location.href = '/?search=' + encodeURIComponent(query);
            }
        }
        
        // Allow search on Enter key
        document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
        
        // Auto-focus search input on 404 pages
        <?php if ($error_code == 404): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                setTimeout(() => searchInput.focus(), 500);
            }
        });
        <?php endif; ?>
        
        // Add some interactivity
        document.addEventListener('DOMContentLoaded', function() {
            // Animate error container on load
            const container = document.querySelector('.error-container');
            if (container) {
                container.style.opacity = '0';
                container.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    container.style.transition = 'all 0.6s ease';
                    container.style.opacity = '1';
                    container.style.transform = 'translateY(0)';
                }, 100);
            }
            
            // Add click effect to buttons
            document.querySelectorAll('.btn-coffee, .btn-outline-coffee').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    // Create ripple effect
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.cssText = `
                        position: absolute;
                        width: ${size}px;
                        height: ${size}px;
                        left: ${x}px;
                        top: ${y}px;
                        background: rgba(255,255,255,0.3);
                        border-radius: 50%;
                        transform: scale(0);
                        animation: ripple 0.6s linear;
                        pointer-events: none;
                    `;
                    
                    this.style.position = 'relative';
                    this.style.overflow = 'hidden';
                    this.appendChild(ripple);
                    
                    setTimeout(() => ripple.remove(), 600);
                });
            });
        });
        
        // Add CSS for ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
        
        // Log error for debugging (in development)
        console.error('HTTP Error <?php echo $error_code; ?>: <?php echo addslashes($error_info['title']); ?>');
        console.log('Request URL:', window.location.href);
        console.log('Referer:', document.referrer);
    </script>
</body>
</html>
