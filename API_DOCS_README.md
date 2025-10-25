# 📚 RegMail API Documentation

## 🚀 **Swagger UI Access**

### **Live API Documentation:**
- **URL:** `http://127.0.0.1:8000/api/documentation`
- **Description:** Interactive API documentation with Swagger UI
- **Features:** 
  - Try out API endpoints directly
  - View request/response examples
  - Test authentication flows

## 🔐 **Authentication**

### **JWT Token-based Authentication**
- All protected endpoints require JWT token in Authorization header
- Format: `Authorization: Bearer <your-jwt-token>`
- Token expires after configured time (default: 24 hours)
- Use `/api/auth/refresh` to get new token before expiration

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
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin",
    "password": "admin123",
    "device_name": "Test Device"
  }'
```

### **2. Submit email registration (triggers Telegram notification):**
```bash
curl -X POST http://127.0.0.1:8000/api/email/submit \
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
curl -X GET http://127.0.0.1:8000/api/users/profile \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

## 🔧 **Development**

### **Generate/Update API Docs:**
```bash
php artisan l5-swagger:generate
```

### **View Raw JSON Schema:**
- **URL:** `http://127.0.0.1:8000/docs/api-docs.json`

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

## 🎯 **Quick Start**

1. **Start server:** `php artisan serve`
2. **Open API docs:** `http://127.0.0.1:8000/api/documentation`
3. **Login with:** `admin` / `admin123`
4. **Configure Telegram:** Go to `http://127.0.0.1:8000/admin` → Telegram Settings
5. **Test endpoints** directly in Swagger UI

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
1. Go to `http://127.0.0.1:8000/admin`
2. Navigate to "Telegram Settings"
3. Create/Edit settings for your user
4. Enter Bot Token and Chat ID
5. Enable desired notifications
6. Test connection

## 📝 **Notes**

- All API responses follow consistent format with `success`, `message`, and `data` fields
- JWT tokens are automatically managed by the system
- Device fingerprinting is used for security
- Admin panel available at: `http://127.0.0.1:8000/admin`
- **Telegram notifications** are sent automatically when email registration completes
- **Device names** are included in Telegram messages for better tracking
- **Custom templates** can be configured per user for personalized messages
