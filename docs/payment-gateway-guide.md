# Panduan Integrasi Payment Gateway untuk Sambatan Coffee & Space

## Daftar Isi
1. [Persiapan](#persiapan)
2. [Midtrans Integration](#midtrans-integration)
3. [Xendit Integration](#xendit-integration)
4. [PayPal Integration](#paypal-integration)
5. [Testing dan Go Live](#testing-dan-go-live)
6. [Security Best Practices](#security-best-practices)

---

## Persiapan

### 1. Dokumen yang Diperlukan
- **SIUP/NIB** - Surat Izin Usaha Perdagangan
- **NPWP** - Nomor Pokok Wajib Pajak Perusahaan
- **Akta Pendirian** - Akta pendirian perusahaan
- **KTP Direktur** - KTP direktur/pemilik perusahaan
- **Rekening Bank Bisnis** - Rekening atas nama perusahaan

### 2. Memilih Payment Gateway
Untuk bisnis di Indonesia, rekomendasi payment gateway:
1. **Midtrans** (Paling populer di Indonesia)
2. **Xendit** (Mudah implementasi)
3. **DOKU** (Veteran payment gateway Indonesia)
4. **PayPal** (Untuk transaksi internasional)

---

## Midtrans Integration

### 1. Registrasi Akun
1. Kunjungi [https://midtrans.com](https://midtrans.com)
2. Pilih "Daftar Sekarang"
3. Isi formulir registrasi dengan data lengkap
4. Upload dokumen yang diperlukan
5. Tunggu verifikasi (1-3 hari kerja)

### 2. Konfigurasi Dashboard
```bash
# Login ke Midtrans Dashboard
# 1. Pilih Environment: Sandbox (untuk testing) / Production (untuk live)
# 2. Catat Server Key dan Client Key
# 3. Set Notification URL: https://yourdomain.com/payment/midtrans-notification.php
# 4. Set Finish Redirect URL: https://yourdomain.com/payment/success
# 5. Set Unfinish Redirect URL: https://yourdomain.com/payment/pending
# 6. Set Error Redirect URL: https://yourdomain.com/payment/failed
```

### 3. Instalasi SDK
```bash
# Via Composer
composer require midtrans/midtrans-php

# Atau download manual dari GitHub
# https://github.com/Midtrans/midtrans-php
```

### 4. Implementasi Backend
```php
<?php
// File: payment/midtrans-config.php
require_once '../vendor/autoload.php';

// Set your Merchant Server Key
\Midtrans\Config::$serverKey = 'SB-Mid-server-xxxxxxxxxx'; // Sandbox
// \Midtrans\Config::$serverKey = 'Mid-server-xxxxxxxxxx'; // Production

// Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
\Midtrans\Config::$isProduction = false;

// Set sanitization on (default)
\Midtrans\Config::$isSanitized = true;

// Set 3DS transaction for credit card to true
\Midtrans\Config::$is3ds = true;
?>

<?php
// File: payment/create-payment.php
require_once 'midtrans-config.php';
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = generateOrderNumber();
    $gross_amount = (int)$_POST['total_amount'];
    
    $transaction_details = array(
        'order_id' => $order_id,
        'gross_amount' => $gross_amount,
    );
    
    $customer_details = array(
        'first_name'    => $_POST['customer_name'],
        'email'         => $_POST['customer_email'],
        'phone'         => $_POST['customer_phone'],
    );
    
    $item_details = array();
    foreach ($_POST['items'] as $item) {
        $item_details[] = array(
            'id' => $item['id'],
            'price' => $item['price'],
            'quantity' => $item['quantity'],
            'name' => $item['name']
        );
    }
    
    $transaction = array(
        'transaction_details' => $transaction_details,
        'customer_details' => $customer_details,
        'item_details' => $item_details,
    );
    
    try {
        $snapToken = \Midtrans\Snap::getSnapToken($transaction);
        
        // Save order to database
        $stmt = $pdo->prepare("INSERT INTO orders (order_number, customer_name, customer_email, customer_phone, total_amount, payment_status, payment_token) VALUES (?, ?, ?, ?, ?, 'pending', ?)");
        $stmt->execute([
            $order_id,
            $_POST['customer_name'],
            $_POST['customer_email'],
            $_POST['customer_phone'],
            $gross_amount,
            $snapToken
        ]);
        
        echo json_encode(['token' => $snapToken, 'order_id' => $order_id]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
```

### 5. Implementasi Frontend
```html
<!-- File: payment/checkout.html -->
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script type="text/javascript"
            src="https://app.sandbox.midtrans.com/snap/snap.js"
            data-client-key="SB-Mid-client-xxxxxxxxxx"></script>
    <!-- For Production: src="https://app.midtrans.com/snap/snap.js" -->
</head>
<body>
    <button id="pay-button">Bayar Sekarang</button>
    
    <script type="text/javascript">
        document.getElementById('pay-button').onclick = function(){
            // Request ke backend untuk mendapatkan token
            fetch('create-payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    customer_name: 'John Doe',
                    customer_email: 'john@example.com',
                    customer_phone: '081234567890',
                    total_amount: 50000,
                    items: [
                        {
                            id: 1,
                            name: 'Kopi Sambatan Special',
                            price: 25000,
                            quantity: 2
                        }
                    ]
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.token) {
                    snap.pay(data.token, {
                        onSuccess: function(result){
                            window.location.href = 'success.php?order_id=' + data.order_id;
                        },
                        onPending: function(result){
                            window.location.href = 'pending.php?order_id=' + data.order_id;
                        },
                        onError: function(result){
                            window.location.href = 'failed.php?order_id=' + data.order_id;
                        }
                    });
                }
            });
        };
    </script>
</body>
</html>
```

### 6. Notification Handler
```php
<?php
// File: payment/midtrans-notification.php
require_once 'midtrans-config.php';
require_once '../config/config.php';

$notif = new \Midtrans\Notification();

$transaction = $notif->transaction_status;
$type = $notif->payment_type;
$order_id = $notif->order_id;
$fraud = $notif->fraud_status;

// Update order status based on notification
if ($transaction == 'capture') {
    if ($type == 'credit_card'){
        if($fraud == 'challenge'){
            $status = 'challenge';
        } else {
            $status = 'paid';
        }
    }
} else if ($transaction == 'settlement'){
    $status = 'paid';
} else if($transaction == 'pending'){
    $status = 'pending';
} else if ($transaction == 'deny') {
    $status = 'denied';
} else if ($transaction == 'expire') {
    $status = 'expired';
} else if ($transaction == 'cancel') {
    $status = 'cancelled';
}

// Update database
$stmt = $pdo->prepare("UPDATE orders SET payment_status = ? WHERE order_number = ?");
$stmt->execute([$status, $order_id]);

echo "OK";
?>
```

---

## Xendit Integration

### 1. Registrasi dan Setup
```bash
# 1. Daftar di https://xendit.co
# 2. Verifikasi dokumen bisnis
# 3. Dapatkan API Keys dari dashboard
```

### 2. Implementasi Xendit
```php
<?php
// File: payment/xendit-config.php
$xendit_secret_key = 'xnd_development_xxxxx'; // Development
// $xendit_secret_key = 'xnd_production_xxxxx'; // Production

function createXenditInvoice($order_data) {
    global $xendit_secret_key;
    
    $data = array(
        'external_id' => $order_data['order_id'],
        'payer_email' => $order_data['customer_email'],
        'description' => 'Pembayaran Sambatan Coffee',
        'amount' => $order_data['total_amount'],
        'success_redirect_url' => 'https://yourdomain.com/payment/success',
        'failure_redirect_url' => 'https://yourdomain.com/payment/failed',
    );
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.xendit.co/v2/invoices',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => false,
        CURLOPT_HTTPHEADER => array(
            'Authorization: Basic ' . base64_encode($xendit_secret_key . ':'),
            'Content-Type: application/json',
        ),
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
    ));
    
    $response = curl_exec($curl);
    curl_close($curl);
    
    return json_decode($response, true);
}
?>
```

---

## Testing dan Go Live

### 1. Testing Checklist
- [ ] **Sandbox Testing**
  - [ ] Test successful payment
  - [ ] Test failed payment
  - [ ] Test cancelled payment
  - [ ] Test notification webhook
  
- [ ] **Security Testing**
  - [ ] Verify HTTPS implementation
  - [ ] Test SQL injection protection
  - [ ] Validate input sanitization
  - [ ] Check CSRF protection

- [ ] **Performance Testing**
  - [ ] Load testing untuk payment flow
  - [ ] Test response time
  - [ ] Database query optimization

### 2. Go Live Checklist
- [ ] **Production Setup**
  - [ ] Update API keys ke production
  - [ ] Configure production webhook URLs
  - [ ] Setup monitoring dan logging
  - [ ] Backup database

- [ ] **Legal & Compliance**
  - [ ] Privacy Policy untuk payment data
  - [ ] Terms of Service
  - [ ] PCI DSS compliance (jika applicable)

---

## Security Best Practices

### 1. Data Protection
```php
// Always validate and sanitize input
function validatePaymentData($data) {
    $required_fields = ['amount', 'customer_email', 'customer_name'];
    
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            throw new Exception("Field $field is required");
        }
    }
    
    // Validate email
    if (!filter_var($data['customer_email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }
    
    // Validate amount
    if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
        throw new Exception("Invalid amount");
    }
    
    return true;
}
```

### 2. Webhook Security
```php
// Verify webhook signature
function verifyWebhookSignature($payload, $signature, $secret) {
    $calculated_signature = hash_hmac('sha256', $payload, $secret);
    return hash_equals($signature, $calculated_signature);
}
```

### 3. Rate Limiting
```php
// Simple rate limiting
function checkRateLimit($ip) {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    
    $key = "payment_rate_limit:$ip";
    $current = $redis->get($key);
    
    if ($current >= 10) { // 10 requests per minute
        throw new Exception("Rate limit exceeded");
    }
    
    $redis->incr($key);
    $redis->expire($key, 60);
}
```

---

## Monitoring dan Maintenance

### 1. Log Essential Events
```php
// Log payment events
function logPaymentEvent($event, $data) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'data' => $data,
        'ip' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT']
    ];
    
    file_put_contents(
        '/var/log/sambatan/payment.log', 
        json_encode($log_entry) . "\n", 
        FILE_APPEND | LOCK_EX
    );
}
```

### 2. Health Check Endpoint
```php
// File: payment/health-check.php
header('Content-Type: application/json');

$health_status = [
    'status' => 'healthy',
    'timestamp' => date('c'),
    'services' => [
        'database' => 'healthy',
        'midtrans' => 'healthy',
        'redis' => 'healthy'
    ]
];

// Check database connection
try {
    $pdo->query('SELECT 1');
} catch (Exception $e) {
    $health_status['status'] = 'unhealthy';
    $health_status['services']['database'] = 'unhealthy';
}

echo json_encode($health_status);
?>
```

---

## FAQ

**Q: Berapa lama proses verifikasi akun payment gateway?**
A: Midtrans: 1-3 hari kerja, Xendit: 1-2 hari kerja, PayPal: 1-5 hari kerja.

**Q: Apakah perlu HTTPS untuk payment gateway?**
A: Ya, HTTPS wajib untuk semua payment gateway untuk keamanan data.

**Q: Bagaimana cara handle failed payment?**
A: Simpan log error, kirim notification ke admin, dan berikan opsi retry ke customer.

**Q: Biaya transaksi payment gateway berapa?**
A: Midtrans: 2.9% + Rp 2,000, Xendit: 2.9% + Rp 2,000, PayPal: 3.9% + Rp 2,300.

---

**Catatan Penting:**
- Selalu test di environment sandbox sebelum go live
- Backup database sebelum implementasi
- Monitor transaksi secara real-time
- Update security patches secara berkala
- Simpan log transaksi minimal 2 tahun
