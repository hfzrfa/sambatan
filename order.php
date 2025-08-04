<?php
require_once 'config/config.php';

// Get menu items for ordering
$stmt = $pdo->query("
    SELECT mi.*, c.name as category_name 
    FROM menu_items mi 
    LEFT JOIN categories c ON mi.category_id = c.id 
    WHERE mi.status = 'available'
    ORDER BY c.name, mi.name
");
$menu_items = $stmt->fetchAll();

// Group by category
$menu_by_category = [];
foreach ($menu_items as $item) {
    $category = $item['category_name'] ?? 'Lainnya';
    if (!isset($menu_by_category[$category])) {
        $menu_by_category[$category] = [];
    }
    $menu_by_category[$category][] = $item;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Sekarang - Sambatan Coffee & Space</title>
    <meta name="description" content="Pesan menu favorit Anda dari Sambatan Coffee & Space">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="assets/sambatanlogo.png">
    
    <style>
        .order-hero {
            background: linear-gradient(rgba(28, 64, 62, 0.9), rgba(28, 64, 62, 0.9)), url('assets/background.png');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 120px 0 80px;
        }
        
        .menu-item-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .menu-item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .menu-item-img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #e9ecef;
            border-radius: 25px;
            background: white;
        }
        
        .quantity-btn {
            background: none;
            border: none;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1C403E;
            font-weight: bold;
            transition: all 0.2s ease;
        }
        
        .quantity-btn:hover {
            background: #F0B33C;
            color: white;
            border-radius: 50%;
        }
        
        .quantity-input {
            border: none;
            width: 50px;
            text-align: center;
            font-weight: bold;
            color: #1C403E;
        }
        
        .quantity-input:focus {
            outline: none;
            box-shadow: none;
        }
        
        .cart-summary {
            position: sticky;
            top: 100px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        
        .cart-item {
            padding: 1rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .checkout-btn {
            background: linear-gradient(45deg, #1C403E, #2a5a56);
            border: none;
            border-radius: 25px;
            color: white;
            padding: 15px 30px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .checkout-btn:hover {
            background: linear-gradient(45deg, #F0B33C, #d19d35);
            transform: translateY(-2px);
            color: white;
        }
        
        .checkout-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }
        
        .order-type-selector {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1rem;
            margin-bottom: 2rem;
        }
        
        .order-type-btn {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }
        
        .order-type-btn.active {
            border-color: #F0B33C;
            background: #F0B33C;
            color: white;
        }
        
        .order-type-btn:hover {
            border-color: #F0B33C;
        }
        
        .floating-cart {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            display: none;
        }
        
        .floating-cart-btn {
            background: #F0B33C;
            color: white;
            border: none;
            border-radius: 50px;
            padding: 15px 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        
        .floating-cart-btn:hover {
            background: #d19d35;
            transform: scale(1.05);
            color: white;
        }
        
        .badge-cart {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .cart-summary {
                position: relative;
                top: auto;
                margin-top: 2rem;
            }
            
            .floating-cart {
                display: block;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'layout/navbar.html'; ?>
    
    <!-- Hero Section -->
    <section class="order-hero">
        <div class="container text-center">
            <h1 class="display-3 fw-bold mb-4" data-aos="fade-up">Pesan Sekarang</h1>
            <p class="lead" data-aos="fade-up" data-aos-delay="200">
                Pilih menu favorit Anda dan nikmati pengalaman kuliner terbaik
            </p>
        </div>
    </section>
    
    <!-- Order Section -->
    <section class="py-5" style="background-color: #f8f9fa;">
        <div class="container">
            <!-- Order Type Selector -->
            <div class="order-type-selector">
                <h5 class="mb-3 text-center" style="color: #1C403E;">Pilih Jenis Pesanan</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="order-type-btn active" data-type="dine_in">
                            <i class="fas fa-utensils fa-2x mb-2" style="color: #1C403E;"></i>
                            <h6>Makan di Tempat</h6>
                            <small class="text-muted">Nikmati langsung di cafe</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="order-type-btn" data-type="takeaway">
                            <i class="fas fa-shopping-bag fa-2x mb-2" style="color: #1C403E;"></i>
                            <h6>Bawa Pulang</h6>
                            <small class="text-muted">Ambil di tempat</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="order-type-btn" data-type="delivery">
                            <i class="fas fa-motorcycle fa-2x mb-2" style="color: #1C403E;"></i>
                            <h6>Delivery</h6>
                            <small class="text-muted">Antar ke alamat</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Menu Items -->
                <div class="col-lg-8">
                    <?php foreach ($menu_by_category as $category => $items): ?>
                    <div class="mb-4">
                        <h3 class="mb-3" style="color: #1C403E;"><?= $category ?></h3>
                        <div class="row">
                            <?php foreach ($items as $item): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card menu-item-card">
                                    <?php if ($item['image']): ?>
                                        <img src="uploads/<?= $item['image'] ?>" alt="<?= $item['name'] ?>" class="menu-item-img">
                                    <?php else: ?>
                                        <img src="assets/kopi.png" alt="<?= $item['name'] ?>" class="menu-item-img">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title" style="color: #1C403E;"><?= $item['name'] ?></h6>
                                            <?php if ($item['is_featured']): ?>
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-star"></i>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="card-text small text-muted mb-3"><?= substr($item['description'], 0, 80) ?>...</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold text-warning"><?= formatPrice($item['price']) ?></span>
                                            <div class="quantity-control">
                                                <button class="quantity-btn" onclick="decreaseQuantity(<?= $item['id'] ?>)">-</button>
                                                <input type="number" class="quantity-input" id="qty-<?= $item['id'] ?>" value="0" min="0" readonly>
                                                <button class="quantity-btn" onclick="increaseQuantity(<?= $item['id'] ?>, '<?= addslashes($item['name']) ?>', <?= $item['price'] ?>)">+</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Cart Summary -->
                <div class="col-lg-4">
                    <div class="cart-summary">
                        <h4 class="mb-3" style="color: #1C403E;">
                            <i class="fas fa-shopping-cart"></i> Keranjang Pesanan
                        </h4>
                        
                        <div id="cart-items">
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                                <p>Keranjang masih kosong</p>
                            </div>
                        </div>
                        
                        <div id="cart-summary" style="display: none;">
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span id="subtotal">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2" id="delivery-fee-row" style="display: none;">
                                <span>Biaya Kirim:</span>
                                <span id="delivery-fee">Rp 5,000</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Total:</strong>
                                <strong id="total" style="color: #F0B33C;">Rp 0</strong>
                            </div>
                            
                            <button class="checkout-btn" onclick="proceedToCheckout()" disabled>
                                <i class="fas fa-credit-card"></i> Lanjut ke Pembayaran
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Floating Cart (Mobile) -->
    <div class="floating-cart">
        <button class="floating-cart-btn" onclick="scrollToCart()">
            <i class="fas fa-shopping-cart"></i>
            <span class="badge-cart" id="floating-cart-count">0</span>
        </button>
    </div>
    
    <!-- WhatsApp Float Button -->
    <a href="https://wa.me/6281234567890?text=Halo, saya ingin bertanya tentang menu" class="whatsapp-float" target="_blank">
        <i class="fab fa-whatsapp"></i>
    </a>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true
        });
        
        // Cart object
        let cart = {
            items: [],
            orderType: 'dine_in',
            deliveryFee: 5000,
            
            addItem: function(id, name, price) {
                const existingItem = this.items.find(item => item.id === id);
                if (existingItem) {
                    existingItem.quantity++;
                } else {
                    this.items.push({
                        id: id,
                        name: name,
                        price: price,
                        quantity: 1
                    });
                }
                this.updateDisplay();
            },
            
            removeItem: function(id) {
                const itemIndex = this.items.findIndex(item => item.id === id);
                if (itemIndex > -1) {
                    if (this.items[itemIndex].quantity > 1) {
                        this.items[itemIndex].quantity--;
                    } else {
                        this.items.splice(itemIndex, 1);
                    }
                }
                this.updateDisplay();
            },
            
            getSubtotal: function() {
                return this.items.reduce((total, item) => total + (item.price * item.quantity), 0);
            },
            
            getTotal: function() {
                const subtotal = this.getSubtotal();
                if (this.orderType === 'delivery' && subtotal > 0) {
                    return subtotal + this.deliveryFee;
                }
                return subtotal;
            },
            
            updateDisplay: function() {
                const cartItemsDiv = document.getElementById('cart-items');
                const cartSummaryDiv = document.getElementById('cart-summary');
                const floatingCartCount = document.getElementById('floating-cart-count');
                
                // Update floating cart count
                const totalItems = this.items.reduce((total, item) => total + item.quantity, 0);
                floatingCartCount.textContent = totalItems;
                
                if (this.items.length === 0) {
                    cartItemsDiv.innerHTML = `
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                            <p>Keranjang masih kosong</p>
                        </div>
                    `;
                    cartSummaryDiv.style.display = 'none';
                    return;
                }
                
                // Display cart items
                let itemsHtml = '';
                this.items.forEach(item => {
                    itemsHtml += `
                        <div class="cart-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">${item.name}</h6>
                                    <small class="text-muted">${formatPrice(item.price)} × ${item.quantity}</small>
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-outline-danger" onclick="cart.removeItem(${item.id})">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <span class="mx-2 fw-bold">${formatPrice(item.price * item.quantity)}</span>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                cartItemsDiv.innerHTML = itemsHtml;
                cartSummaryDiv.style.display = 'block';
                
                // Update totals
                document.getElementById('subtotal').textContent = formatPrice(this.getSubtotal());
                document.getElementById('total').textContent = formatPrice(this.getTotal());
                
                // Show/hide delivery fee
                const deliveryFeeRow = document.getElementById('delivery-fee-row');
                if (this.orderType === 'delivery') {
                    deliveryFeeRow.style.display = 'flex';
                } else {
                    deliveryFeeRow.style.display = 'none';
                }
                
                // Enable/disable checkout button
                const checkoutBtn = document.querySelector('.checkout-btn');
                checkoutBtn.disabled = this.items.length === 0;
                
                // Update quantity inputs
                this.items.forEach(item => {
                    const qtyInput = document.getElementById(`qty-${item.id}`);
                    if (qtyInput) {
                        qtyInput.value = item.quantity;
                    }
                });
                
                // Reset quantity inputs for items not in cart
                document.querySelectorAll('.quantity-input').forEach(input => {
                    const id = parseInt(input.id.split('-')[1]);
                    if (!this.items.find(item => item.id === id)) {
                        input.value = 0;
                    }
                });
            }
        };
        
        // Order type selection
        document.querySelectorAll('.order-type-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.order-type-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                cart.orderType = this.getAttribute('data-type');
                cart.updateDisplay();
            });
        });
        
        // Quantity functions
        function increaseQuantity(id, name, price) {
            cart.addItem(id, name, price);
        }
        
        function decreaseQuantity(id) {
            cart.removeItem(id);
        }
        
        // Checkout function
        function proceedToCheckout() {
            if (cart.items.length === 0) return;
            
            // Create order summary
            const orderData = {
                items: cart.items,
                orderType: cart.orderType,
                subtotal: cart.getSubtotal(),
                deliveryFee: cart.orderType === 'delivery' ? cart.deliveryFee : 0,
                total: cart.getTotal()
            };
            
            // Store in session storage for checkout page
            sessionStorage.setItem('orderData', JSON.stringify(orderData));
            
            // Redirect to checkout
            window.location.href = 'checkout.php';
        }
        
        // Format price function
        function formatPrice(price) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(price);
        }
        
        // Scroll to cart function
        function scrollToCart() {
            document.querySelector('.cart-summary').scrollIntoView({
                behavior: 'smooth'
            });
        }
        
        // Show/hide floating cart based on screen size and scroll
        function toggleFloatingCart() {
            const floatingCart = document.querySelector('.floating-cart');
            if (window.innerWidth <= 768) {
                floatingCart.style.display = 'block';
            } else {
                floatingCart.style.display = 'none';
            }
        }
        
        window.addEventListener('resize', toggleFloatingCart);
        window.addEventListener('load', toggleFloatingCart);
    </script>
</body>
</html>
