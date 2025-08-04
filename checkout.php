<?php
require_once 'config/config.php';

// Check if order data exists
if (!isset($_SESSION['order_data']) && !isset($_GET['order_id'])) {
    header('Location: order.php');
    exit;
}

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = sanitize($_POST['customer_name']);
    $customer_phone = sanitize($_POST['customer_phone']);
    $customer_email = sanitize($_POST['customer_email']);
    $order_type = sanitize($_POST['order_type']);
    $table_number = sanitize($_POST['table_number'] ?? '');
    $delivery_address = sanitize($_POST['delivery_address'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');
    $total_amount = (float)$_POST['total_amount'];
    
    try {
        $pdo->beginTransaction();
        
        // Create customer
        $stmt = $pdo->prepare("INSERT INTO customers (name, email, phone) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name), phone = VALUES(phone)");
        $stmt->execute([$customer_name, $customer_email, $customer_phone]);
        $customer_id = $pdo->lastInsertId() ?: $pdo->query("SELECT id FROM customers WHERE email = '$customer_email'")->fetchColumn();
        
        // Create order
        $order_number = generateOrderNumber();
        $stmt = $pdo->prepare("
            INSERT INTO orders (customer_id, order_number, total_amount, status, payment_status, order_type, table_number, delivery_address, notes) 
            VALUES (?, ?, ?, 'pending', 'pending', ?, ?, ?, ?)
        ");
        $stmt->execute([$customer_id, $order_number, $total_amount, $order_type, $table_number, $delivery_address, $notes]);
        $order_id = $pdo->lastInsertId();
        
        // Add order items
        $items = json_decode($_POST['items'], true);
        foreach ($items as $item) {
            $stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, menu_item_id, quantity, price, subtotal) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $subtotal = $item['price'] * $item['quantity'];
            $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price'], $subtotal]);
        }
        
        $pdo->commit();
        
        // Redirect to payment or success page
        header("Location: payment.php?order_id=$order_id");
        exit;
        
    } catch (Exception $e) {
        $pdo->rollback();
        $message = 'Gagal membuat pesanan: ' . $e->getMessage();
        $message_type = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Sambatan Coffee & Space</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="assets/sambatanlogo.png">
    
    <style>
        .checkout-hero {
            background: linear-gradient(rgba(28, 64, 62, 0.9), rgba(28, 64, 62, 0.9)), url('assets/background.png');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 120px 0 60px;
        }
        
        .checkout-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .order-summary {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
        }
        
        .order-item {
            display: flex;
            justify-content: between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .form-floating {
            margin-bottom: 1rem;
        }
        
        .form-floating > .form-control {
            height: calc(3.5rem + 2px);
            padding: 1rem 0.75rem;
        }
        
        .form-floating > label {
            padding: 1rem 0.75rem;
        }
        
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }
        
        .payment-method {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }
        
        .payment-method:hover,
        .payment-method.active {
            border-color: #F0B33C;
            background: #F0B33C;
            color: white;
        }
        
        .payment-method i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .place-order-btn {
            background: linear-gradient(45deg, #1C403E, #2a5a56);
            border: none;
            border-radius: 25px;
            color: white;
            padding: 15px 30px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .place-order-btn:hover {
            background: linear-gradient(45deg, #F0B33C, #d19d35);
            transform: translateY(-2px);
            color: white;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        
        .step {
            display: flex;
            align-items: center;
            margin: 0 1rem;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 0.5rem;
        }
        
        .step.active .step-number {
            background: #F0B33C;
            color: white;
        }
        
        .step.completed .step-number {
            background: #28a745;
            color: white;
        }
        
        .delivery-info {
            display: none;
            margin-top: 1rem;
            padding: 1rem;
            background: #e3f2fd;
            border-radius: 10px;
        }
        
        .delivery-info.show {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'layout/navbar.html'; ?>
    
    <!-- Hero Section -->
    <section class="checkout-hero">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-3">Checkout</h1>
            <p class="lead">Lengkapi informasi pesanan Anda</p>
        </div>
    </section>
    
    <!-- Checkout Section -->
    <section class="py-5" style="background-color: #f8f9fa;">
        <div class="container">
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step completed">
                    <div class="step-number">1</div>
                    <span>Pilih Menu</span>
                </div>
                <div class="step active">
                    <div class="step-number">2</div>
                    <span>Checkout</span>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <span>Pembayaran</span>
                </div>
            </div>
            
            <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                <?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <form id="checkoutForm" method="POST">
                <div class="row">
                    <!-- Customer Information -->
                    <div class="col-lg-8">
                        <div class="checkout-card">
                            <h4 class="mb-4" style="color: #1C403E;">
                                <i class="fas fa-user"></i> Informasi Pelanggan
                            </h4>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="customer_name" name="customer_name" placeholder="Nama Lengkap" required>
                                        <label for="customer_name">Nama Lengkap</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="tel" class="form-control" id="customer_phone" name="customer_phone" placeholder="Nomor Telepon" required>
                                        <label for="customer_phone">Nomor Telepon</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-floating">
                                <input type="email" class="form-control" id="customer_email" name="customer_email" placeholder="Email">
                                <label for="customer_email">Email (Opsional)</label>
                            </div>
                        </div>
                        
                        <!-- Order Type -->
                        <div class="checkout-card">
                            <h4 class="mb-4" style="color: #1C403E;">
                                <i class="fas fa-utensils"></i> Jenis Pesanan
                            </h4>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="payment-method active" data-type="dine_in">
                                        <input type="radio" name="order_type" value="dine_in" checked style="display: none;">
                                        <i class="fas fa-utensils"></i>
                                        <div>Makan di Tempat</div>
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <label class="payment-method" data-type="takeaway">
                                        <input type="radio" name="order_type" value="takeaway" style="display: none;">
                                        <i class="fas fa-shopping-bag"></i>
                                        <div>Bawa Pulang</div>
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <label class="payment-method" data-type="delivery">
                                        <input type="radio" name="order_type" value="delivery" style="display: none;">
                                        <i class="fas fa-motorcycle"></i>
                                        <div>Delivery</div>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Table Number (for dine in) -->
                            <div id="table-info" class="mt-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="table_number" name="table_number" placeholder="Nomor Meja">
                                    <label for="table_number">Nomor Meja (Opsional)</label>
                                </div>
                            </div>
                            
                            <!-- Delivery Address -->
                            <div id="delivery-info" class="delivery-info">
                                <h6 class="mb-3">Alamat Pengiriman</h6>
                                <div class="form-floating">
                                    <textarea class="form-control" id="delivery_address" name="delivery_address" placeholder="Alamat Lengkap" style="height: 100px"></textarea>
                                    <label for="delivery_address">Alamat Lengkap</label>
                                </div>
                                <div class="alert alert-info mt-2">
                                    <i class="fas fa-info-circle"></i>
                                    Biaya pengiriman: Rp 5,000 (Minimum order: Rp 50,000)
                                </div>
                            </div>
                        </div>
                        
                        <!-- Special Notes -->
                        <div class="checkout-card">
                            <h4 class="mb-4" style="color: #1C403E;">
                                <i class="fas fa-sticky-note"></i> Catatan Khusus
                            </h4>
                            
                            <div class="form-floating">
                                <textarea class="form-control" id="notes" name="notes" placeholder="Catatan untuk pesanan" style="height: 100px"></textarea>
                                <label for="notes">Catatan untuk pesanan (Opsional)</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="col-lg-4">
                        <div class="checkout-card">
                            <h4 class="mb-4" style="color: #1C403E;">
                                <i class="fas fa-receipt"></i> Ringkasan Pesanan
                            </h4>
                            
                            <div id="order-summary" class="order-summary">
                                <!-- Order items will be populated by JavaScript -->
                            </div>
                            
                            <button type="submit" class="place-order-btn mt-3">
                                <i class="fas fa-credit-card"></i> Buat Pesanan
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Hidden inputs -->
                <input type="hidden" id="items" name="items">
                <input type="hidden" id="total_amount" name="total_amount">
            </form>
        </div>
    </section>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Load order data from session storage
        const orderData = JSON.parse(sessionStorage.getItem('orderData') || '{}');
        
        if (!orderData.items || orderData.items.length === 0) {
            window.location.href = 'order.php';
        }
        
        // Populate order summary
        function populateOrderSummary() {
            const summaryDiv = document.getElementById('order-summary');
            let itemsHtml = '';
            
            orderData.items.forEach(item => {
                itemsHtml += `
                    <div class="order-item">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <div>
                                <h6 class="mb-1">${item.name}</h6>
                                <small class="text-muted">${formatPrice(item.price)} × ${item.quantity}</small>
                            </div>
                            <div class="fw-bold">${formatPrice(item.price * item.quantity)}</div>
                        </div>
                    </div>
                `;
            });
            
            itemsHtml += `
                <div class="mt-3 pt-3 border-top">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>${formatPrice(orderData.subtotal)}</span>
                    </div>
                    ${orderData.deliveryFee > 0 ? `
                        <div class="d-flex justify-content-between mb-2" id="delivery-fee-display">
                            <span>Biaya Kirim:</span>
                            <span>${formatPrice(orderData.deliveryFee)}</span>
                        </div>
                    ` : ''}
                    <div class="d-flex justify-content-between">
                        <strong>Total:</strong>
                        <strong style="color: #F0B33C;" id="total-display">${formatPrice(orderData.total)}</strong>
                    </div>
                </div>
            `;
            
            summaryDiv.innerHTML = itemsHtml;
            
            // Set hidden form values
            document.getElementById('items').value = JSON.stringify(orderData.items);
            document.getElementById('total_amount').value = orderData.total;
        }
        
        // Order type change handler
        document.querySelectorAll('input[name="order_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                // Update visual selection
                document.querySelectorAll('.payment-method').forEach(method => {
                    method.classList.remove('active');
                });
                this.closest('.payment-method').classList.add('active');
                
                // Show/hide relevant sections
                const deliveryInfo = document.getElementById('delivery-info');
                const tableInfo = document.getElementById('table-info');
                
                if (this.value === 'delivery') {
                    deliveryInfo.classList.add('show');
                    tableInfo.style.display = 'none';
                    
                    // Update total with delivery fee
                    orderData.deliveryFee = 5000;
                    orderData.total = orderData.subtotal + orderData.deliveryFee;
                } else {
                    deliveryInfo.classList.remove('show');
                    tableInfo.style.display = 'block';
                    
                    // Remove delivery fee
                    orderData.deliveryFee = 0;
                    orderData.total = orderData.subtotal;
                }
                
                // Update display
                updateTotalDisplay();
                document.getElementById('total_amount').value = orderData.total;
            });
        });
        
        function updateTotalDisplay() {
            const deliveryDisplay = document.getElementById('delivery-fee-display');
            const totalDisplay = document.getElementById('total-display');
            
            if (orderData.deliveryFee > 0) {
                if (deliveryDisplay) {
                    deliveryDisplay.style.display = 'flex';
                }
            } else {
                if (deliveryDisplay) {
                    deliveryDisplay.style.display = 'none';
                }
            }
            
            totalDisplay.textContent = formatPrice(orderData.total);
        }
        
        // Form validation
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            const orderType = document.querySelector('input[name="order_type"]:checked').value;
            
            if (orderType === 'delivery') {
                const deliveryAddress = document.getElementById('delivery_address').value.trim();
                if (!deliveryAddress) {
                    e.preventDefault();
                    alert('Alamat pengiriman harus diisi untuk pesanan delivery!');
                    return;
                }
                
                if (orderData.subtotal < 50000) {
                    e.preventDefault();
                    alert('Minimum order untuk delivery adalah Rp 50,000!');
                    return;
                }
            }
        });
        
        function formatPrice(price) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(price);
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            populateOrderSummary();
            
            // Auto-format phone number
            const phoneInput = document.getElementById('customer_phone');
            phoneInput.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                if (value.startsWith('0')) {
                    value = '62' + value.substring(1);
                }
                this.value = value;
            });
        });
    </script>
</body>
</html>
