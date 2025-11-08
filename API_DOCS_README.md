# 📚 RegMail API Documentation

## 🚀 **Swagger UI Access**

### **Live API Documentation:**
- **URL:** `https://trananhtu.vn/api/documentation`
- **Description:** Interactive API documentation with Swagger UI
- **Features:** 
  - Try out API endpoints directly
  - View request/response examples
  - Test authentication flows

## 🔐 **Authentication**

### **JWT Token-based Authentication**
- All protected endpoints require JWT token in Authorization header
- Format: `Authorization: Bearer <your-jwt-token>`
- Token expires after configured time (default: 1 hour)
- Use `/api/auth/refresh` to get new token before expiration
- **Device fingerprint required** for login - generate unique device identifier

### **Device Fingerprint Generation**
For Python clients, generate fingerprint using:
```python
import hashlib, platform, uuid
system_info = platform.platform() + platform.machine() + str(uuid.getnode())
fingerprint = 'device_' + hashlib.sha256(system_info.encode()).hexdigest()[:12]
```

For other clients, use any unique string like: `device_abc123xyz`

## 📋 **Available Endpoints**

### **Authentication Endpoints**
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| `POST` | `/api/auth/login` | User login | ❌ |
| `GET` | `/api/auth/validate` | Validate token | ✅ |
| `POST` | `/api/auth/refresh` | Refresh token | ✅ |
| `POST` | `/api/auth/logout` | User logout | ✅ |
| `GET` | `/api/auth/devices` | Get user devices | ✅ |
| `DELETE` | `/api/auth/devices/{id}` | Logout device | ✅ |

### **User Endpoints**
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| `GET` | `/api/users/profile` | Get user profile | ✅ |
| `GET` | `/api/users/quota` | Get quota info | ✅ |

### **Email Registration Endpoints**
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| `POST` | `/api/email/submit` | Submit successful email registration | ✅ |
| `GET` | `/api/register/history` | Get registration history | ✅ |
| `GET` | `/api/register/stats` | Get registration statistics | ✅ |
| `POST` | `/api/register/start` | Start registration process | ❌ |

### **App Version Management Endpoints**
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| `GET` | `/api/app/version/check` | Check for app updates | ❌ |
| `GET` | `/api/app/version/info` | Get latest version info | ❌ |
| `GET` | `/api/app/version/download/{id}` | Download app executable | ❌ |

### **Telegram Notifications**
| Feature | Description | Configuration |
|---------|-------------|---------------|
| **Auto Notifications** | Automatic Telegram messages on registration success/failure | Per-user settings in Filament admin |
| **Device Tracking** | Include device name in notifications | Automatic from device_fingerprint |
| **Custom Templates** | Personalized message templates | JSON format in user settings |
| **Multi-language** | Support for multiple languages | English, Vietnamese, Chinese, Japanese |

## 🧪 **Testing API**

### **1. Login to get token:**
```bash
curl -X POST https://trananhtu.vn/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin",
    "password": "admin123",
    "device_name": "Test Device",
    "device_fingerprint": "test-device-123"
  }'
```

### **2. Submit email registration (triggers Telegram notification):**
```bash
curl -X POST https://trananhtu.vn/api/email/submit \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -d '{
    "email": "testuser@gmail.com",
    "password": "SecurePass123!",
    "device_fingerprint": "device_abc123xyz",
    "proxy_info": {
      "ip": "192.168.1.100",
      "port": 8080
    },
    "registration_time": 1800
  }'
```

### **3. Use token for protected endpoints:**
```bash
curl -X GET https://trananhtu.vn/api/users/profile \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

### **4. Check for app updates:**
```bash
curl -X GET "https://trananhtu.vn/api/app/version/check?current_version_code=100"
```

### **5. Get latest version info:**
```bash
curl -X GET https://trananhtu.vn/api/app/version/info
```

### **6. Download app version:**
```bash
curl -X GET https://trananhtu.vn/api/app/version/download/1 \
  -o app-v1.2.0.exe
