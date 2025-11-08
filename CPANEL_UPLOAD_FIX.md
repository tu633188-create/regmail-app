# 🔧 Fix PHP Upload Limits trong cPanel

## 📋 **Các Cách Sửa trong cPanel:**

### **Cách 1: MultiPHP INI Editor (Khuyến nghị)**

1. **Đăng nhập cPanel**
2. **Tìm "MultiPHP INI Editor"** trong phần Software
3. **Chọn domain** hoặc thư mục cần sửa
4. **Tìm và sửa các dòng:**
   ```ini
   upload_max_filesize = 500M
   post_max_size = 500M
   max_execution_time = 300
   memory_limit = 512M
   ```
5. **Click "Save"**

### **Cách 2: PHP Selector (nếu có)**

1. **Tìm "Select PHP Version"** hoặc **"PHP Selector"**
2. **Chọn PHP version** đang dùng
3. **Click "Options"** hoặc **"Extensions"**
4. **Tìm tab "Options"** hoặc **"INI Editor"**
5. **Sửa các giá trị:**
   - `upload_max_filesize` → `500M`
   - `post_max_size` → `500M`
   - `max_execution_time` → `300`
   - `memory_limit` → `512M`
6. **Save changes**

### **Cách 3: Tạo .user.ini file (Nếu không có quyền sửa PHP INI)**

1. **Vào File Manager** trong cPanel
2. **Vào thư mục public_html** (hoặc thư mục gốc của Laravel app)
3. **Tạo file mới** tên `.user.ini`
4. **Thêm nội dung:**
   ```ini
   upload_max_filesize = 500M
   post_max_size = 500M
   max_execution_time = 300
   memory_limit = 512M
   ```
5. **Save file**
6. **Set permissions** (nếu cần): `644`

**Lưu ý:** `.user.ini` chỉ work với PHP-FPM, không work với suPHP hoặc CGI.

### **Cách 4: Tạo php.ini trong thư mục (Nếu dùng suPHP)**

1. **Vào File Manager**
2. **Vào thư mục public_html**
3. **Tạo file `php.ini`**
4. **Copy nội dung từ file php.ini mặc định** và sửa:
   ```ini
   upload_max_filesize = 500M
   post_max_size = 500M
   max_execution_time = 300
   memory_limit = 512M
   ```
5. **Save**

### **Cách 5: Sửa .htaccess (Chỉ work với Apache + mod_php)**

File: `public/.htaccess` (đã có sẵn trong project)
```apache
<IfModule mod_php.c>
    php_value upload_max_filesize 500M
    php_value post_max_size 500M
    php_value max_execution_time 300
    php_value memory_limit 512M
</IfModule>
```

**Lưu ý:** Chỉ work nếu hosting dùng Apache + mod_php, không work với PHP-FPM.

## 🔍 **Kiểm tra sau khi sửa:**

### **Tạo file test:**
Tạo file `public/check_php_limits.php`:
```php
<?php
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
```

Truy cập: `https://yourdomain.com/check_php_limits.php`

### **Hoặc dùng file có sẵn:**
File `check_php_upload_limits.php` đã có trong project, copy vào `public/`:
```bash
cp check_php_upload_limits.php public/
```

Truy cập: `https://yourdomain.com/check_php_upload_limits.php`

## ⚠️ **Lưu ý quan trọng:**

1. **Sau khi sửa, cần đợi vài phút** để cPanel apply changes
2. **Clear cache** nếu cần:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   ```
3. **Kiểm tra PHP handler:**
   - **PHP-FPM**: Dùng `.user.ini` hoặc MultiPHP INI Editor
   - **suPHP**: Dùng `php.ini` trong thư mục
   - **CGI**: Không thể override, phải liên hệ hosting support
   - **mod_php**: Dùng `.htaccess`

## 🎯 **Khuyến nghị:**

1. **Thử Cách 1 (MultiPHP INI Editor)** trước - dễ nhất
2. **Nếu không có**, thử **Cách 3 (.user.ini)**
3. **Nếu vẫn không work**, liên hệ hosting support để họ sửa

## 📞 **Nếu không có quyền:**

Liên hệ hosting support và yêu cầu:
- Tăng `upload_max_filesize` lên 500M
- Tăng `post_max_size` lên 500M
- Tăng `max_execution_time` lên 300
- Tăng `memory_limit` lên 512M

## 🔧 **Troubleshooting:**

### **Nếu .user.ini không work:**
- Kiểm tra PHP handler trong cPanel
- Đảm bảo file có tên chính xác `.user.ini` (có dấu chấm đầu)
- Đảm bảo file ở đúng thư mục (public_html hoặc thư mục Laravel)

### **Nếu .htaccess không work:**
- Kiểm tra xem hosting có dùng Apache không
- Kiểm tra xem có mod_php không (thường không có trong shared hosting hiện đại)
- Thử dùng `.user.ini` thay thế

### **Nếu vẫn không work:**
- Liên hệ hosting support
- Hoặc dùng cách upload file nhỏ hơn 2MB (nén file exe)

