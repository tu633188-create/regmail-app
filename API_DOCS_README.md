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

### **Registration Endpoints**
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| `POST` | `/api/register/start` | Start registration | ✅ |
| `GET` | `/api/register/status/{id}` | Check status | ✅ |
| `GET` | `/api/register/history` | Get history | ✅ |
| `GET` | `/api/register/stats` | Get statistics | ✅ |

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

### **2. Use token for protected endpoints:**
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

## 🎯 **Quick Start**

1. **Start server:** `php artisan serve`
2. **Open API docs:** `http://127.0.0.1:8000/api/documentation`
3. **Login with:** `admin` / `admin123`
4. **Test endpoints** directly in Swagger UI

## 📝 **Notes**

- All API responses follow consistent format with `success`, `message`, and `data` fields
- JWT tokens are automatically managed by the system
- Device fingerprinting is used for security
- Admin panel available at: `http://127.0.0.1:8000/admin`
