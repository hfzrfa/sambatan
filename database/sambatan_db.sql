-- Database untuk Sambatan Coffee Shop
CREATE DATABASE IF NOT EXISTS sambatan_db;
USE sambatan_db;

-- Tabel admin untuk login
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel kategori menu
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel menu
CREATE TABLE menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    status ENUM('available', 'unavailable') DEFAULT 'available',
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Tabel pelanggan
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel pesanan
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_method ENUM('cash', 'transfer', 'e_wallet', 'credit_card') DEFAULT 'cash',
    payment_reference VARCHAR(100),
    order_type ENUM('dine_in', 'takeaway', 'delivery') DEFAULT 'dine_in',
    table_number VARCHAR(10),
    delivery_address TEXT,
    notes TEXT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
);

-- Tabel detail pesanan
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
);

-- Tabel ulasan
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    order_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
);

-- Tabel pengaturan
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel galeri
CREATE TABLE gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100),
    image VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert data admin default
INSERT INTO admin (username, password, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@sambatan.com');
-- Password default: password

-- Insert kategori default
INSERT INTO categories (name, description) VALUES 
('Kopi Panas', 'Berbagai macam kopi panas dengan cita rasa yang khas'),
('Kopi Dingin', 'Kopi dingin yang menyegarkan untuk cuaca panas'),
('Non Kopi', 'Minuman selain kopi untuk yang tidak suka kopi'),
('Makanan Ringan', 'Camilan untuk menemani minum kopi'),
('Makanan Berat', 'Menu makanan utama');

-- Insert menu default
INSERT INTO menu_items (category_id, name, description, price, is_featured) VALUES 
(1, 'Kopi Sambatan Special', 'Kopi house blend dengan rasa yang khas dan aroma yang menggoda', 25000, TRUE),
(1, 'Espresso', 'Kopi murni dengan rasa yang kuat dan pekat', 20000, FALSE),
(1, 'Americano', 'Espresso dengan air panas, rasa yang balance', 22000, FALSE),
(1, 'Cappuccino', 'Espresso dengan steamed milk dan milk foam', 28000, TRUE),
(1, 'Latte', 'Espresso dengan steamed milk yang creamy', 30000, FALSE),
(2, 'Es Kopi Sambatan', 'Kopi dingin khas sambatan dengan es batu', 27000, TRUE),
(2, 'Iced Americano', 'Americano dengan es batu yang menyegarkan', 24000, FALSE),
(2, 'Iced Latte', 'Latte dingin yang creamy dan menyegarkan', 32000, FALSE),
(3, 'Teh Tarik', 'Teh dengan susu yang ditarik hingga berbusa', 18000, FALSE),
(3, 'Chocolate', 'Cokelat panas yang hangat dan manis', 25000, FALSE),
(4, 'Pisang Goreng', 'Pisang goreng crispy dengan topping menarik', 15000, FALSE),
(4, 'Roti Bakar', 'Roti bakar dengan berbagai pilihan topping', 20000, FALSE),
(5, 'Nasi Goreng Sambatan', 'Nasi goreng spesial dengan bumbu rahasia', 35000, TRUE),
(5, 'Mie Ayam', 'Mie ayam dengan kuah yang gurih', 30000, FALSE);

-- Insert pengaturan default
INSERT INTO settings (setting_key, setting_value, description) VALUES 
('cafe_name', 'Sambatan Coffee & Space', 'Nama cafe'),
('cafe_phone', '081234567890', 'Nomor telepon cafe'),
('cafe_email', 'info@sambatan.com', 'Email cafe'),
('cafe_address', 'Jl. Contoh No. 123, Kota Contoh', 'Alamat cafe'),
('opening_hours', '08:00 - 22:00', 'Jam buka cafe'),
('delivery_fee', '5000', 'Biaya pengiriman'),
('min_order_delivery', '50000', 'Minimum order untuk delivery'),
('tax_percentage', '10', 'Persentase pajak'),
('payment_gateway_key', '', 'API Key untuk payment gateway'),
('whatsapp_number', '6281234567890', 'Nomor WhatsApp untuk order');
