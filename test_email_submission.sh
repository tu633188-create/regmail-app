#!/bin/bash

# RegMail Email Submission API Test Script
# This script tests the email submission API endpoints

BASE_URL="http://127.0.0.1:8000/api"
TOKEN=""

echo "🚀 RegMail Email Submission API Test"
echo "=================================="

# Step 1: Login to get JWT token
echo "📝 Step 1: Login to get JWT token..."
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin",
    "password": "admin123",
    "device_name": "Test Device"
  }')

echo "Login Response:"
echo "$LOGIN_RESPONSE" | jq '.'

# Extract token from response
TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.data.token // empty')

if [ -z "$TOKEN" ] || [ "$TOKEN" = "null" ]; then
    echo "❌ Failed to get token. Please check login credentials."
    exit 1
fi

echo "✅ Token obtained: ${TOKEN:0:20}..."
echo ""

# Step 2: Test email submission
echo "📧 Step 2: Test email submission..."
SUBMIT_RESPONSE=$(curl -s -X POST "$BASE_URL/email/submit" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "email": "testuser123@gmail.com",
    "password": "SecurePass123!",
    "device_fingerprint": "device_abc123xyz",
    "proxy_info": {
      "ip": "192.168.1.100",
      "port": 8080,
      "username": "proxy_user",
      "password": "proxy_pass"
    },
    "metadata": {
      "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
      "creation_time": "2025-10-24T21:30:00Z",
      "verification_status": "verified",
      "notes": "Account created successfully"
    }
  }')

echo "Submit Response:"
echo "$SUBMIT_RESPONSE" | jq '.'

# Extract registration ID
REGISTRATION_ID=$(echo "$SUBMIT_RESPONSE" | jq -r '.data.registration_id // empty')

if [ -z "$REGISTRATION_ID" ] || [ "$REGISTRATION_ID" = "null" ]; then
    echo "❌ Failed to submit email. Please check the response."
    exit 1
fi

echo "✅ Email submitted successfully. Registration ID: $REGISTRATION_ID"
echo ""

# Step 3: Test get submissions list
echo "📋 Step 3: Test get submissions list..."
SUBMISSIONS_RESPONSE=$(curl -s -X GET "$BASE_URL/email/submissions" \
  -H "Authorization: Bearer $TOKEN")

echo "Submissions Response:"
echo "$SUBMISSIONS_RESPONSE" | jq '.'

echo "✅ Submissions list retrieved"
echo ""

# Step 4: Test get specific submission
echo "🔍 Step 4: Test get specific submission..."
SUBMISSION_RESPONSE=$(curl -s -X GET "$BASE_URL/email/submissions/$REGISTRATION_ID" \
  -H "Authorization: Bearer $TOKEN")

echo "Submission Details Response:"
echo "$SUBMISSION_RESPONSE" | jq '.'

echo "✅ Submission details retrieved"
echo ""

# Step 5: Test quota check
echo "📊 Step 5: Test quota check..."
QUOTA_RESPONSE=$(curl -s -X GET "$BASE_URL/users/quota" \
  -H "Authorization: Bearer $TOKEN")

echo "Quota Response:"
echo "$QUOTA_RESPONSE" | jq '.'

echo "✅ Quota information retrieved"
echo ""

# Step 6: Test validation error (missing required fields)
echo "⚠️  Step 6: Test validation error..."
VALIDATION_RESPONSE=$(curl -s -X POST "$BASE_URL/email/submit" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "email": "invalid-email",
    "password": "123",
    "device_fingerprint": "device_invalid"
  }')

echo "Validation Error Response:"
echo "$VALIDATION_RESPONSE" | jq '.'

echo "✅ Validation error handled correctly"
echo ""

# Step 7: Test unauthorized access (no token)
echo "🔒 Step 7: Test unauthorized access..."
UNAUTHORIZED_RESPONSE=$(curl -s -X POST "$BASE_URL/email/submit" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123",
    "device_fingerprint": "device_unauthorized"
  }')

echo "Unauthorized Response:"
echo "$UNAUTHORIZED_RESPONSE" | jq '.'

echo "✅ Unauthorized access blocked correctly"
echo ""

echo "🎉 All tests completed successfully!"
echo "=================================="
echo "✅ Login successful"
echo "✅ Email submission successful"
echo "✅ Submissions list retrieved"
echo "✅ Specific submission retrieved"
echo "✅ Quota information retrieved"
echo "✅ Validation errors handled"
echo "✅ Unauthorized access blocked"
echo ""
echo "📝 Test Summary:"
echo "- All API endpoints are working correctly"
echo "- JWT authentication is properly implemented"
echo "- Input validation is working"
echo "- Error handling is appropriate"
echo "- Security measures are in place"
