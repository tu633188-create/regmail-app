# 🚀 Deploy App Version Management - Checklist

Sau khi deploy code mới lên server, cần chạy các lệnh sau để App Versions tab hiện ra:

## 📋 **Các bước cần thiết:**

### 1. **Chạy Migration**
```bash
php artisan migrate
```
Tạo table `app_versions` trong database.

### 2. **Clear Cache**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 3. **Optimize (Production)**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4. **Composer Autoload (nếu cần)**
```bash
composer dump-autoload
```

### 5. **Filament Cache (nếu vẫn không hiện)**
```bash
php artisan filament:cache-components
php artisan filament:upgrade
```

## 🔍 **Kiểm tra:**

1. **Kiểm tra table đã tồn tại:**
```bash
php artisan tinker
>>> \DB::table('app_versions')->count()
```

2. **Kiểm tra Resource đã được discover:**
- Vào `/admin` → Kiểm tra sidebar có "App Versions" không
- Hoặc truy cập trực tiếp: `/admin/app-versions`

3. **Kiểm tra logs nếu có lỗi:**
```bash
tail -f storage/logs/laravel.log
```

## ⚠️ **Lưu ý:**

- Nếu vẫn không hiện, có thể do:
  - File chưa được upload đầy đủ
  - Permissions không đúng
  - PHP opcache chưa clear (restart PHP-FPM)

## 🔧 **Quick Fix Script:**

Tạo file `deploy_version_management.sh`:

```bash
#!/bin/bash
cd /path/to/regmail
php artisan migrate --force
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
composer dump-autoload
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✅ Done! Check /admin/app-versions"
```

