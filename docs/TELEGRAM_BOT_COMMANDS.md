# Telegram Bot Commands Documentation

## Overview
Telegram bot với 3 commands chính:
- `/start` - Chào mừng và hướng dẫn nhanh
- `/help` - Hiển thị tài liệu đầy đủ
- `/devices [period] [filter]` - Xem danh sách thiết bị và thống kê email registration theo thời gian

Hỗ trợ filter active/inactive devices và nhiều format thời gian (today, week, month, hours, minutes).

## Commands

### `/start`
**Mục đích:** Chào mừng và hiển thị hướng dẫn sử dụng

**Response:**
```
👋 Chào mừng đến với Email Registration Bot!

📋 <b>Command có sẵn:</b>

<b>/devices [period] [filter]</b>
Xem danh sách thiết bị và thống kê email registration

<b>Parameters:</b>
• [period] - today, week, month, <n>h, <n>m, <n>d
• [filter] - active (có email), inactive (không có email)

<b>Ví dụ:</b>
/devices              → Tất cả devices
/devices today        → Devices hôm nay
/devices 2h           → Devices trong 2 giờ gần đây
/devices 30m          → Devices trong 30 phút gần đây
/devices week active  → Chỉ active devices trong tuần
/devices 1h inactive → Chỉ inactive devices trong 1 giờ

📖 Gõ /help để xem tài liệu đầy đủ
```

**Logic:**
- Kiểm tra xem user có telegram settings chưa
- Nếu chưa có → hướng dẫn setup qua admin panel
- Nếu có rồi → hiển thị welcome message + quick guide

---

### `/help`
**Mục đích:** Hiển thị tài liệu đầy đủ về command `/devices`

**Response:**
```
📖 <b>Tài liệu hướng dẫn /devices</b>

<b>Command:</b> /devices [period] [filter]

<b>Mục đích:</b>
Danh sách thiết bị và thống kê email registration theo thời gian

<b>Parameters:</b>

<b>[period] - Thời gian:</b>
• today - Hôm nay (00:00 - hiện tại)
• week - Tuần này (Monday 00:00 - hiện tại)
• month - Tháng này (ngày 1 00:00 - hiện tại)
• <n>h - N giờ gần đây (vd: 1h, 2h, 24h)
• <n>m - N phút gần đây (vd: 30m, 60m, 120m)
• <n>d - N ngày gần đây (vd: 7d, 30d)
• (để trống) - Tất cả thời gian

<b>[filter] - Lọc:</b>
• active - Chỉ thiết bị có hoạt động (có email)
• inactive - Chỉ thiết bị không có hoạt động (không có email)
• (để trống) - Hiển thị cả active và inactive

<b>Ví dụ sử dụng:</b>
/devices
/devices today
/devices 2h
/devices 30m
/devices week active
/devices month inactive
/devices 1h active
/devices 30m inactive

Gõ /start để xem hướng dẫn nhanh
```

**Logic:**
- Hiển thị documentation đầy đủ về command `/devices`
- Bao gồm tất cả parameters và ví dụ

---

### `/devices [period] [filter]`
**Mục đích:** Danh sách thiết bị và thống kê

**Parameters:**
- `[period]` - Tùy chọn: 
  - `today` - Hôm nay (00:00 - hiện tại)
  - `week` - Tuần này (Monday 00:00 - hiện tại)
  - `month` - Tháng này (ngày 1 00:00 - hiện tại)
  - `<number>h` - Số giờ gần đây (vd: `1h`, `2h`, `24h`)
  - `<number>m` - Số phút gần đây (vd: `30m`, `60m`)
  - `<number>d` - Số ngày gần đây (vd: `7d`, `30d`)
  - (để trống) - Tất cả thời gian
- `[filter]` - Tùy chọn: 
  - `active` - Chỉ thiết bị có hoạt động (có email trong period)
  - `inactive` - Chỉ thiết bị không có hoạt động (không có email trong period)

**Response mặc định (tất cả devices):**
```
📱 <b>Device Statistics [Today]</b>

<b>Active Devices (5):</b>
• iPhone 14: 45 emails
• MacBook Pro: 38 emails
• Android Phone: 22 emails
• iPad: 15 emails
• Windows PC: 8 emails

<b>Inactive Devices (3):</b>
• Chromebook: 0 emails
• Tablet: 0 emails
• Old Phone: 0 emails

📊 <b>Summary:</b>
Total Devices: 8
Active: 5 (62.5%)
Inactive: 3 (37.5%)
```

**Response với filter `active`:**
```
📱 <b>Active Devices [Today]</b>

• iPhone 14: 45 emails
• MacBook Pro: 38 emails
• Android Phone: 22 emails
• iPad: 15 emails
• Windows PC: 8 emails

Total: 5 devices with activity
```

**Response với filter `inactive`:**
```
📱 <b>Inactive Devices [Today]</b>

• Chromebook: 0 emails
• Tablet: 0 emails
• Old Phone: 0 emails

Total: 3 devices without activity
```

**Logic:**
- Query tất cả devices của user
- Tính số registration cho mỗi device trong period
- Group thành active/inactive
- Support filter để chỉ hiển thị active hoặc inactive
- Sort by registration count (descending)