```

## 🔧 **Development**

### **Generate/Update API Docs:**
```bash
php artisan l5-swagger:generate
```

### **View Raw JSON Schema:**
- **URL:** `https://trananhtu.vn/docs/api-docs.json`

## 📖 **Features**

### **Swagger UI Features:**
- ✅ **Interactive Testing** - Test endpoints directly in browser
- ✅ **Request/Response Examples** - See exact format
- ✅ **Authentication Testing** - Test JWT flow
- ✅ **Schema Validation** - Validate request/response
- ✅ **Export Options** - Download as JSON/YAML

### **API Features:**
- ✅ **JWT Authentication** - Secure token-based auth
- ✅ **Device Management** - Track and manage devices
- ✅ **Rate Limiting** - Built-in protection
- ✅ **Validation** - Request validation
- ✅ **Error Handling** - Consistent error responses
- ✅ **Telegram Notifications** - Real-time notifications via Telegram
- ✅ **Device Tracking** - Include device names in notifications
- ✅ **Custom Templates** - Personalized message templates
- ✅ **Multi-language Support** - Multiple notification languages
- ✅ **App Version Management** - Check for updates and download new versions

## 🎯 **Quick Start**

1. **Access API docs:** `https://trananhtu.vn/api/documentation`
2. **Login with:** `admin` / `admin123`
3. **Configure Telegram:** Go to `https://trananhtu.vn/admin` → Telegram Settings
4. **Test endpoints** directly in Swagger UI

## 📦 **App Version Management**

### **Version Check Workflow:**
1. **Client app** calls `/api/app/version/check?current_version_code=100`
2. **Server** compares client version code with latest active version
3. **Response** includes:
   - `needs_update`: `true` if update available
   - `force_update`: `true` if update is mandatory
   - `download_url`: URL to download new version
   - `release_notes`: What's new in the update
   - `checksum`: SHA256 hash for file verification

### **Version Code Format:**
- Version code is an integer for easy comparison
- Example: Version `1.0.0` = code `100`, Version `1.2.3` = code `123`
- Higher code = newer version

### **File Verification:**
- Each version includes SHA256 checksum
- Client should verify downloaded file matches checksum before installation
- Prevents corrupted or tampered downloads

### **Force Update:**
- Admin can mark versions as "force update"
- Clients with `force_update=true` must update before continuing
- Useful for critical security patches

### **Example Response:**
```json
{
  "success": true,
  "data": {
    "needs_update": true,
    "force_update": false,
    "latest_version": "1.2.0",
    "latest_version_code": 120,
    "current_version_code": 100,
    "download_url": "/api/app/version/download/1",
    "release_notes": "Bug fixes and performance improvements",
    "file_size": 5242880,
    "checksum": "a1b2c3d4e5f6..."
  }
}
```

## 📱 **Telegram Setup**

### **1. Create Telegram Bot:**
1. Message `@BotFather` on Telegram
2. Send `/newbot` and follow instructions
3. Get your bot token (format: `1234567890:ABCdefGHIjklMNOpqrsTUVwxyz`)

### **2. Get Chat ID:**
1. Message `@userinfobot` on Telegram
2. Send `/start` to get your Chat ID
3. Or use `@getidsbot` for Chat ID

### **3. Configure in Admin Panel:**
1. Go to `https://trananhtu.vn/admin`
2. Navigate to "Telegram Settings"
3. Create/Edit settings for your user
4. Enter Bot Token and Chat ID
5. Enable desired notifications
6. Test connection

## 📝 **Notes**

- All API responses follow consistent format with `success`, `message`, and `data` fields
- JWT tokens are automatically managed by the system
- Device fingerprinting is used for security
- Admin panel available at: `https://trananhtu.vn/admin`
- **Telegram notifications** are sent automatically when email registration completes
- **Device names** are included in Telegram messages for better tracking
- **Custom templates** can be configured per user for personalized messages
- **App versions** can be managed through Filament admin panel at `/admin/app-versions`
- **Version updates** are checked by comparing integer version codes (e.g., 100, 120)
- **SHA256 checksums** are provided for file integrity verification
