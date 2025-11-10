#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Đổi tất cả dấu phẩy (,) thành dấu pipe (|) trong file account.txt
"""

import os

# Tên file
input_file = "account.txt"
backup_file = "account.txt.backup"

# Kiểm tra file có tồn tại không
if not os.path.exists(input_file):
    print(f"❌ Không tìm thấy file: {input_file}")
    exit(1)

print(f"📖 Đang đọc file: {input_file}")

# Đọc file
try:
    with open(input_file, 'r', encoding='utf-8') as f:
        content = f.read()
except Exception as e:
    print(f"❌ Lỗi khi đọc file: {e}")
    exit(1)

# Đếm số dấu phẩy
comma_count = content.count(',')
print(f"📊 Tìm thấy {comma_count:,} dấu phẩy")

if comma_count == 0:
    print("⚠️  Không có dấu phẩy nào để thay thế!")
    exit(0)

# Tạo backup
print(f"💾 Đang tạo backup: {backup_file}")
try:
    with open(backup_file, 'w', encoding='utf-8') as f:
        f.write(content)
    print("✅ Backup đã được tạo")
except Exception as e:
    print(f"⚠️  Không thể tạo backup: {e}")
    response = input("Tiếp tục không? (y/n): ")
    if response.lower() != 'y':
        exit(0)

# Thay thế dấu phẩy bằng dấu pipe
print("🔄 Đang thay thế dấu phẩy bằng dấu pipe...")
new_content = content.replace(',', '|')

# Ghi file mới
print(f"💾 Đang ghi file: {input_file}")
try:
    with open(input_file, 'w', encoding='utf-8') as f:
        f.write(new_content)
    print(f"✅ Hoàn thành! Đã thay thế {comma_count:,} dấu phẩy bằng dấu pipe")
    print(f"📁 Backup được lưu tại: {backup_file}")
except Exception as e:
    print(f"❌ Lỗi khi ghi file: {e}")
    exit(1)