**Ví dụ sử dụng:**
```
/devices              → Tất cả devices, tất cả thời gian
/devices today        → Tất cả devices hôm nay
/devices 2h           → Tất cả devices trong 2 giờ gần đây
/devices 30m          → Tất cả devices trong 30 phút gần đây
/devices week active  → Chỉ active devices trong tuần này
/devices 1h inactive → Chỉ inactive devices trong 1 giờ gần đây
/devices month        → Tất cả devices trong tháng này
```

---

## Time Period Formats

Command `/devices` hỗ trợ các format sau:

| Format | Example | Description |
|--------|---------|-------------|
| `today` | `/devices today` | Hôm nay (00:00 - hiện tại) |
| `week` | `/devices week` | Tuần này (Monday 00:00 - hiện tại) |
| `month` | `/devices month` | Tháng này (ngày 1 00:00 - hiện tại) |
| `<n>h` | `/devices 1h` | N giờ gần đây (vd: `1h`, `2h`, `24h`) |
| `<n>m` | `/devices 30m` | N phút gần đây (vd: `30m`, `60m`, `120m`) |
| `<n>d` | `/devices 7d` | N ngày gần đây (vd: `7d`, `30d`) |
| (empty) | `/devices` | Tất cả thời gian |

**Lưu ý:**
- Format `<n>h` tính từ thời điểm hiện tại trở về trước N giờ
- Format `<n>m` tính từ thời điểm hiện tại trở về trước N phút
- Format `<n>d` tính từ thời điểm hiện tại trở về trước N ngày
- Kết hợp với filter `active` hoặc `inactive` để lọc kết quả

---

## Error Handling

### Common Error Messages

```
❌ Error: User not found
→ Bot không tìm thấy user account liên kết

❌ Error: Invalid period format
→ Format thời gian không hợp lệ. Dùng: today, week, month, <n>h, <n>m, <n>d
→ Ví dụ: `1h`, `2h`, `30m`, `60m`, `7d`

❌ Error: No data found
→ Không có dữ liệu trong khoảng thời gian được chọn

❌ Error: Telegram not configured
→ User chưa cấu hình Telegram settings. Vào admin panel để setup.

❌ Error: Unauthorized
→ Chat ID không khớp với user account
```

---

## Implementation Notes

### Webhook Endpoint
- URL: `/api/telegram/webhook/{token?}`
- Method: POST
- Security: Verify token hoặc chat_id để map với user

### Command Parser
- Parse `/command args` format
- Extract command name và parameters
- Route đến handler tương ứng

### Response Format
- HTML parse mode cho formatting
- Emoji icons cho visual clarity
- Code blocks cho technical data
- Bold text cho headers

### Database Queries
- Efficient queries với proper indexes
- Cache frequent queries nếu cần
- Pagination cho large datasets

---

## Future Enhancements

1. **Interactive Buttons**
   - Inline keyboard cho period selection
   - Quick action buttons (refresh, export)

2. **Advanced Filters**
   - Filter by device name
   - Filter by status (success/failed)
   - Filter by date range

3. **Export Features**
   - Export CSV via Telegram file
   - Generate reports

4. **Real-time Updates**
   - Push notifications cho events
   - Live stats updates

---

## Testing

### Test Cases

1. `/start` với user chưa setup → hướng dẫn setup qua admin panel
2. `/start` với user đã setup → hiển thị welcome message + quick guide
3. `/help` → hiển thị tài liệu đầy đủ về `/devices`
4. `/devices` → Tất cả devices, tất cả thời gian
5. `/devices today` → Tất cả devices hôm nay
6. `/devices week` → Tất cả devices trong tuần
7. `/devices month` → Tất cả devices trong tháng
8. `/devices 1h` → Tất cả devices trong 1 giờ gần đây
9. `/devices 2h` → Tất cả devices trong 2 giờ gần đây
10. `/devices 30m` → Tất cả devices trong 30 phút gần đây
11. `/devices 60m` → Tất cả devices trong 60 phút gần đây
12. `/devices week active` → Chỉ active devices trong tuần
13. `/devices month inactive` → Chỉ inactive devices trong tháng
14. `/devices 1h active` → Chỉ active devices trong 1 giờ gần đây
15. `/devices 30m inactive` → Chỉ inactive devices trong 30 phút gần đây
16. Invalid period format (vd: `abc`, `1x`) → Error message
17. Empty data trong period → "No data found" message
18. User chưa có devices → "No devices found" message

---

## Security Considerations

1. **User Authentication**
   - Verify chat_id matches user's telegram_chat_id
   - Validate bot token

2. **Rate Limiting**
   - Limit commands per user per minute
   - Prevent spam

3. **Data Privacy**
   - Chỉ hiển thị data của chính user
   - Mask sensitive info (passwords, tokens)

4. **Input Validation**
   - Sanitize all user inputs
   - Validate period formats
   - Limit pagination size

---

## API Integration

### Telegram Bot API Methods Used

- `sendMessage` - Gửi text responses
- `sendPhoto` - (Future) Charts/graphs
- `setWebhook` - Setup webhook endpoint
- `getWebhookInfo` - Check webhook status

### Internal API Calls

- User model relationships
- Registration queries
- Device statistics
- Quota calculations

---

*Last Updated: 2024-01-15*

