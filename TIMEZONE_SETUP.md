# Timezone Setup for RegMail

## Đã cấu hình timezone Vietnam (+7) trong Laravel:

### 1. Application Timezone
- **File**: `config/app.php`
- **Setting**: `'timezone' => 'Asia/Ho_Chi_Minh'`

### 2. Database Timezone
- **File**: `config/database.php`
- **Settings**: 
  - SQLite: `'timezone' => 'Asia/Ho_Chi_Minh'`
  - MySQL: `'timezone' => 'Asia/Ho_Chi_Minh'`
  - MariaDB: `'timezone' => 'Asia/Ho_Chi_Minh'`
  - PostgreSQL: `'timezone' => 'Asia/Ho_Chi_Minh'`

## Trên Server (cPanel/Hosting):

### 1. PHP Timezone
Thêm vào `.env` file:
```env
APP_TIMEZONE=Asia/Ho_Chi_Minh
```

### 2. Server Timezone
Trong cPanel → MultiPHP INI Editor:
```ini
date.timezone = "Asia/Ho_Chi_Minh"
```

### 3. Database Timezone (nếu dùng MySQL)
```sql
SET time_zone = '+07:00';
```

## Kiểm tra timezone:

### 1. Laravel Command:
```bash
php artisan tinker
>>> now()
>>> config('app.timezone')
```

### 2. PHP Command:
```bash
php -r "echo date_default_timezone_get();"
```

## Kết quả:
- ✅ Tất cả datetime sẽ hiển thị theo giờ Vietnam (+7)
- ✅ Database timestamps sẽ được lưu theo timezone Vietnam
- ✅ API responses sẽ có timezone đúng
- ✅ Logs sẽ có timestamp Vietnam

## Lưu ý:
- Timezone `Asia/Ho_Chi_Minh` = UTC+7
- Bao gồm cả daylight saving time
- Tương thích với tất cả database engines
